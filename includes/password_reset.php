<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/functions.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/dev_mail.php';

function reset_local_only(): bool {
    $remote=(string)($_SERVER['REMOTE_ADDR']??'');
    $host=strtolower((string)($_SERVER['HTTP_HOST']??''));
    return in_array($remote,['127.0.0.1','::1'],true) && preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/',$host)===1;
}
function reset_account(PDO $pdo,string $email): ?array {
    $q=$pdo->prepare('SELECT l.user_ID,l.user_Email,u.first_Name,u.last_Name FROM Login l JOIN User u ON u.user_ID=l.user_ID WHERE LOWER(l.user_Email)=? LIMIT 1');
    $q->execute([strtolower(trim($email))]);
    $row=$q->fetch(PDO::FETCH_ASSOC);
    return $row?:null;
}
function request_password_reset(PDO $pdo,string $email): void {
    if (!reset_local_only()) throw new RuntimeException('Demo password reset is localhost only.');
    $user=reset_account($pdo,$email);
    if (!$user) return;
    $uid=(int)$user['user_ID'];
    $pdo->beginTransaction();
    try {
        $q=$pdo->prepare('SELECT requested_At FROM Password_Reset_Code WHERE user_ID=? FOR UPDATE');
        $q->execute([$uid]);$existing=$q->fetch(PDO::FETCH_ASSOC);
        if($existing && strtotime((string)$existing['requested_At'])>time()-60){$pdo->commit();return;}
        $code=(string)random_int(100000,999999);
        $hash=password_hash($code,PASSWORD_DEFAULT);
        $q=$pdo->prepare('INSERT INTO Password_Reset_Code (user_ID,code_hash,expires_At,requested_At,attempts,used_At) VALUES (?,?,DATE_ADD(NOW(),INTERVAL 10 MINUTE),NOW(),0,NULL) ON DUPLICATE KEY UPDATE code_hash=VALUES(code_hash),expires_At=VALUES(expires_At),requested_At=NOW(),attempts=0,used_At=NULL');
        $q->execute([$uid,$hash]);
        $pdo->commit();
    } catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    $name=trim($user['first_Name'].' '.$user['last_Name']);
    try {
        dev_mail_deliver((string)$user['user_Email'],'Your Password Reset Verification Code',"Hello {$name},\n\nWe received a request to reset your Ashford University account password.\n\nYour verification code is: {$code}\n\nThis code expires in 10 minutes and can only be used once.\n\nIf you did not request this change, you can ignore this message.\n\nAshford University Security",'reset_code');
    } catch(Throwable $e){error_log('Local password reset delivery failed: '.$e->getMessage());}
    audit_log($pdo,'PasswordResetRequest','Login',(string)$uid);
}
function complete_password_reset(PDO $pdo,string $email,string $code,string $password,string $confirm): array {
    $email=strtolower(trim($email));$code=trim($code);
    $invalid='Invalid, expired, or already used verification code.';
    if(!reset_local_only())return [false,'Local demonstration only.'];
    if(!preg_match('/^[0-9]{6}$/',$code))return [false,$invalid];
    if($password!==$confirm)return [false,'Passwords do not match.'];
    [$ok,$policyMessage]=password_policy_ok($password);
    if(!$ok)return [false,$policyMessage];
    try {
        $pdo->beginTransaction();
        $q=$pdo->prepare('SELECT l.user_ID,r.code_hash,r.expires_At,r.used_At,r.attempts FROM Login l JOIN Password_Reset_Code r ON r.user_ID=l.user_ID WHERE LOWER(l.user_Email)=? FOR UPDATE');
        $q->execute([$email]);$row=$q->fetch(PDO::FETCH_ASSOC);
        if(!$row || $row['used_At']!==null || strtotime((string)$row['expires_At'])<=time() || (int)$row['attempts']>=5){$pdo->rollBack();return [false,$invalid];}
        $uid=(int)$row['user_ID'];
        if(!password_verify($code,(string)$row['code_hash'])){
            $pdo->prepare('UPDATE Password_Reset_Code SET attempts=attempts+1 WHERE user_ID=?')->execute([$uid]);
            $pdo->commit();return [false,$invalid];
        }
        [$same,$sameMessage]=password_is_same_as_current($pdo,$uid,$password);
        if($same){$pdo->rollBack();return [false,$sameMessage];}
        [$reused,$reusedMessage]=password_is_reused($pdo,$uid,$password);
        if($reused){$pdo->rollBack();return [false,$reusedMessage];}
        $q=$pdo->prepare('SELECT user_Password FROM Login WHERE user_ID=? FOR UPDATE');$q->execute([$uid]);$oldHash=$q->fetchColumn();
        if($oldHash!==false){
            $pdo->prepare('INSERT INTO Password_History (user_ID,password_hash) VALUES (?,?)')->execute([$uid,$oldHash]);
            $pdo->prepare('DELETE ph FROM Password_History ph LEFT JOIN (SELECT history_ID FROM Password_History WHERE user_ID=? ORDER BY changed_At DESC LIMIT 5) keep ON keep.history_ID=ph.history_ID WHERE ph.user_ID=? AND keep.history_ID IS NULL')->execute([$uid,$uid]);
        }
        $pdo->prepare('UPDATE Login SET user_Password=?,no_Of_Tries=0,lock_var=0 WHERE user_ID=?')->execute([password_hash($password,PASSWORD_DEFAULT),$uid]);
        $pdo->prepare('UPDATE Password_Reset_Code SET used_At=NOW() WHERE user_ID=?')->execute([$uid]);
        $pdo->commit();
        audit_log($pdo,'PasswordReset','Login',(string)$uid);
        // Send the confirmation only after the password change has committed.
        try {
            $account=one($pdo,'SELECT l.user_Email,u.first_Name,u.last_Name FROM Login l JOIN User u ON u.user_ID=l.user_ID WHERE l.user_ID=? LIMIT 1',[$uid]);
            if ($account) {
                $fullName=trim((string)$account['first_Name'].' '.(string)$account['last_Name']);
                dev_mail_deliver((string)$account['user_Email'],'Your Ashford University Password Was Changed',
                    "Hello {$fullName},\n\nYour Ashford University account password was successfully changed.\n\nIf you made this change, no further action is needed. If you did not authorize it, contact university support immediately.\n\nAshford University Security",'password_changed');
            }
        } catch (Throwable $mailError) { error_log('Password change confirmation delivery failed: '.$mailError->getMessage()); }
        return [true,'Your password has been updated. You can now sign in.'];
    } catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Password reset failed: '.$e->getMessage());return [false,'Unable to reset password. Please try again.'];}
}

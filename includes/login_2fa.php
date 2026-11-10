<?php
declare(strict_types=1);
require_once __DIR__ . '/dev_mail.php';

function login_2fa_begin(PDO $pdo, array $user): void {
    if (getenv('APP_ENV') !== 'local') {
        throw new RuntimeException('Demo login verification is local-only.');
    }
    $uid = (int)$user['user_ID'];
    $code = (string)random_int(100000, 999999);
    $hash = password_hash($code, PASSWORD_DEFAULT);
    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO Login_Verification (user_ID,code_hash,expires_At,attempts,requested_At)
            VALUES (?,?,DATE_ADD(NOW(), INTERVAL 10 MINUTE),0,NOW())
            ON DUPLICATE KEY UPDATE code_hash=VALUES(code_hash), expires_At=VALUES(expires_At),
            attempts=0, requested_At=NOW()')->execute([$uid,$hash]);
        $name = trim((string)$user['first_Name'].' '.(string)$user['last_Name']);
        dev_mail_deliver((string)$user['user_Email'], 'Your Ashford University sign-in code',
            "Hello {$name},\n\nYour sign-in verification code is: {$code}\n\n"
            ."It expires in 10 minutes and can only be used once.\n\n"
            ."If you did not attempt to sign in, contact university support.\n\n"
            ."Ashford University Security");
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
    // Clear any existing authenticated dashboard session before challenge.
    unset($_SESSION['user_id'], $_SESSION['user_type'], $_SESSION['email'], $_SESSION['name']);
    session_regenerate_id(true);
    $_SESSION['login_2fa_pending'] = [
        'user_id'=>$uid, 'email'=>(string)$user['user_Email'],
        'role'=>(string)$user['user_Type'], 'name'=>$name,
        'started'=>time()
    ];
}

function login_2fa_complete(PDO $pdo, string $code): bool {
    $pending = $_SESSION['login_2fa_pending'] ?? null;
    if (!is_array($pending) || time() - (int)($pending['started'] ?? 0) > 600
        || preg_match('/^[0-9]{6}$/D', $code) !== 1) return false;
    $uid=(int)$pending['user_id'];
    $pdo->beginTransaction();
    try {
        $q=$pdo->prepare('SELECT code_hash,expires_At,attempts FROM Login_Verification WHERE user_ID=? FOR UPDATE');
        $q->execute([$uid]);
        $row=$q->fetch(PDO::FETCH_ASSOC);
        if (!$row || (int)$row['attempts'] >= 5 || strtotime((string)$row['expires_At']) <= time()) {
            $pdo->commit(); return false;
        }
        if (!password_verify($code,(string)$row['code_hash'])) {
            $pdo->prepare('UPDATE Login_Verification SET attempts=attempts+1 WHERE user_ID=?')->execute([$uid]);
            $pdo->commit(); return false;
        }
        $pdo->prepare('DELETE FROM Login_Verification WHERE user_ID=?')->execute([$uid]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
    session_regenerate_id(true);
    $_SESSION['user_id']=$uid;
    $_SESSION['user_type']=$pending['role'];
    $_SESSION['email']=$pending['email'];
    $_SESSION['name']=$pending['name'];
    unset($_SESSION['login_2fa_pending']);
    return true;
}

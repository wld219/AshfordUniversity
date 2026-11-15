<?php
declare(strict_types=1);
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/helpers.php';
require_once __DIR__.'/includes/login_2fa.php';
$pending=$_SESSION['login_2fa_pending']??null;
if (!is_array($pending) || time()-(int)($pending['started']??0)>600) {
    unset($_SESSION['login_2fa_pending']);
    $_SESSION['login_error']='Your sign-in verification session expired. Please sign in again.';
    header('Location: '.url('index.php'));exit;
}
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $code=trim((string)($_POST['code']??''));
    try {
        if (login_2fa_complete($pdo,$code)) {
            audit_log($pdo,'Login','Login',(string)$_SESSION['user_id']);
            header('Location: '.role_home((string)$_SESSION['user_type']));exit;
        }
        $error='Invalid, expired, or previously used code. After five incorrect attempts, sign in again.';
    } catch (Throwable $e) {
        error_log('Login verification error: '.$e->getMessage());
        $error='Unable to verify your code right now.';
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verify sign-in | Ashford University</title>
<style>body{font:16px system-ui,sans-serif;background:#f1f5f9;color:#172033;display:grid;place-items:center;min-height:100vh;margin:0}.panel{background:white;border-radius:14px;padding:32px;max-width:420px;width:calc(100% - 48px);box-shadow:0 10px 35px #17203315}input,button{box-sizing:border-box;width:100%;padding:13px;font:inherit;border-radius:8px;margin:10px 0}input{border:1px solid #94a3b8}button{border:0;background:#123f73;color:white;cursor:pointer}.error{color:#a11}a{color:#123f73}</style></head><body><main class="panel"><h1>Verify your sign-in</h1><p>We sent a six-digit code to your Ashford Mail inbox: <strong><?=e($pending['email'])?></strong>.</p><p>Code expires in 10 minutes.</p>
<?php if($error!==''):?><p class="error" role="alert"><?=e($error)?></p><?php endif;?>
<form method="post"><?=csrf_field()?><label for="code">Verification code</label><input id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus><button type="submit">Verify and sign in</button></form><p><a href="<?=e(url('index.php'))?>">Return to sign-in</a></p></main></body></html>

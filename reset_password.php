<?php
declare(strict_types=1);
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/helpers.php';
require_once __DIR__.'/includes/password_reset.php';
$error='';$success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_verify();
 [$ok,$message]=complete_password_reset($pdo,(string)($_POST['email']??''),(string)($_POST['code']??''),(string)($_POST['password']??''),(string)($_POST['confirm_password']??''));
 if($ok)$success=$message;else $error=$message;
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset Password | Ashford University</title><link rel="stylesheet" href="<?=e(url('assets/css/login.css'))?>"><style>body{padding:16px}.reset-panel{max-width:440px;margin:30px auto;padding:25px;background:white;border-radius:12px}.reset-panel input{display:block;width:100%;padding:12px;margin:7px 0 15px}.reset-panel a{color:#155b98}.reset-panel .notice{padding:12px;background:#eef8ee}.reset-panel .error{padding:12px;background:#ffefef}</style></head><body><main class="reset-panel"><h2>Reset Password</h2>
<?php if($error):?><p class="error" role="alert"><?=e($error)?></p><?php endif;?>
<?php if($success):?><p class="notice" role="status"><?=e($success)?></p><p><a href="<?=e(url('index.php'))?>" target="_top">Return to login</a></p><?php else:?>
<p>Enter the code from your Ashford Mail inbox and choose a new password.</p><form method="post"><?=csrf_field()?>
<label for="email">University email</label><input id="email" name="email" type="email" required maxlength="255" value="<?=e((string)($_POST['email']??($_GET['email']??'')))?>">
<label for="code">Six-digit verification code</label><input id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" required autocomplete="one-time-code">
<label for="password">New password</label><input id="password" name="password" type="password" minlength="10" autocomplete="new-password" required>
<label for="confirm">Confirm new password</label><input id="confirm" name="confirm_password" type="password" minlength="10" autocomplete="new-password" required>
<button class="login-button" type="submit">Update password</button></form><p><a href="<?=e(url('forgot_password.php'))?>">Request another code</a></p><?php endif;?></main></body></html>

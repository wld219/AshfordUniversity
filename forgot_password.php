<?php
declare(strict_types=1);
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/helpers.php';
require_once __DIR__.'/includes/password_reset.php';
$error='';$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_verify();$email=post_str('email',255);
 if(!valid_email($email))$error='Enter a valid university email address.';
 else {try{request_password_reset($pdo,$email);$message='If an account exists, a six-digit verification code has been sent to your university email address. Codes expire in 10 minutes.';}catch(Throwable $e){error_log('Reset request error: '.$e->getMessage());$error='Unable to process the request. Please try again.';}}
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Forgot Password | Ashford University</title><link rel="stylesheet" href="<?=e(url('assets/css/login.css'))?>"></head><body class="login-page"><main class="login-card"><h1>Forgot Password</h1><p>Enter your university email. Your verification code will be sent to your Ashford University email inbox.</p>
<?php if($error):?><p class="login-error" role="alert"><?=e($error)?></p><?php endif;?><?php if($message):?><p class="success-message" role="status"><?=e($message)?></p><?php endif;?>
<form method="post" class="login-form"><?=csrf_field()?><label for="email">University email</label><input id="email" type="email" name="email" maxlength="255" required placeholder="first.last@ashford.edu" value="<?=e((string)($_POST['email']??''))?>"><button class="login-button" type="submit">Send verification code</button></form><p class="login-help"><a href="<?=e(url('reset_password.php'))?>">I have a verification code</a></p><p class="login-help"><a href="<?=e(url('index.php'))?>" target="_top">Back to login</a></p></main></body></html>

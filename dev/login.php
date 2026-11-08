<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/mail_auth.php';
mail_local_only();
require_once __DIR__ . '/../config/database.php';
if (!empty($_SESSION['mail_user_id'])) { header('Location: mailbox.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(mail_csrf(), (string)($_POST['csrf'] ?? ''))) { http_response_code(400); exit('Invalid form.'); }
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $role = (string)($_POST['user_type'] ?? '');
    $allowedRoles = ['Student', 'Faculty', 'Admin', 'StatStaff'];
    $stmt = $pdo->prepare('SELECT ma.user_ID, ma.password_hash, l.user_Email, u.first_Name, u.last_Name, l.user_Type FROM Mail_Account ma JOIN Login l ON l.user_ID=ma.user_ID JOIN User u ON u.user_ID=ma.user_ID WHERE LOWER(l.user_Email)=? LIMIT 1');
    $stmt->execute([$email]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    if ((int)($_SESSION['mail_failures'] ?? 0) >= 5) {
        http_response_code(429);
        $error = 'Too many attempts in this session.';
    } elseif ($account && in_array($role, $allowedRoles, true) && hash_equals((string)$account['user_Type'], $role) && password_verify($password, (string)$account['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['mail_failures'] = 0;
        $_SESSION['mail_user_id'] = (int)$account['user_ID'];
        $_SESSION['mail_role'] = (string)$account['user_Type'];
        $_SESSION['mail_email'] = (string)$account['user_Email'];
        $_SESSION['mail_name'] = trim($account['first_Name'].' '.$account['last_Name']);
        header('Location: mailbox.php'); exit;
    }
    if ($error === '') $error = 'Invalid email, password, or account type.';
    // Small per-session throttle; production systems require persistent IP/account rate limits.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && http_response_code() !== 429) $_SESSION['mail_failures'] = (int)($_SESSION['mail_failures'] ?? 0) + 1;
    if ($_SESSION['mail_failures'] >= 5) { http_response_code(429); $error = 'Too many attempts. Close the browser and try later.'; }
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Sign in | Ashford University Mail</title><link rel="stylesheet" href="mail.css"></head>
<body class="auth-body">
<aside class="auth-aside"><div class="brand"><span class="brandmark">A</span> Ashford University Mail</div><div><h2>Your university inbox, all in one place.</h2><p>Access university messages and account notifications in a familiar, organized workspace.</p></div><small>Internal university mail · Local demonstration</small></aside>
<main class="auth-main"><section class="auth-panel"><div class="eyebrow">University mail</div><h1>Welcome back</h1><p class="muted">Sign in with your university mail credentials.</p>
<?php if ($error !== ''): ?><div class="alert" role="alert"><?= mail_h($error) ?></div><?php endif; ?>
<form class="auth-form" method="post" action="login.php"><input type="hidden" name="csrf" value="<?= mail_h(mail_csrf()) ?>">
<div class="field"><label for="email">University email</label><input id="email" name="email" type="email" autocomplete="username" placeholder="first.last@ashford.edu" maxlength="255" value="<?= mail_h((string)($_POST['email'] ?? '')) ?>" required></div>
<div class="field"><label for="user_type">Account type</label><select id="user_type" name="user_type" required><option value="">Choose your role</option><?php foreach (['Student'=>'Student','Faculty'=>'Faculty','Admin'=>'Admin','StatStaff'=>'Statistics Staff'] as $value=>$label): ?><option value="<?=mail_h($value)?>" <?=($_POST['user_type']??'')===$value?'selected':''?>><?=mail_h($label)?></option><?php endforeach; ?></select></div>
<div class="field"><label for="password">Mail password</label><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your mail password" required></div>
<button class="primary" type="submit">Sign in to mail</button></form>
<p class="auth-foot">This is an internal mail simulation for the Ashford University capstone. It does not send or receive internet email.</p></section></main>
</body></html>

<?php
declare(strict_types=1);
// Separate session from the main Ashford University portal.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('ASHFORD_MAIL_DEMO');
    session_set_cookie_params(['lifetime'=>0,'path'=>'/dev','secure'=>false,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
function mail_local_only(): void {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    if (!in_array($remote, ['127.0.0.1','::1'], true) || !preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $host)) {
        http_response_code(403); exit('Local demonstration only.');
    }
}
function mail_h(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function mail_require_login(): int {
    if (empty($_SESSION['mail_user_id'])) { header('Location: login.php'); exit; }
    return (int)$_SESSION['mail_user_id'];
}
function mail_csrf(): string {
    if (empty($_SESSION['mail_csrf'])) $_SESSION['mail_csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['mail_csrf'];
}

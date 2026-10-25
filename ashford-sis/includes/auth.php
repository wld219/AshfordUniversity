<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

// Session bootstrap: call once at the top of every request.

function init_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        $name = $_ENV['SESSION_NAME'] ?? 'ashford_sis_session';
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        session_name($name);
        session_start();

        // Regenerate session id if user just authenticated (callers invoke after login).
        if (!empty($_SESSION['user_id']) && empty($_SESSION['regenerated'])) {
            session_regenerate_id(true);
            $_SESSION['regenerated'] = true;
        }
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    return one(
        get_pdo(),
        'SELECT u.*, l.user_Type AS login_type
           FROM User u
           JOIN Login l ON l.user_ID = u.user_ID
          WHERE u.user_ID = ?',
        [(int) $_SESSION['user_id']]
    );
}

function sign_in(int $userID, string $userType): void
{
    $_SESSION['user_id'] = $userID;
    $_SESSION['user_Type'] = $userType;
    $_SESSION['regenerated'] = true;
    session_regenerate_id(true);
}

function sign_out(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

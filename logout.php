<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/session.php';

app_start_session();

// Remove all session information
$_SESSION = [];

// End the session
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(
		session_name(),
		'',
		[
			'expires' => time() - 42000,
			'path' => $params['path'],
			'domain' => $params['domain'],
			'secure' => $params['secure'],
			'httponly' => $params['httponly'],
			'samesite' => $params['samesite'] ?? 'Lax',
		]
	);
}

session_destroy();

// Return to the login page
header('Location: ' . (function_exists('app_base_path') ? app_base_path() : '') . '/index.php');
exit;

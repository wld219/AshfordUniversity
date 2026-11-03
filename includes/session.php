<?php

declare(strict_types=1);

/**
 * Hardened session start shared by every entry point.
 */

function app_start_session(): void
{
	// Sessions are a web-only concern; skip them in CLI (tests, scripts).
	if (PHP_SAPI === 'cli') {
		return;
	}

	if (session_status() === PHP_SESSION_ACTIVE) {
		return;
	}

	session_set_cookie_params([
		'httponly' => true,
		'samesite' => 'Lax',
		'secure' => !empty($_SERVER['HTTPS']),
	]);

	session_start();
}

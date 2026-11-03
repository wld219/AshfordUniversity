<?php

declare(strict_types=1);

if (!function_exists('env_int')) {
	require_once __DIR__ . '/../includes/env.php';
}

env_load(dirname(__DIR__) . '/.env');

return [
	// Consecutive failed logins before the account is locked (specification: 3).
	'max_attempts' => env_int('AUTH_MAX_ATTEMPTS', 3),

	// Reset-link lifetime in seconds (default 1 hour).
	'reset_ttl' => env_int('AUTH_RESET_TTL', 3600),

	// Minimum seconds between reset requests for one account (rate limiting).
	'reset_cooldown' => env_int('AUTH_RESET_COOLDOWN', 60),

	// Password policy minimum length used by password_policy_ok().
	'password_min_length' => 10,
];

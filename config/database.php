<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/session.php';

env_load(dirname(__DIR__) . '/.env');

app_start_session();

date_default_timezone_set(env_value('APP_TIMEZONE', 'America/New_York') ?? 'America/New_York');

/**
 * Backwards-compatible wrapper for the previous loader name in this file.
 */
function load_env(string $path): void
{
	env_load($path);
}

// Database information (from .env; never hardcode credentials)
$host = env_value('DB_HOST', '127.0.0.1') ?? '127.0.0.1';
$port = env_value('DB_PORT', '3306') ?? '3306';
$database = env_value('DB_NAME', 'ashford_university') ?? 'ashford_university';
$username = env_value('DB_USER', 'root') ?? 'root';
$password = env_value('DB_PASS', '') ?? '';

// Charset is interpolated into the DSN, so it is limited to a safe list.
$charset = env_value('DB_CHARSET', 'utf8mb4') ?? 'utf8mb4';

if (!in_array($charset, ['utf8mb4', 'utf8'], true)) {
	$charset = 'utf8mb4';
}

// Connect to the MySQL database
try {

	$pdo = new PDO(
		"mysql:host=$host;port=$port;dbname=$database;charset=$charset",
		$username,
		$password
	);

	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {

	http_response_code(500);

	exit('Database connection failed. Please check your MySQL settings.');
}

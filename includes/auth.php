<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/env.php';

env_load(dirname(__DIR__) . '/.env');

// Start the session if it has not already been started
app_start_session();


// Base path of the application inside the web root ('' when deployed at
// the root, '/subfolder' when deployed inside a folder). Central here so
// every redirect helper agrees on one prefix.
function app_base_path(): string
{
	$base = (string) env_value('APP_BASE_PATH', '');

	return ($base === '' || $base === '/') ? '' : rtrim($base, '/');
}


// Make sure the user is logged in
function require_login(): void
{
	if (empty($_SESSION['user_id'])) {

		header('Location: ' . app_base_path() . '/index.php');

		exit;
	}
}


// Make sure the user has permission to view the page
function require_role(array $roles): void
{
	require_login();

	$userType = $_SESSION['user_type'] ?? '';

	if (!in_array($userType, $roles, true)) {

		http_response_code(403);

		exit(
			'403 - You do not have permission to view this page.'
		);
	}
}


// Safely display information on a page
function e($value): string
{
	return htmlspecialchars(
		(string) $value,
		ENT_QUOTES,
		'UTF-8'
	);
}


// The role-based portals this system ships: student, faculty,
// administrator, and statistics staff. Referenced by public pages instead
// of hardcoding the number in the markup.
const ASHFORD_PORTAL_COUNT = 4;


// Send each type of user to the correct dashboard
function role_home(string $role): string
{
	switch ($role) {

		case 'Student':
			return app_base_path() . '/student/dashboard.php';

		case 'Faculty':
			return app_base_path() . '/faculty/dashboard.php';

		case 'Admin':
			return app_base_path() . '/admin/dashboard.php';

		case 'StatStaff':
			return app_base_path() . '/statstaff/dashboard.php';

		default:
			return app_base_path() . '/index.php';
	}
}

// Returns true if the logged-in user has read-only access (StatStaff, or
// an Administrator with security level Read-Only).
function is_read_only(): bool
{
	$userType = $_SESSION['user_type'] ?? '';

	if ($userType === 'StatStaff') {
		return true;
	}

	if ($userType === 'Admin') {
		return ($_SESSION['security_level'] ?? 'Read-Only') === 'Read-Only';
	}

	return false;
}

// Block all state-changing operations for read-only users (StatStaff or
// Read-Only Administrators).
function require_write_access(): void
{
	if (is_read_only()) {
		http_response_code(403);

		exit(
			'403 - Read-only account: changes are not permitted.'
		);
	}
}

// Returns true if the logged-in Administrator has Full-Access (write)
// privileges. Returns false for Read-Only Administrators and non-Admin users.
function is_admin_full_access(): bool
{
	if (($_SESSION['user_type'] ?? '') !== 'Admin') {
		return false;
	}

	return ($_SESSION['security_level'] ?? 'Read-Only') === 'Full-Access';
}

// Require Full-Access Administrator privileges for write operations.
// Read-Only Administrators and StatStaff users are blocked.
function require_admin_write_access(): void
{
	if (!is_admin_full_access()) {
		http_response_code(403);

		exit(
			'403 - Full-Access administrator privileges required.'
		);
	}
}

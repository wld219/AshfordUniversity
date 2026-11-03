<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin', 'StatStaff']);

$adminID = (int) $_SESSION['user_id'];

// Load this admin's security level and lock-manager flag
$adminProfile = one(
    $pdo,
    'SELECT security_Level, lock_manager FROM Admin WHERE user_ID = ?',
    [$adminID]
);

$securityLevel = $adminProfile['security_Level'] ?? 'Read-Only';
$isFullAccess  = $securityLevel === 'Full-Access' && (int) ($adminProfile['lock_manager'] ?? 0) === 0;
$isLockManager = (int) ($adminProfile['lock_manager'] ?? 0) === 1;
$isReadOnly    = $securityLevel === 'Read-Only' || is_read_only();

$error = '';

// Next free user ID inside a role's ID range
// (students 100xxx, faculty 200xxx, admins 300xxx, stat staff 400xxx)
function next_user_id(PDO $pdo, string $role): int
{
	$base = match ($role) {
		'Faculty' => 200000,
		'Admin' => 300000,
		'StatStaff' => 400000,
		default => 100000,
	};

	$row = one(
		$pdo,
		'SELECT COALESCE(MAX(user_ID), ?) AS max_id
		 FROM User
		 WHERE user_ID >= ?
		 AND user_ID < ?',
		[$base, $base, $base + 100000]
	);

	return max($base + 1, ((int) ($row['max_id'] ?? $base)) + 1);
}

// Dispatch form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	require_write_access();

	if (!$isFullAccess && !$isLockManager) {
		redirect_with_flash(
			url('admin/users.php'),
			'error',
			'You have read-only access and cannot change users.'
		);
	}

	$action = post_str('action', 20);

	if ($action === 'create') {

		// Lock manager may create any role including admins.
		// Full-access may create any role except admins.
		$role = post_str('user_role', 20);
		$allowedRoles = ['Student', 'Faculty', 'StatStaff'];

		if ($isLockManager) {
			$allowedRoles[] = 'Admin';
		}

		if (!in_array($role, $allowedRoles, true)) {
			$_SESSION['users_error'] = 'You are not permitted to create that role.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		require_once __DIR__ . '/actions/users_create.php';

	} elseif ($action === 'update') {

		// Lock manager may edit any user including admins.
		// Full-access may edit non-admin users only.
		$userID = post_int('user_id') ?? 0;
		$target = one(
			$pdo,
			'SELECT user_Type FROM User WHERE user_ID = ?',
			[$userID]
		);

		if (!$target) {
			$_SESSION['users_error'] = 'User not found.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		if ($target['user_Type'] === 'Admin' && !$isLockManager) {
			$_SESSION['users_error'] = 'Only the lock manager can edit administrator accounts.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		require_once __DIR__ . '/actions/users_update.php';

	} elseif ($action === 'unlock') {

		// Only the lock manager can unlock accounts.
		if (!$isLockManager) {
			$_SESSION['users_error'] = 'Only the lock manager can unlock accounts.';
			header('Location: ' . url('admin/users.php'));
			exit;
		}

		require_once __DIR__ . '/actions/users_unlock.php';
	}

	header('Location: ' . url('admin/users.php'));
	exit;
}

// Page data (search results, edit target, form options)
require_once __DIR__ . '/users_data.php';

$formError = $_SESSION['users_error'] ?? '';
unset($_SESSION['users_error']);

page_start(
    'Users',
    'Admin',
    'users.php'
);

flash();

require_once __DIR__ . '/partials/users_view.php';

page_end();

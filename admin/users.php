<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin']);

$adminID = (int) $_SESSION['user_id'];

$securityLevel = one(
	$pdo,
	'SELECT security_Level FROM Admin WHERE user_ID = ?',
	[$adminID]
)['security_Level'] ?? 'Read-Only';

$isFullAccess = $securityLevel === 'Full-Access';

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

	if (!$isFullAccess) {
		redirect_with_flash(
			url('admin/users.php'),
			'error',
			'You have read-only access and cannot change users.'
		);
	}

	$action = post_str('action', 20);

	if ($action === 'create') {
		require_once __DIR__ . '/actions/users_create.php';
	} elseif ($action === 'update') {
		require_once __DIR__ . '/actions/users_update.php';
	} elseif ($action === 'unlock') {
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

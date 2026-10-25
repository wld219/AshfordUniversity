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

// Reference data for the forms
require_once __DIR__ . '/sections_data.php';

// Dispatch form submissions to the matching action file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	if (!$isFullAccess) {
		redirect_with_flash(
			url('admin/sections.php'),
			'error',
			'You have read-only access and cannot change sections.'
		);
	}

	$action = post_str('action', 20);

	if ($action === 'create') {
		require_once __DIR__ . '/actions/sections_create.php';
	} elseif ($action === 'cancel') {
		require_once __DIR__ . '/actions/sections_cancel.php';
	} elseif ($action === 'update') {
		require_once __DIR__ . '/actions/sections_update.php';
	}
}

// Filters and listing data
require_once __DIR__ . '/sections_query.php';

page_start(
	'Course Sections',
	'Admin',
	'sections.php'
);

flash();

require_once __DIR__ . '/partials/sections_view.php';

page_end();

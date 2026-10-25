<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin', 'StatStaff']);

// Access level for the current administrator
$adminID = (int) $_SESSION['user_id'];

$securityLevel = one(
	$pdo,
	'SELECT security_Level FROM Admin WHERE user_ID = ?',
	[$adminID]
)['security_Level'] ?? 'Read-Only';

$isFullAccess = $securityLevel === 'Full-Access';

$error = '';

// Dispatch form submissions to the matching action file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	require_write_access();

	$action = post_str('action', 30);

	if (in_array($action, ['create', 'publish', 'reopen'], true)) {
		require_once __DIR__ . '/actions/master_schedule_status.php';
	} elseif ($action === 'update_section') {
		require_once __DIR__ . '/actions/master_schedule_update_section.php';
	}
}

// Page data (semester, schedule record, validation, filters)
require_once __DIR__ . '/master_schedule_data.php';

page_start(
	'Ashford University - Master Schedule',
	'Admin',
	'master-schedule.php'
);

flash();

// View partials (same variable scope)
require_once __DIR__ . '/partials/master_schedule_view.php';

page_end();

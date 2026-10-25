<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin', 'StatStaff']);

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

	if (!$isFullAccess) {
		redirect_with_flash(
			url('admin/academic.php'),
			'error',
			'You have read-only access and cannot change academic records.'
		);
	}

	$action = post_str('action', 30);

	if ($action === 'update_department') {
		require_once __DIR__ . '/actions/academic_department.php';
	} elseif (in_array($action, ['save_course', 'add_prerequisite', 'remove_prerequisite'], true)) {
		require_once __DIR__ . '/actions/academic_courses.php';
	} elseif (in_array($action, ['create_major', 'create_minor', 'add_requirement', 'remove_requirement'], true)) {
		require_once __DIR__ . '/actions/academic_programs.php';
	}
}

// Page data
require_once __DIR__ . '/academic_data.php';

// Faculty assignment rules are reported, never silently corrected, because
// historical records must not be rewritten.
$facultyRuleViolations = faculty_rule_violations($pdo);

page_start(
	'Academic Management',
	'Admin',
	'academic.php'
);

flash();

require_once __DIR__ . '/partials/academic_view.php';

page_end();

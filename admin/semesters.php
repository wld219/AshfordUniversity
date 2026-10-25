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

/*
 * Save semester settings: start date and the registration windows.
 * All date fields are optional; a NULL window means "no deadline
 * configured" (documented behaviour).
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	if (!$isFullAccess) {
		redirect_with_flash(
			url('admin/semesters.php'),
			'error',
			'You have read-only access and cannot change semesters.'
		);
	}

	require_once __DIR__ . '/actions/semesters_save.php';
}

$semesters = all_rows(
	$pdo,
	'SELECT * FROM Semester ORDER BY semester_ID DESC'
);

$editID = get_int('edit');

$editSemester = null;

if ($editID !== null && $isFullAccess) {
	$editSemester = one(
		$pdo,
		'SELECT * FROM Semester WHERE semester_ID = ?',
		[$editID]
	);
}

page_start(
	'Semesters',
	'Admin',
	'semesters.php'
);

flash();

require_once __DIR__ . '/partials/semesters_view.php';

page_end();

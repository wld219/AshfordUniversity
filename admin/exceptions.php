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

/*
 * Grade exceptions let authorized administrators approve grade submissions
 * that arrive after the semester grade deadline. Ordinary faculty
 * submissions stay subject to the deadline; exceptions are recorded with
 * the approving administrator, the reason, and a timestamp.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	require_write_access();

	if (!$isFullAccess) {
		redirect_with_flash(
			url('admin/exceptions.php'),
			'error',
			'You have read-only access and cannot approve grade exceptions.'
		);
	}

	require_once __DIR__ . '/actions/exceptions_manage.php';
}

$exceptions = all_rows(
	$pdo,
	"SELECT ge.exception_ID, ge.student_ID, ge.CRN, ge.reason, ge.created_At,
			CONCAT(u.first_Name, ' ', u.last_Name) AS approver_Name
	FROM Grade_Exception ge
	LEFT JOIN User u ON u.user_ID = ge.approved_By_User_ID
	ORDER BY ge.created_At DESC
	LIMIT 100"
);

page_start(
	'Grade Exceptions',
	'Admin',
	'exceptions.php'
);

flash();

require_once __DIR__ . '/partials/exceptions_view.php';

page_end();

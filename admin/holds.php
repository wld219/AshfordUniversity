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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	require_write_access();

	if (!$isFullAccess) {
		redirect_with_flash(
			url('admin/holds.php'),
			'error',
			'You have read-only access and cannot change holds.'
		);
	}

	require_once __DIR__ . '/actions/holds_manage.php';
}

$holds = all_rows(
	$pdo,
	"SELECT sh.hold_ID, sh.student_ID, sh.hold_Date, h.hold_Type,
			CONCAT(u.first_Name, ' ', u.last_Name) AS student_Name
	FROM Student_Hold sh
	JOIN Hold h ON h.hold_ID = sh.hold_ID
	JOIN User u ON u.user_ID = sh.student_ID
	ORDER BY sh.hold_Date DESC, u.last_Name
	LIMIT 200"
);

$holdTypes = all_rows(
	$pdo,
	'SELECT hold_ID, hold_Type FROM Hold ORDER BY hold_ID'
);

page_start(
	'Student Holds',
	'Admin',
	'holds.php'
);

flash();

require_once __DIR__ . '/partials/holds_view.php';

page_end();

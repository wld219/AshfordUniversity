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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	if (!$isFullAccess) {
		redirect_with_flash(
			url('admin/advisors.php'),
			'error',
			'You have read-only access and cannot change advisor assignments.'
		);
	}

	require_once __DIR__ . '/actions/advisors_assign.php';
}

$facultyList = all_rows(
	$pdo,
	"SELECT f.faculty_ID, f.faculty_Type,
			CONCAT(u.first_Name, ' ', u.last_Name) AS name
	FROM Faculty f
	JOIN User u ON u.user_ID = f.faculty_ID
	ORDER BY name"
);

$assignments = all_rows(
	$pdo,
	"SELECT a.faculty_ID, a.student_ID, a.date_Of_Appnt,
			CONCAT(fu.first_Name, ' ', fu.last_Name) AS faculty_Name,
			CONCAT(su.first_Name, ' ', su.last_Name) AS student_Name,
			f.faculty_Type
	FROM Advisor a
	JOIN User fu ON fu.user_ID = a.faculty_ID
	JOIN User su ON su.user_ID = a.student_ID
	JOIN Faculty f ON f.faculty_ID = a.faculty_ID
	ORDER BY fu.last_Name, su.last_Name
	LIMIT 200"
);

page_start(
	'Advisor Assignments',
	'Admin',
	'advisors.php'
);

flash();

require_once __DIR__ . '/partials/advisors_view.php';

page_end();

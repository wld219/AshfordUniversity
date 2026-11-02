<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];

// Search term for filtering course sections
$search = get_str('search', 100);


// Process a course registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	$crn = (int) ($_POST['crn'] ?? 0);

	require_once __DIR__ . '/registration_actions.php';
}


// Registration window notice for the semester being registered for
$registrationNotice = current_registration_semester($pdo);

$windowOpen = true;
$windowMessage = 'Open';

if ($registrationNotice !== null) {

	[$windowOpen, , $windowMessage] = registration_window_status(
		$pdo,
		$registrationNotice
	);
}


// Registration sections for the current semester
$sql = "SELECT
			Course.course_ID,
			Course.course_Name,
			Course.course_Credits,
			Department.dept_Name,
			Course_Section.CRN,
			Course_Section.section_No,
			Course_Section.available_Seats,
			Course_Section.time_Slot_ID
		FROM Course_Section
		JOIN Course
			ON Course.course_ID = Course_Section.course_ID
		JOIN Department
			ON Department.dept_ID = Course.dept_ID
		WHERE Course_Section.semester_ID = ?
			AND Course_Section.is_Cancelled = 0";

$params = [$registrationNotice];

if ($search !== '') {
	$sql .= ' AND Course.course_Name LIKE ?';
	$params[] = '%' . $search . '%';
}

$sql .= ' ORDER BY
			Course.course_ID,
			Course_Section.section_No';

$sections = all_rows($pdo, $sql, $params);


page_start(
	'Course Registration',
	'Student',
	'registration.php'
);

flash();

require_once __DIR__ . '/partials/registration_view.php';

page_end();

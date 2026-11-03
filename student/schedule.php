<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


// Drop a course
if (isset($_POST['drop'])) {

	csrf_verify();

	require_once __DIR__ . '/schedule_actions.php';
}


// Get all courses the student is registered for
$schedule = all_rows(
	$pdo,
	"SELECT
			Course.course_ID,
			Course.course_Name,
			Course.course_Credits,
			Course_Section.CRN,
			Semester.semester_Name,
			User.first_Name,
			User.last_Name
		FROM Enrollment
		JOIN Course_Section
			ON Course_Section.CRN = Enrollment.CRN
		JOIN Course
			ON Course.course_ID = Course_Section.course_ID
		JOIN Semester
			ON Semester.semester_ID = Course_Section.semester_ID
		JOIN User
			ON User.user_ID = Course_Section.faculty_ID
		WHERE Enrollment.student_ID = ?
		ORDER BY
			Semester.semester_ID,
			Course.course_ID",
	[$studentID]
);


page_start(
	'My Schedule',
	'Student',
	'schedule.php'
);

flash();

require_once __DIR__ . '/partials/schedule_view.php';

page_end();

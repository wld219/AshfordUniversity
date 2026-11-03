<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

	require_role(['Faculty']);

	// Get the logged-in faculty member
	$facultyID = (int) $_SESSION['user_id'];


	// Get the courses taught by this faculty member
$sections = all_rows(
	$pdo,
	"SELECT
			Course_Section.CRN,
			Course.course_ID,
			Course.course_Name
		FROM Course_Section
		JOIN Course
			ON Course.course_ID = Course_Section.course_ID
		WHERE Course_Section.faculty_ID = ?
		ORDER BY Course.course_ID",
	[$facultyID]
);


// Use the selected course or the first course
$crn = (int) (
	$_GET['crn'] ??
	($sections[0]['CRN'] ?? 0)
);


// Save grade and attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	require_once __DIR__ . '/rosters_actions.php';
}


// Get students and today's saved attendance
if ($crn > 0) {

	$today = date('Y-m-d');

	$roster = all_rows(
		$pdo,
		"SELECT
				Enrollment.student_ID,
				User.first_Name,
				User.last_Name,
				Enrollment.Grade,
				Attendance.Present_Absent
			FROM Enrollment

			JOIN User
				ON User.user_ID = Enrollment.student_ID

			JOIN Course_Section
				ON Course_Section.CRN = Enrollment.CRN

			LEFT JOIN Attendance
				ON Attendance.student_ID = Enrollment.student_ID
				AND Attendance.CRN = Enrollment.CRN
				AND Attendance.attendance_Date = ?

			WHERE Enrollment.CRN = ?
			AND Course_Section.faculty_ID = ?

			ORDER BY
				User.last_Name,
				User.first_Name",
		[$today, $crn, $facultyID]
	);

} else {

	$roster = [];
}


page_start(
	'Course Rosters',
	'Faculty',
	'rosters.php'
);

flash();


// Tell the faculty member whether attendance can be recorded right now
// (the server enforces the same rule).
$attendanceOpenNow = false;
$attendanceNotice = '';

if ($crn > 0) {

	[$attendanceOpenNow, $attendanceNotice] = attendance_window_status(
		$pdo,
		$crn
	);
}

require_once __DIR__ . '/partials/rosters_view.php';

page_end();

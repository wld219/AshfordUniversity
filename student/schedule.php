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
			cs.CRN,
			Semester.semester_Name,
			User.first_Name AS faculty_First_Name,
			User.last_Name AS faculty_Last_Name,
			Enrollment.Grade AS final_Grade,
			Enrollment.midterm_Grade,
			Enrollment.midterm_Submitted_At,
			cs.time_Slot_ID,
			cs.section_No,
			cs.available_Seats
		FROM Enrollment
		JOIN Course_Section cs
			ON cs.CRN = Enrollment.CRN
		JOIN Course
			ON Course.course_ID = cs.course_ID
		JOIN Semester
			ON Semester.semester_ID = cs.semester_ID
		JOIN User
			ON User.user_ID = cs.faculty_ID
		WHERE Enrollment.student_ID = ?
		ORDER BY
			Semester.semester_ID DESC,
			Course.course_ID",
	[$studentID]
);

// Time slot details for calendar view
$timeSlotDetails = [];
foreach ($schedule as $course) {
    $timeSlotID = (int) ($course['time_Slot_ID'] ?? 0);
    if ($timeSlotID > 0 && !isset($timeSlotDetails[$timeSlotID])) {
        $slots = all_rows(
            $pdo,
            "SELECT
                    d.day_Of_Week,
                    p.start_Time,
                    p.end_Time
                FROM Time_Slot_Day tsd
                JOIN Day d ON d.day_ID = tsd.day_ID
                JOIN Time_Slot_Period tsp ON tsp.time_Slot_ID = tsd.time_Slot_ID
                JOIN Period p ON p.period_ID = tsp.period_ID
                WHERE tsd.time_Slot_ID = ?
                ORDER BY d.day_ID, p.start_Time",
            [$timeSlotID]
        ) ?? [];
        $timeSlotDetails[$timeSlotID] = $slots;
    }
}


page_start(
	'My Schedule',
	'Student',
	'schedule.php'
);

flash();

require_once __DIR__ . '/partials/schedule_view.php';

page_end();

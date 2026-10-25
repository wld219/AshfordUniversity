<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

/*
 * Read-only master schedule view for students. Shows only information that
 * is already approved for display (course, section, instructor name,
 * meeting time, room, availability). No student records are exposed.
 */

$semesters = all_rows(
	$pdo,
	'SELECT semester_ID, semester_Name FROM Semester ORDER BY semester_ID DESC'
);

$departments = all_rows(
	$pdo,
	'SELECT dept_ID, dept_Name FROM Department ORDER BY dept_Name'
);

$semesterID = get_int('semester')
	?? (int) (one(
		$pdo,
		'SELECT semester_ID FROM Semester ORDER BY semester_ID DESC LIMIT 1'
	)['semester_ID'] ?? 0);

$filterDepartment = get_int('dept');
$filterDay = get_str('day', 10);
$filterCourse = get_str('course', 100);

$sql = "SELECT
			cs.CRN,
			cs.section_No,
			cs.available_Seats,
			cs.lecture_ID,
			cs.lab_ID,
			cs.time_Slot_ID,
			c.course_ID,
			c.course_Name,
			c.course_Credits,
			c.course_Type,
			d.dept_Name,
			CONCAT(u.first_Name, ' ', u.last_Name) AS instructor
		FROM Course_Section cs
		JOIN Course c ON c.course_ID = cs.course_ID
		JOIN Department d ON d.dept_ID = c.dept_ID
		JOIN User u ON u.user_ID = cs.faculty_ID
		WHERE cs.semester_ID = ?
			AND cs.is_Cancelled = 0";

$params = [$semesterID];

if ($filterDepartment !== null) {
	$sql .= ' AND c.dept_ID = ?';
	$params[] = $filterDepartment;
}

if ($filterCourse !== '') {
	$sql .= ' AND c.course_Name LIKE ?';
	$params[] = '%' . $filterCourse . '%';
}

$sql .= ' ORDER BY d.dept_Name, c.course_ID, cs.section_No';

$sections = all_rows($pdo, $sql, $params);

// Meeting days per section (for the day filter and display)
$dayMap = [];

if (count($sections) > 0) {

	$slotIDs = array_unique(array_column($sections, 'time_Slot_ID'));

	$placeholders = implode(',', array_fill(0, count($slotIDs), '?'));

	$dayRows = all_rows(
		$pdo,
		"SELECT tsd.time_Slot_ID, dy.day_Of_Week
		FROM Time_Slot_Day tsd
		JOIN Day dy ON dy.day_ID = tsd.day_ID
		WHERE tsd.time_Slot_ID IN ($placeholders)",
		$slotIDs
	);

	foreach ($dayRows as $row) {
		$dayMap[(int) $row['time_Slot_ID']][] = (string) $row['day_Of_Week'];
	}
}

// Apply the meeting-day filter
if ($filterDay !== '') {

	$sections = array_values(array_filter(
		$sections,
		static fn(array $section): bool => in_array(
			$filterDay,
			$dayMap[(int) $section['time_Slot_ID']] ?? [],
			true
		)
	));
}


page_start(
	'Master Schedule',
	'Student',
	'master-schedule.php'
);

require_once __DIR__ . '/partials/master_schedule_view.php';

page_end();

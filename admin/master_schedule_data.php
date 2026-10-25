<?php

declare(strict_types=1);

// Data for the master schedule page: reference lists, the selected
// semester and schedule record, validation results, filters, pagination.

// Only semesters that actually have schedulable sections appear here, so
// the dropdown reflects real data rather than every row in Semester.
// Sorted by name, which orders the terms chronologically (Fall 2020
// through Spring 2027); the semester_IDs themselves are not chronological.
$semesters = all_rows(
	$pdo,
	"SELECT s.semester_ID, s.semester_Name
	FROM Semester s
	JOIN Course_Section cs
		ON cs.semester_ID = s.semester_ID AND cs.is_Cancelled = 0
	GROUP BY s.semester_ID, s.semester_Name
	ORDER BY s.semester_Name ASC"
);

$semesterIDs = array_map(
	static fn(array $row): int => (int) $row['semester_ID'],
	$semesters
);

$semesterID = get_int('semester');

// The dropdown is the authority: anything else in the query string falls
// back to the newest semester that has sections to schedule.
if ($semesterID === null || !in_array($semesterID, $semesterIDs, true)) {

	$semesterID = $semesterIDs === []
		? 0
		: (int) end($semesterIDs);
}

$semester = one(
	$pdo,
	'SELECT * FROM Semester WHERE semester_ID = ?',
	[$semesterID]
);

$schedule = $semester !== null ? master_schedule_get($pdo, $semesterID) : null;

$validation = $semester !== null
	? validate_semester_schedule($pdo, $semesterID)
	: ['rows' => [], 'stats' => [], 'issues' => [], 'valid' => false];

$scheduleLocked = $semester !== null
	? semester_schedule_locked($pdo, $semesterID)
	: false;

// Filter controls (server-side)
$filterDepartment = get_int('dept');
$filterFaculty = get_int('faculty');
$filterDay = get_str('day', 10);
$filterCourse = get_str('course', 100);
$filterRoom = get_str('room', 50);

$departments = all_rows(
	$pdo,
	'SELECT dept_ID, dept_Name FROM Department ORDER BY dept_Name'
);

$facultyList = all_rows(
	$pdo,
	"SELECT f.faculty_ID, CONCAT(u.first_Name, ' ', u.last_Name) AS name
	FROM Faculty f
	JOIN User u ON u.user_ID = f.faculty_ID
	ORDER BY name"
);

$timeSlots = all_rows(
	$pdo,
	'SELECT time_Slot_ID FROM Time_Slot ORDER BY time_Slot_ID'
);

$lectureRooms = all_rows(
	$pdo,
	"SELECT l.lecture_ID, r.room_Number, b.Bldg_Name
	FROM Lecture l
	JOIN Room r ON r.room_ID = l.lecture_ID
	JOIN Building b ON b.Bldg_ID = r.Bldg_ID
	ORDER BY b.Bldg_Name, r.room_Number"
);

$labRooms = all_rows(
	$pdo,
	"SELECT b.lab_ID, r.room_Number, bl.Bldg_Name
	FROM Lab b
	JOIN Room r ON r.room_ID = b.lab_ID
	JOIN Building bl ON bl.Bldg_ID = r.Bldg_ID
	ORDER BY bl.Bldg_Name, r.room_Number"
);

// Apply the filters to the loaded rows
$rows = $validation['rows'];

if ($filterDepartment !== null) {
	$rows = array_values(array_filter(
		$rows,
		static fn(array $row): bool => (int) $row['dept_ID'] === $filterDepartment
	));
}

if ($filterFaculty !== null) {
	$rows = array_values(array_filter(
		$rows,
		static fn(array $row): bool => (int) $row['faculty_ID'] === $filterFaculty
	));
}

if ($filterDay !== '') {
	$rows = array_values(array_filter(
		$rows,
		static fn(array $row): bool => in_array($filterDay, $row['days'], true)
	));
}

if ($filterCourse !== '') {
	$rows = array_values(array_filter(
		$rows,
		static fn(array $row): bool => stripos((string) $row['course_Name'], $filterCourse) !== false
	));
}

if ($filterRoom !== '') {
	$rows = array_values(array_filter(
		$rows,
		static fn(array $row): bool => $row['room_Label'] !== null
			&& stripos((string) $row['room_Label'], $filterRoom) !== false
	));
}

// Server-side pagination over the filtered set
$perPage = 50;
$page = max(1, get_int('page') ?? 1);
$totalRows = count($rows);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$pageRows = array_slice($rows, $offset, $perPage);

// Section being edited
$editCRN = get_int('edit');
$editSection = null;

if ($editCRN !== null) {
	$editSection = one(
		$pdo,
		'SELECT * FROM Course_Section WHERE CRN = ?',
		[$editCRN]
	);
}

// Recent schedule audit events
$auditEntries = all_rows(
	$pdo,
	"SELECT a.created_At, a.action, a.details,
			CONCAT(u.first_Name, ' ', u.last_Name) AS actor_Name
	FROM Audit_Log a
	LEFT JOIN User u ON u.user_ID = a.actor_User_ID
	WHERE a.entity_Type = 'Master_Schedule'
	ORDER BY a.created_At DESC
	LIMIT 10"
);

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
	WHERE s.semester_Name IN ('Fall 2026', 'Spring 2027')
	GROUP BY s.semester_ID, s.semester_Name
	ORDER BY CASE s.semester_Name WHEN 'Fall 2026' THEN 1 WHEN 'Spring 2027' THEN 2 ELSE 3 END"
);

$semesterIDs = array_map(
	static fn(array $row): int => (int) $row['semester_ID'],
	$semesters
);

$semesterID = get_int('semester');

// Only Fall 2026 and Spring 2027 are selectable; invalid values default to Fall 2026.
if ($semesterID === null || !in_array($semesterID, $semesterIDs, true)) {

	$semesterID = $semesterIDs === []
		? 0
		: (int) reset($semesterIDs);
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
$filterDays = $_GET['days'] ?? [];
if (!is_array($filterDays)) $filterDays = [];
$filterDays = array_values(array_intersect(['MW','TTH','M','TU','W','TH','F'], array_filter($filterDays,'is_string')));
$filterMode = get_str('mode', 20);
$filterPeriod = get_str('period', 20);
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

if ($filterDays !== []) {
    $patterns = ['MW'=>['Monday','Wednesday'],'TTH'=>['Tuesday','Thursday'],
      'M'=>['Monday'],'TU'=>['Tuesday'],'W'=>['Wednesday'],'TH'=>['Thursday'],'F'=>['Friday']];
    $rows = array_values(array_filter($rows, static function(array $row) use ($patterns,$filterDays): bool {
        $actual = $row['days'] ?? []; sort($actual);
        foreach ($filterDays as $key) { $wanted = $patterns[$key]; sort($wanted); if ($actual === $wanted) return true; }
        return false;
    }));
}
if (in_array($filterMode, ['In-Person','Hybrid','Online'], true)) {
    $rows = array_values(array_filter($rows,
      static fn(array $row): bool => ($row['instruction_Mode'] ?? 'In-Person') === $filterMode));
}
if ($filterPeriod !== '') {
    $rows = array_values(array_filter($rows, static fn(array $row): bool =>
        stripos((string)($row['start_Time'] ?? ''), $filterPeriod) !== false ||
        stripos((string)($row['end_Time'] ?? ''), $filterPeriod) !== false));
}

if ($filterCourse !== '') {
	$rows = array_values(array_filter(
		$rows,
		static fn(array $row): bool => (
            stripos((string) $row['course_Name'], $filterCourse) !== false
            || stripos((string) $row['course_ID'], $filterCourse) !== false
        )
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

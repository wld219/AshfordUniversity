<?php

declare(strict_types=1);

// Filters and section listing for the sections page.

$filterSemester = get_int('semester');
$filterDept = get_int('dept');
$search = get_str('search', 100);

// Default to current active terms (Fall 2026 / Spring 2027) when no
// semester filter is explicitly chosen.
if ($filterSemester === null) {
	$sql = "SELECT
			cs.CRN,
			cs.section_No,
			cs.available_Seats,
			cs.is_Cancelled,
			cs.time_Slot_ID,
			cs.lecture_ID,
			cs.lab_ID,
			cs.faculty_ID,
			cs.course_ID,
			cs.semester_ID,
			c.course_Name,
			c.course_Credits,
			d.dept_Name,
			s.semester_Name,
			s.semester_Start,
			CONCAT(u.first_Name, ' ', u.last_Name) AS instructor,
			(SELECT COUNT(*) FROM Enrollment e WHERE e.CRN = cs.CRN) AS enrolled,
			(SELECT GROUP_CONCAT(dy.day_Of_Week ORDER BY dy.day_ID SEPARATOR ', ')
			 FROM Time_Slot_Day tsd
			 JOIN Day dy ON dy.day_ID = tsd.day_ID
			 WHERE tsd.time_Slot_ID = cs.time_Slot_ID) AS meeting_days,
			(SELECT CONCAT(MIN(p.start_Time), ' - ', MAX(p.end_Time))
			 FROM Time_Slot_Period tsp
			 JOIN Period p ON p.period_ID = tsp.period_ID
			 WHERE tsp.time_Slot_ID = cs.time_Slot_ID) AS meeting_time
		FROM Course_Section cs
		JOIN Course c ON c.course_ID = cs.course_ID
		JOIN Department d ON d.dept_ID = c.dept_ID
		JOIN Semester s ON s.semester_ID = cs.semester_ID
		JOIN User u ON u.user_ID = cs.faculty_ID
		WHERE cs.semester_ID IN (1, 2)";
	$params = [];
} else {
	$sql = "SELECT
			cs.CRN,
			cs.section_No,
			cs.available_Seats,
			cs.is_Cancelled,
			cs.time_Slot_ID,
			cs.lecture_ID,
			cs.lab_ID,
			cs.faculty_ID,
			cs.course_ID,
			cs.semester_ID,
			c.course_Name,
			c.course_Credits,
			d.dept_Name,
			s.semester_Name,
			s.semester_Start,
			CONCAT(u.first_Name, ' ', u.last_Name) AS instructor,
			(SELECT COUNT(*) FROM Enrollment e WHERE e.CRN = cs.CRN) AS enrolled,
			(SELECT GROUP_CONCAT(dy.day_Of_Week ORDER BY dy.day_ID SEPARATOR ', ')
			 FROM Time_Slot_Day tsd
			 JOIN Day dy ON dy.day_ID = tsd.day_ID
			 WHERE tsd.time_Slot_ID = cs.time_Slot_ID) AS meeting_days,
			(SELECT CONCAT(MIN(p.start_Time), ' - ', MAX(p.end_Time))
			 FROM Time_Slot_Period tsp
			 JOIN Period p ON p.period_ID = tsp.period_ID
			 WHERE tsp.time_Slot_ID = cs.time_Slot_ID) AS meeting_time
		FROM Course_Section cs
		JOIN Course c ON c.course_ID = cs.course_ID
		JOIN Department d ON d.dept_ID = c.dept_ID
		JOIN Semester s ON s.semester_ID = cs.semester_ID
		JOIN User u ON u.user_ID = cs.faculty_ID
		WHERE 1 = 1";
	$params = [];
}

if ($filterSemester !== null) {
	$sql .= ' AND cs.semester_ID = ?';
	$params[] = $filterSemester;
}

if ($filterDept !== null) {
	$sql .= ' AND c.dept_ID = ?';
	$params[] = $filterDept;
}

if ($search !== '') {
	$sql .= ' AND (c.course_Name LIKE ? OR CAST(cs.CRN AS CHAR) LIKE ?)';
	$params[] = '%' . $search . '%';
	$params[] = '%' . $search . '%';
}

$sql .= ' ORDER BY s.semester_ID DESC, d.dept_Name, c.course_ID, cs.section_No LIMIT 100';

$sections = all_rows($pdo, $sql, $params);

$editCRN = get_int('edit');

$editSection = null;

if ($editCRN !== null) {
	$editSection = one(
		$pdo,
		'SELECT * FROM Course_Section WHERE CRN = ?',
		[$editCRN]
	);
}

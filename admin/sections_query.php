<?php

declare(strict_types=1);

// Filters and section listing for the sections page.

$filterSemester = get_int('semester');
$filterDept = get_int('dept');
$search = get_str('search', 100);

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
			(SELECT COUNT(*) FROM Enrollment e WHERE e.CRN = cs.CRN) AS enrolled
		FROM Course_Section cs
		JOIN Course c ON c.course_ID = cs.course_ID
		JOIN Department d ON d.dept_ID = c.dept_ID
		JOIN Semester s ON s.semester_ID = cs.semester_ID
		JOIN User u ON u.user_ID = cs.faculty_ID
		WHERE 1 = 1";

$params = [];

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

<?php

declare(strict_types=1);

// Report: undergraduate and graduate enrollment totals per semester.

$title = 'Undergraduate and graduate totals';
$description = 'Distinct students enrolled per semester and academic level.';

$sql = "SELECT
			s.semester_Name AS semester,
			st.student_Type AS level,
			COUNT(DISTINCT e.student_ID) AS students,
			COUNT(*) AS enrollments
		FROM Enrollment e
		JOIN Student st
			ON st.student_ID = e.student_ID
		JOIN Semester s
			ON s.semester_ID = e.semester_ID";

$params = [];

if ($semesterID !== null) {
	$sql .= ' WHERE e.semester_ID = ?';
	$params[] = $semesterID;
}

$sql .= ' GROUP BY s.semester_ID, s.semester_Name, st.student_Type
		ORDER BY s.semester_ID DESC, st.student_Type';

$rows = all_rows($pdo, $sql, $params);

$headers = [
	'Semester',
	'Level',
	'Students',
	'Enrollments'
];

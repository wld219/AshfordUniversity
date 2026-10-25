<?php

declare(strict_types=1);

// Report: grade distribution per department and course (counts only).
// Individual students are never identified.

$title = 'Grade summaries by department and course';
$description = 'Grade counts per course. Individual students are never identified.';

$sql = "SELECT
			d.dept_Name AS department,
			c.course_ID AS course,
			c.course_Name AS title,
			e.Grade AS grade,
			COUNT(*) AS students
		FROM Enrollment e
		JOIN Course_Section cs
			ON cs.CRN = e.CRN
		JOIN Course c
			ON c.course_ID = cs.course_ID
		JOIN Department d
			ON d.dept_ID = c.dept_ID
		WHERE e.semester_ID = ?
		AND e.Grade IS NOT NULL";

$params = [$semesterID];

if ($deptID !== null) {
	$sql .= ' AND c.dept_ID = ?';
	$params[] = $deptID;
}

$sql .= " GROUP BY d.dept_Name, c.course_ID, c.course_Name, e.Grade
		ORDER BY d.dept_Name, c.course_ID, e.Grade";

$rows = all_rows($pdo, $sql, $params);

$headers = [
	'Department',
	'Course',
	'Title',
	'Grade',
	'Students'
];

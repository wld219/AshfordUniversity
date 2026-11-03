<?php

declare(strict_types=1);

// Report: enrollment totals per course (anonymous head counts).
// Uses $pdo, $semesterID, $deptID from the entry point; sets $rows,
// $headers, $title, $description.

$title = 'Course enrollment totals';
$description = 'Number of students enrolled in each course, by section semester.';

$sql = "SELECT
			d.dept_Name AS department,
			c.course_ID AS course,
			c.course_Name AS title,
			c.course_Credits AS credits,
			COUNT(e.student_ID) AS enrolled
		FROM Course_Section cs
		JOIN Course c
			ON c.course_ID = cs.course_ID
		JOIN Department d
			ON d.dept_ID = c.dept_ID
		LEFT JOIN Enrollment e
			ON e.CRN = cs.CRN
			AND e.semester_ID = cs.semester_ID
		WHERE cs.semester_ID = ?";

$params = [$semesterID];

if ($deptID !== null) {
	$sql .= ' AND c.dept_ID = ?';
	$params[] = $deptID;
}

$sql .= " GROUP BY d.dept_Name, c.course_ID, c.course_Name, c.course_Credits
		ORDER BY d.dept_Name, c.course_ID";

$rows = all_rows($pdo, $sql, $params);

$headers = [
	'Department',
	'Course',
	'Title',
	'Credits',
	'Enrolled'
];

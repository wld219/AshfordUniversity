<?php

declare(strict_types=1);

// Report: section capacity and utilization per course.

$title = 'Section capacity and utilization';
$description = 'Total scheduled room capacity versus enrolled students per course.';

$sql = "SELECT
			s.semester_Name AS semester,
			d.dept_Name AS department,
			c.course_ID AS course,
			c.course_Name AS title,
			COUNT(cs.CRN) AS sections,
			SUM(COALESCE(l.avail_Seats, b.no_Of_Workstations, 0)) AS capacity,
			(SELECT COUNT(*)
			FROM Enrollment e
			JOIN Course_Section cs2 ON cs2.CRN = e.CRN
			WHERE cs2.course_ID = c.course_ID
				AND cs2.semester_ID = cs.semester_ID
				AND cs2.is_Cancelled = 0) AS enrolled
		FROM Course_Section cs
		JOIN Course c ON c.course_ID = cs.course_ID
		JOIN Department d ON d.dept_ID = c.dept_ID
		JOIN Semester s ON s.semester_ID = cs.semester_ID
		LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
		LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID
		WHERE cs.is_Cancelled = 0";

$params = [];

if ($semesterID !== null) {
	$sql .= ' AND cs.semester_ID = ?';
	$params[] = $semesterID;
}

if ($deptID !== null) {
	$sql .= ' AND c.dept_ID = ?';
	$params[] = $deptID;
}

$sql .= ' GROUP BY s.semester_ID, s.semester_Name, d.dept_Name,
			c.course_ID, c.course_Name
		ORDER BY s.semester_ID DESC, d.dept_Name, c.course_ID';

$rows = all_rows($pdo, $sql, $params);

// Add a utilization percentage per course (computed in application code)
$rows = array_map(
	static function (array $row): array {

		$capacity = (int) $row['capacity'];
		$enrolled = (int) $row['enrolled'];

		$row['utilization'] = $capacity > 0
			? round($enrolled / $capacity * 100, 1) . '%'
			: 'N/A';

		return $row;
	},
	$rows
);

$headers = [
	'Semester',
	'Department',
	'Course',
	'Title',
	'Sections',
	'Capacity',
	'Enrolled',
	'Utilization'
];

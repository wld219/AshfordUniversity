<?php

declare(strict_types=1);

// Report: department summary — enrollment, sections, faculty, courses per department.
// All aggregates; no individual student or faculty data.

$title = 'Department summary';
$description = 'Department-level overview: enrolled students, course sections, faculty count, and courses offered.';

$sql = "SELECT
		d.dept_Name AS department,
		COUNT(DISTINCT c.course_ID) AS courses_offered,
		COUNT(DISTINCT cs.CRN) AS sections,
		COALESCE(SUM(CASE WHEN cs.is_Cancelled = 0 THEN 1 ELSE 0 END), 0) AS active_sections,
		COUNT(DISTINCT f.faculty_ID) AS faculty,
		COALESCE(SUM(e.enrolled_count), 0) AS total_enrolled
	FROM Department d
	LEFT JOIN Course c
		ON c.dept_ID = d.dept_ID
	LEFT JOIN Course_Section cs
		ON cs.course_ID = c.course_ID
	LEFT JOIN Faculty f
		ON f.faculty_ID = cs.faculty_ID
	LEFT JOIN (
		SELECT cs2.CRN, COUNT(e2.student_ID) AS enrolled_count
		FROM Course_Section cs2
		JOIN Enrollment e2 ON e2.CRN = cs2.CRN AND e2.semester_ID = cs2.semester_ID
		WHERE cs2.is_Cancelled = 0";

$params = [];

if ($semesterID !== null) {
    $sql .= ' AND cs2.semester_ID = ?';
    $params[] = $semesterID;
}

$sql .= "		GROUP BY cs2.CRN
	) e ON e.CRN = cs.CRN
	WHERE 1 = 1";

if ($semesterID !== null) {
    $sql .= ' AND cs.semester_ID = ?';
    $params[] = $semesterID;
}

if ($deptID !== null) {
    $sql .= ' AND d.dept_ID = ?';
    $params[] = $deptID;
}

$sql .= ' GROUP BY d.dept_ID, d.dept_Name
		ORDER BY total_enrolled DESC, d.dept_Name';

$rows = all_rows($pdo, $sql, $params);

// Compute average enrollment per section
$rows = array_map(
	static function (array $row): array {
		$sections = (int) $row['sections'];
		$enrolled = (int) $row['total_enrolled'];
		$row['avg_per_section'] = $sections > 0
			? round($enrolled / $sections, 1)
			: 'N/A';
		return $row;
	},
	$rows
);

$headers = [
	'Department',
	'Courses Offered',
	'Sections',
	'Active Sections',
	'Faculty',
	'Total Enrolled',
	'Avg per Section',
];

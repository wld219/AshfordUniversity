<?php

declare(strict_types=1);

// Report: enrollment trends over time (anonymous head counts per semester).
// Shows distinct students enrolled per semester, by academic level.

$title = 'Enrollment trends over time';
$description = 'Distinct students enrolled per semester, broken down by academic level. No individual student data is shown.';

$sql = "SELECT
		s.semester_Name AS semester,
		st.student_Type AS level,
		COUNT(DISTINCT e.student_ID) AS students,
		COUNT(*) AS enrollments
	FROM Enrollment e
	JOIN Student st
		ON st.student_ID = e.student_ID
	JOIN Semester s
		ON s.semester_ID = e.semester_ID
	JOIN Course_Section cs
		ON cs.CRN = e.CRN AND cs.semester_ID = e.semester_ID
	JOIN Course c
		ON c.course_ID = cs.course_ID";

$params = [];

// Enrollment trends should not be limited to one semester by default;
// if a semester filter is provided, apply it.
if ($semesterID !== null) {
	$sql .= ' WHERE e.semester_ID = ?';
	$params[] = $semesterID;
}

if ($deptID !== null) {
	$sql .= (strpos($sql, 'WHERE') !== false ? ' AND' : ' WHERE') . ' c.dept_ID = ?';
	$params[] = $deptID;
}

$sql .= ' GROUP BY s.semester_ID, s.semester_Name, st.student_Type
		ORDER BY s.semester_ID DESC, st.student_Type';

$rows = all_rows($pdo, $sql, $params);

// Compute semester totals and growth rates for context
$semesterTotals = [];
foreach ($rows as $row) {
    $sem = (string) $row['semester'];
    if (!isset($semesterTotals[$sem])) {
        $semesterTotals[$sem] = ['students' => 0, 'enrollments' => 0];
    }
    $semesterTotals[$sem]['students'] += (int) $row['students'];
    $semesterTotals[$sem]['enrollments'] += (int) $row['enrollments'];
}

// Add growth rate column (computed from previous semester)
$sortedSemesters = array_keys($semesterTotals);
rsort($sortedSemesters);
$previousTotal = null;
foreach ($sortedSemesters as $sem) {
    $currentTotal = $semesterTotals[$sem]['students'];
    $growthRate = null;
    if ($previousTotal !== null && $previousTotal > 0) {
        $growthRate = round(($currentTotal - $previousTotal) / $previousTotal * 100, 1);
    }
    $semesterTotals[$sem]['growth_rate'] = $growthRate;
    $previousTotal = $currentTotal;
}

$headers = [
	'Semester',
	'Level',
	'Students',
	'Enrollments',
];

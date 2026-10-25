<?php

declare(strict_types=1);

// Data for the majors & minors page.

// Primary major
$student = one(
	$pdo,
	'SELECT major_ID FROM Student WHERE student_ID = ?',
	[$studentID]
);

$primaryMajorID = (int) ($student['major_ID'] ?? 0);

// Declared majors
$declaredMajors = all_rows(
	$pdo,
	"SELECT m.major_ID, m.major_Name, d.dept_Name, sm.date_Of_Choice
	FROM Student_Major sm
	JOIN Major m ON m.major_ID = sm.major_ID
	JOIN Department d ON d.dept_ID = m.dept_ID
	WHERE sm.student_ID = ?
	ORDER BY sm.date_Of_Choice",
	[$studentID]
);

$declaredMajorIDs = array_map(
	static fn(array $row): int => (int) $row['major_ID'],
	$declaredMajors
);

// Majors available to add
$availableMajors = all_rows(
	$pdo,
	"SELECT m.major_ID, m.major_Name, d.dept_Name
	FROM Major m
	JOIN Department d ON d.dept_ID = m.dept_ID
	ORDER BY m.major_Name"
);

$availableMajors = array_values(
	array_filter(
		$availableMajors,
		static fn(array $row): bool => !in_array((int) $row['major_ID'], $declaredMajorIDs, true)
	)
);

// Declared minor
$declaredMinor = one(
	$pdo,
	"SELECT mn.minor_ID, mn.minor_Name, d.dept_Name, sm.date_Of_Choice
	FROM Student_Minor sm
	JOIN Minor mn ON mn.minor_ID = sm.minor_ID
	JOIN Department d ON d.dept_ID = mn.dept_ID
	WHERE sm.student_ID = ?
	LIMIT 1",
	[$studentID]
);

// Minors available to add
$availableMinors = all_rows(
	$pdo,
	"SELECT mn.minor_ID, mn.minor_Name, d.dept_Name
	FROM Minor mn
	JOIN Department d ON d.dept_ID = mn.dept_ID
	ORDER BY mn.minor_Name"
);

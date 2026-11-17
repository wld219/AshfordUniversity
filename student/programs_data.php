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

// Program comparison data: requirement counts for each available major
$majorRequirementCounts = [];
foreach ($availableMajors as $major) {
    $count = one(
        $pdo,
        'SELECT COUNT(*) AS total FROM Major_Requirement WHERE major_ID = ?',
        [(int) $major['major_ID']]
    );
    $majorRequirementCounts[(int) $major['major_ID']] = (int) ($count['total'] ?? 0);
}

// Credits already earned toward each available major (based on completed courses)
$majorCreditProgress = [];
$studentHistory = all_rows(
    $pdo,
    'SELECT course_ID, Grade FROM Student_History WHERE student_ID = ? AND Grade IS NOT NULL',
    [$studentID]
) ?? [];
$completedCourseIDs = array_map(
    static fn($row) => (string) $row['course_ID'],
    $studentHistory
);

if ($completedCourseIDs) {
    $placeholders = implode(',', array_fill(0, count($completedCourseIDs), '?'));
    foreach ($availableMajors as $major) {
        $matched = one(
            $pdo,
            "SELECT COUNT(*) AS matched FROM Major_Requirement mr
             WHERE mr.major_ID = ? AND mr.course_ID IN ($placeholders)",
            array_merge([(int) $major['major_ID']], $completedCourseIDs)
        );
        $totalReq = $majorRequirementCounts[(int) $major['major_ID']] ?? 0;
        $majorCreditProgress[(int) $major['major_ID']] = [
            'matched' => (int) ($matched['matched'] ?? 0),
            'total' => $totalReq,
            'pct' => $totalReq > 0 ? round(($matched['matched'] ?? 0) / $totalReq * 100, 0) : 0,
        ];
    }
}

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

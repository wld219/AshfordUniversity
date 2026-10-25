<?php

declare(strict_types=1);

// Data for the degree audit: declared programs evaluated against the
// student's best recorded grade for each required course.

// Best grade earned per course (for requirement checks)
$history = all_rows(
	$pdo,
	"SELECT course_ID, Grade
	FROM Student_History
	WHERE student_ID = ?
	AND Grade IS NOT NULL",
	[$studentID]
);

$bestGrades = [];

foreach ($history as $row) {

	$courseID = (int) $row['course_ID'];
	$points = grade_points($row['Grade']);

	if ($points === null) {
		continue;
	}

	if (
		!isset($bestGrades[$courseID]) ||
		$points > $bestGrades[$courseID]['points']
	) {
		$bestGrades[$courseID] = [
			'grade' => (string) $row['Grade'],
			'points' => $points,
		];
	}
}

// Evaluate a requirement list against the student's history
function audit_requirements(array $requirements, array $bestGrades): array
{
	$completed = 0;
	$rows = [];

	foreach ($requirements as $requirement) {

		$courseID = (int) $requirement['course_ID'];
		$earned = $bestGrades[$courseID] ?? null;
		$met = $earned !== null
			&& grade_meets($earned['grade'], (string) $requirement['min_Grade_Req']);

		if ($met) {
			$completed++;
		}

		$rows[] = [
			'course_ID' => $courseID,
			'course_Name' => (string) $requirement['course_Name'],
			'min_Grade_Req' => (string) $requirement['min_Grade_Req'],
			'grade' => $earned['grade'] ?? null,
			'met' => $met,
		];
	}

	return [
		'rows' => $rows,
		'completed' => $completed,
		'total' => count($requirements),
	];
}

// Student's declared majors and minor
$declaredMajors = all_rows(
	$pdo,
	"SELECT m.major_ID, m.major_Name, d.dept_Name
	FROM Student_Major sm
	JOIN Major m ON m.major_ID = sm.major_ID
	JOIN Department d ON d.dept_ID = m.dept_ID
	WHERE sm.student_ID = ?
	ORDER BY sm.date_Of_Choice",
	[$studentID]
);

$primaryMajorID = (int) (one(
	$pdo,
	'SELECT major_ID FROM Student WHERE student_ID = ?',
	[$studentID]
)['major_ID'] ?? 0);

$declaredMinor = one(
	$pdo,
	"SELECT mn.minor_ID, mn.minor_Name
	FROM Student_Minor sm
	JOIN Minor mn ON mn.minor_ID = sm.minor_ID
	WHERE sm.student_ID = ?
	LIMIT 1",
	[$studentID]
);

$audits = [];

foreach ($declaredMajors as $major) {

	$requirements = all_rows(
		$pdo,
		"SELECT mr.course_ID, mr.min_Grade_Req, c.course_Name
		FROM Major_Requirement mr
		JOIN Course c ON c.course_ID = mr.course_ID
		WHERE mr.major_ID = ?
		ORDER BY mr.course_ID",
		[(int) $major['major_ID']]
	);

	$audits[] = [
		'type' => 'Major',
		'name' => (string) $major['major_Name'],
		'primary' => (int) $major['major_ID'] === $primaryMajorID,
	] + audit_requirements($requirements, $bestGrades);
}

if ($declaredMinor) {

	$requirements = all_rows(
		$pdo,
		"SELECT mr.course_ID, mr.min_Grade_Req, c.course_Name
		FROM Minor_Requirement mr
		JOIN Course c ON c.course_ID = mr.course_ID
		WHERE mr.minor_ID = ?
		ORDER BY mr.course_ID",
		[(int) $declaredMinor['minor_ID']]
	);

	$audits[] = [
		'type' => 'Minor',
		'name' => (string) $declaredMinor['minor_Name'],
		'primary' => false,
	] + audit_requirements($requirements, $bestGrades);
}

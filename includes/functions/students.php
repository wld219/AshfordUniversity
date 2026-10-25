<?php

declare(strict_types=1);

// Student credit limits.

// Find the maximum number of credits a student can take
function student_credit_limit(
	PDO $pdo,
	int $studentID
): int {

	// Full-time undergraduate: limit stored on the student record.
	$fullTime = one(
		$pdo,
		"SELECT max_Credits
			FROM Full_Time_Undergraduate
			WHERE student_ID = ?",
		[$studentID]
	);

	if ($fullTime) {
		$limit = (int) $fullTime['max_Credits'];

		return $limit > 0
			? $limit
			: DEFAULT_UNDERGRADUATE_CREDIT_LIMIT;
	}


	// Part-time undergraduate: limit stored on the student record.
	$partTime = one(
		$pdo,
		"SELECT max_Credits
		FROM Part_Time_Undergraduate
		WHERE student_ID = ?",
		[$studentID]
	);

	if ($partTime) {
		$limit = (int) $partTime['max_Credits'];

		return $limit > 0
			? $limit
			: DEFAULT_PART_TIME_UNDERGRADUATE_CREDIT_LIMIT;
	}


	// Graduate students use the documented graduate defaults.
	$graduate = one(
		$pdo,
		"SELECT graduate_Student_Type
		FROM Graduate_Student
		WHERE student_ID = ?",
		[$studentID]
	);

	if ($graduate) {
		return $graduate['graduate_Student_Type'] === 'Part-Time'
			? GRADUATE_PART_TIME_CREDIT_LIMIT
			: GRADUATE_FULL_TIME_CREDIT_LIMIT;
	}


	return DEFAULT_UNDERGRADUATE_CREDIT_LIMIT;
}

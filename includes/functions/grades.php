<?php

declare(strict_types=1);

// Grade conversion helpers.

// Convert a letter grade to grade points (null for non-grade values)
function grade_points(?string $grade): ?float
{
	$grade = strtoupper(trim((string) $grade));

	$points = [
		'A'  => 4.0,
		'A-' => 3.7,
		'B+' => 3.3,
		'B'  => 3.0,
		'B-' => 2.7,
		'C+' => 2.3,
		'C'  => 2.0,
		'C-' => 1.7,
		'D+' => 1.3,
		'D'  => 1.0,
		'F'  => 0.0,
	];

	return $points[$grade] ?? null;
}

/**
 * Resolve a student-facing course status without treating registrations as
 * completed history. Spring 2027 stays Registered because future Enrollment
 * grade values are not transcript results.
 */
function student_course_status(?string $grade, string $semesterName, bool $isEnrollment): string
{
	if ($isEnrollment && $semesterName === 'Spring 2027') {
		return 'Registered';
	}

	$normalizedGrade = strtoupper(trim((string) $grade));
	if ($normalizedGrade === '') {
		if ($isEnrollment && $semesterName === 'Fall 2026') {
			return 'In Progress';
		}

		return $isEnrollment ? 'Registered' : 'Not Started';
	}

	if (in_array($normalizedGrade, ['W', 'WD', 'WITHDRAWN'], true)) {
		return 'Withdrawn';
	}

	$points = grade_points($normalizedGrade);
	if ($points === null) {
		// Incomplete or other non-final grade codes do not become a pass/fail.
		return 'In Progress';
	}

	return $points >= 1.0 ? 'Completed' : 'Failed';
}


// Does a recorded grade satisfy a minimum grade requirement?
function grade_meets(?string $grade, string $minGrade): bool
{
	$earned = grade_points($grade);
	$required = grade_points($minGrade);

	if ($earned === null || $required === null) {
		return false;
	}

	return $earned >= $required;
}

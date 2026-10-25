<?php

declare(strict_types=1);

// Student-side registration checks.

// Cancelled sections cannot receive new registrations.
function section_is_cancelled(PDO $pdo, int $crn): bool
{
	$section = one(
		$pdo,
		"SELECT is_Cancelled
		FROM Course_Section
		WHERE CRN = ?",
		[$crn]
	);

	return $section !== null && (int) $section['is_Cancelled'] === 1;
}

// Load a section with its course information for the checks below.
function registration_section(PDO $pdo, int $crn): ?array
{
	return one(
		$pdo,
		"SELECT
			Course_Section.*,
			Course.course_Credits,
			Course.course_Type,
			Course.course_ID
		FROM Course_Section
		JOIN Course
			ON Course.course_ID = Course_Section.course_ID
		WHERE Course_Section.CRN = ?",
		[$crn]
	);
}

// Student holds block registration.
function check_student_holds(PDO $pdo, int $studentID): ?string
{
	$hold = one(
		$pdo,
		"SELECT 1
		FROM Student_Hold
		WHERE student_ID = ?
		LIMIT 1",
		[$studentID]
	);

	return $hold
		? 'Registration blocked: you have a hold.'
		: null;
}

// A section must have seats left.
function check_section_seats(array $section): ?string
{
	return (int) $section['available_Seats'] <= 0
		? 'Registration blocked: no seats are available.'
		: null;
}

// A student cannot register for the same section twice.
function check_already_registered(PDO $pdo, int $studentID, int $crn): ?string
{
	$registered = one(
		$pdo,
		"SELECT 1
		FROM Enrollment
		WHERE student_ID = ?
		AND CRN = ?",
		[$studentID, $crn]
	);

	return $registered
		? 'You are already registered for this section.'
		: null;
}

// A passing course cannot be retaken.
function check_course_already_passed(PDO $pdo, int $studentID, array $section): ?string
{
	$passed = one(
		$pdo,
		"SELECT 1
		FROM Student_History
		WHERE student_ID = ?
		AND course_ID = ?
		AND Grade IN (
			'A', 'A-', 'B+', 'B', 'B-',
			'C+', 'C', 'C-', 'D+', 'D'
		)
		LIMIT 1",
		[$studentID, $section['course_ID']]
	);

	return $passed
		? 'Registration blocked: you already passed this course.'
		: null;
}

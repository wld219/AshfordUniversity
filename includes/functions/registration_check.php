<?php

declare(strict_types=1);

// Section eligibility: run every registration check in order and return
// the first blocking message, or "Eligible".

// Check if a student is allowed to register for a course
function registration_check(
	PDO $pdo,
	int $studentID,
	int $crn
): array {

	$section = registration_section($pdo, $crn);

	if (!$section) {
		return [false, 'Course section not found.'];
	}

	if (section_is_cancelled($pdo, $crn)) {
		return [false, 'Registration blocked: this section has been cancelled.'];
	}

	[$windowOpen, , $windowMessage] = registration_window_status(
		$pdo,
		(int) $section['semester_ID']
	);

	if (!$windowOpen) {
		return [false, 'Registration blocked: ' . $windowMessage];
	}

	// Order matters: checks that describe the student's own state come
	// first so the message names the real reason. A student who is already
	// enrolled in a section should be told that, not that the section is
	// full.
	$checks = [
		check_already_registered($pdo, $studentID, $crn),
		check_course_already_passed($pdo, $studentID, $section),
		check_student_holds($pdo, $studentID),
		check_course_level($pdo, $studentID, $section),
		check_prerequisites($pdo, $studentID, $section),
		check_credit_limit($pdo, $studentID, $section),
		check_time_conflict($pdo, $studentID, $section),
		check_duplicate_course_semester($pdo, $studentID, $section),
		check_section_seats($section),
	];

	foreach ($checks as $message) {

		if ($message !== null) {
			return [false, $message];
		}
	}

	return [true, 'Eligible'];
}

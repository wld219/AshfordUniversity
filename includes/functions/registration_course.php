<?php

declare(strict_types=1);

// Course-side registration checks: level, prerequisites, credits, time.

// Undergraduate and graduate levels cannot mix.
function check_course_level(PDO $pdo, int $studentID, array $section): ?string
{
	$student = one(
		$pdo,
		"SELECT student_Type
		FROM Student
		WHERE student_ID = ?",
		[$studentID]
	);

	if (!$student) {
		return null;
	}

	if (
		$student['student_Type'] === 'Undergraduate' &&
		$section['course_Type'] === 'Graduate'
	) {
		return 'Undergraduate students cannot register for graduate courses.';
	}

	if (
		$student['student_Type'] === 'Graduate' &&
		$section['course_Type'] === 'Undergraduate'
	) {
		return 'Graduate students cannot register for undergraduate courses.';
	}

	return null;
}

// Every prerequisite must be completed at the required minimum grade.
function check_prerequisites(PDO $pdo, int $studentID, array $section): ?string
{
	$prerequisites = all_rows(
		$pdo,
		"SELECT prerequisite_course_ID, min_Grade_Req
		FROM Course_Prerequisite
		WHERE course_ID = ?",
		[$section['course_ID']]
	);

	foreach ($prerequisites as $prerequisite) {

		// Order by grade so the best recorded attempt is compared, not an
		// arbitrary row from an unordered LIMIT 1.
		$completed = one(
			$pdo,
			"SELECT Grade
			FROM Student_History
			WHERE student_ID = ?
			AND course_ID = ?
			AND Grade IN (
				'A', 'A-', 'B+', 'B', 'B-',
				'C+', 'C', 'C-', 'D+', 'D', 'F'
			)
			ORDER BY FIELD(
				Grade,
				'A', 'A-', 'B+', 'B', 'B-',
				'C+', 'C', 'C-', 'D+', 'D', 'F'
			)
			LIMIT 1",
			[$studentID, $prerequisite['prerequisite_course_ID']]
		);

		if (
			!$completed ||
			!grade_meets(
				$completed['Grade'],
				(string) $prerequisite['min_Grade_Req']
			)
		) {
			return 'Registration blocked: prerequisite requirements are not satisfied.';
		}
	}

	return null;
}

// Current credits plus the new course must stay within the credit limit.
function check_credit_limit(PDO $pdo, int $studentID, array $section): ?string
{
	$credits = one(
		$pdo,
		"SELECT
			COALESCE(SUM(Course.course_Credits), 0) AS total
		FROM Enrollment
		JOIN Course_Section
			ON Course_Section.CRN = Enrollment.CRN
		JOIN Course
			ON Course.course_ID = Course_Section.course_ID
		WHERE Enrollment.student_ID = ?
		AND Enrollment.semester_ID = ?",
		[$studentID, $section['semester_ID']]
	);

	$creditLimit = student_credit_limit($pdo, $studentID);

	$currentCredits = (int) ($credits['total'] ?? 0);
	$courseCredits = (int) $section['course_Credits'];

	return $currentCredits + $courseCredits > $creditLimit
		? "Registration blocked: this would exceed your {$creditLimit}-credit limit."
		: null;
}

// No overlapping meeting time with an already registered section.
function check_time_conflict(PDO $pdo, int $studentID, array $section): ?string
{
	$sql = "SELECT 1
			FROM Enrollment
			JOIN Course_Section
				ON Course_Section.CRN = Enrollment.CRN
			JOIN Time_Slot_Day my_day
				ON my_day.time_Slot_ID = Course_Section.time_Slot_ID
			JOIN Time_Slot_Day new_day
				ON new_day.day_ID = my_day.day_ID
				AND new_day.time_Slot_ID = ?
			WHERE Enrollment.student_ID = ?
				AND Enrollment.semester_ID = ?
				AND EXISTS (
					SELECT 1
					FROM Time_Slot_Period my_period
					JOIN Period my_time
						ON my_time.period_ID = my_period.period_ID
					JOIN Time_Slot_Period new_period
						ON new_period.time_Slot_ID = ?
					JOIN Period new_time
						ON new_time.period_ID = new_period.period_ID
					WHERE my_period.time_Slot_ID = Course_Section.time_Slot_ID
						AND my_time.start_Time < new_time.end_Time
						AND new_time.start_Time < my_time.end_Time
				)
			LIMIT 1";

	$conflict = one(
		$pdo,
		$sql,
		[
			$section['time_Slot_ID'],
			$studentID,
			$section['semester_ID'],
			$section['time_Slot_ID']
		]
	);

	return $conflict
		? 'Registration blocked: this course has a time conflict.'
		: null;
}

// A student cannot register for the same course more than once per semester
// (even under a different section/CRN).
function check_duplicate_course_semester(
	PDO $pdo,
	int $studentID,
	array $section
): ?string {
	$duplicate = one(
		$pdo,
		"SELECT 1
			FROM Enrollment e
			JOIN Course_Section cs ON cs.CRN = e.CRN
			WHERE e.student_ID = ?
				AND e.semester_ID = ?
				AND cs.course_ID = ?
				AND e.CRN <> ?
			LIMIT 1",
		[
			$studentID,
			$section['semester_ID'],
			$section['course_ID'],
			$section['CRN']
		]
	);

	return $duplicate
		? 'Registration blocked: you are already registered for this course this semester.'
		: null;
}

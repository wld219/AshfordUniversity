<?php

declare(strict_types=1);

// Attendance and grade submission windows.

// Is the section currently meeting (today is a meeting day and the current
// time is inside one of its periods)? Attendance may only be recorded then.
function section_is_meeting_now(PDO $pdo, int $crn): bool
{
	$section = one(
		$pdo,
		"SELECT time_Slot_ID
		FROM Course_Section
		WHERE CRN = ?",
		[$crn]
	);

	if (!$section) {
		return false;
	}

	$timeSlotID = (int) $section['time_Slot_ID'];

	// Today must be one of the section's meeting days
	$todayDay = one(
		$pdo,
		"SELECT d.day_ID
		FROM Time_Slot_Day tsd
		JOIN Day d ON d.day_ID = tsd.day_ID
		WHERE tsd.time_Slot_ID = ?
		AND d.day_Of_Week = DAYNAME(CURDATE())
		LIMIT 1",
		[$timeSlotID]
	);

	if (!$todayDay) {
		return false;
	}

	// The current time must fall inside one of its periods
	$inPeriod = one(
		$pdo,
		"SELECT 1
		FROM Time_Slot_Period tsp
		JOIN Period p ON p.period_ID = tsp.period_ID
		WHERE tsp.time_Slot_ID = ?
		AND CURTIME() >= p.start_Time
		AND CURTIME() <= p.end_Time
		LIMIT 1",
		[$timeSlotID]
	);

	return $inPeriod !== null;
}

// The attendance window for the current moment: allowed only while the
// section is actually meeting, and never for a past date.
function attendance_window_status(PDO $pdo, int $crn): array
{
	if (section_is_cancelled($pdo, $crn)) {
		return [false, 'This section has been cancelled.'];
	}

	if (!section_is_meeting_now($pdo, $crn)) {
		return [
			false,
			'Attendance can only be recorded while the section is meeting.'
		];
	}

	return [true, 'Open'];
}

// May a faculty member submit or change a grade for this student in this
// section right now?
//
// Rules:
//   - The semester grade deadline must not have passed, or
//   - an administrator-approved Grade_Exception must exist for the student
//     and section.
//   - When no deadline is configured, submission stays open and the
//     exception workflow is still available (documented behaviour).
function grade_submission_status(
	PDO $pdo,
	int $studentID,
	int $crn
): array {

	$section = one(
		$pdo,
		"SELECT cs.semester_ID, s.grade_Deadline
		FROM Course_Section cs
		JOIN Semester s ON s.semester_ID = cs.semester_ID
		WHERE cs.CRN = ?",
		[$crn]
	);

	if (!$section) {
		return [false, 'Section not found.'];
	}

	if ($section['grade_Deadline'] !== null) {

		$deadline = new DateTimeImmutable((string) $section['grade_Deadline']);

		if (new DateTimeImmutable('now') > $deadline) {

			// Only an administrator-approved exception reopens submission.
			// A row with no approving user is still pending, so it does not
			// qualify.
			$exception = one(
				$pdo,
				"SELECT 1
				FROM Grade_Exception
				WHERE student_ID = ?
				AND CRN = ?
				AND approved_By_User_ID IS NOT NULL
				LIMIT 1",
				[$studentID, $crn]
			);

			if ($exception) {
				return [true, 'Approved grade exception.'];
			}

			return [false, 'The grade submission deadline for this semester has passed.'];
		}
	}

	return [true, 'Open'];
}

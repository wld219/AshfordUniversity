<?php

declare(strict_types=1);

/**
 * Provisional graduate semester full-time threshold. This is configurable
 * here pending confirmation of the university's graduate enrollment policy.
 */
const GRADUATE_SEMESTER_FULL_TIME_CREDITS = 9;

/** Credit totals above this value are surfaced to Admin for review. */
const STUDENT_SEMESTER_CREDIT_REVIEW_THRESHOLD = 20;

/**
 * Calculate semester enrollment status from active Enrollment records.
 * Duplicate registrations remain represented as recorded; this function does
 * not alter or silently deduplicate academic records.
 */
function student_semester_enrollment_status(
	PDO $pdo,
	int $studentID,
	int $semesterID
): ?array {
	$row = one(
		$pdo,
		"SELECT st.student_Type,
				COALESCE((
					SELECT SUM(c.course_Credits)
					FROM Enrollment e
					JOIN Course_Section cs
						ON cs.CRN = e.CRN
						AND cs.semester_ID = e.semester_ID
					JOIN Course c ON c.course_ID = cs.course_ID
					WHERE e.student_ID = st.student_ID
						AND e.semester_ID = ?
						AND cs.is_Cancelled = 0
				), 0) AS registered_credits
		FROM Student st
		WHERE st.student_ID = ?",
		[$semesterID, $studentID]
	);

	if ($row === null) {
		return null;
	}

	$academicLevel = (string) $row['student_Type'];
	$registeredCredits = (int) $row['registered_credits'];
	$fullTimeThreshold = $academicLevel === 'Graduate'
		? GRADUATE_SEMESTER_FULL_TIME_CREDITS
		: 12;

	if ($academicLevel === 'Undergraduate') {
		$status = match (true) {
			$registeredCredits === 0 => 'Not Enrolled',
			$registeredCredits < 4 => 'Below PT Minimum',
			$registeredCredits <= 10 => 'Part-Time (PT)',
			$registeredCredits === 11 => 'Unclassified / Review',
			$registeredCredits <= 16 => 'Full-Time (FT)',
			default => 'Overloaded',
		};
	} else {
		$status = $registeredCredits === 0
			? 'Not Enrolled'
			: ($registeredCredits >= $fullTimeThreshold ? 'Full-Time (FT)' : 'Part-Time (PT)');
	}

	return [
		'academic_level' => $academicLevel,
		'registered_credits' => $registeredCredits,
		'status' => $status,
		'full_time_threshold' => $fullTimeThreshold,
		'graduate_threshold_provisional' => $academicLevel === 'Graduate',
		'needs_admin_review' => $academicLevel === 'Undergraduate'
			? ($registeredCredits === 11 || $registeredCredits > 16)
			: $registeredCredits > STUDENT_SEMESTER_CREDIT_REVIEW_THRESHOLD,
	];
}

/**
 * Return one row per active enrollment for a student and semester.
 * Enrollment rows are deliberately not deduplicated.
 */
function student_semester_registered_courses(
	PDO $pdo,
	int $studentID,
	int $semesterID,
	bool $ungradedOnly = false
): array {
	$ungradedFilter = $ungradedOnly ? ' AND e.Grade IS NULL' : '';
	$courses = all_rows(
		$pdo,
		"SELECT e.CRN,
				c.course_ID,
				c.course_Name,
				c.course_Credits,
				cs.time_Slot_ID,
				CONCAT_WS(' ', u.first_Name, u.last_Name) AS faculty_Name,
				COALESCE((
					SELECT GROUP_CONCAT(d.day_Of_Week ORDER BY d.day_ID SEPARATOR ', ')
					FROM Time_Slot_Day tsd
					JOIN Day d ON d.day_ID = tsd.day_ID
					WHERE tsd.time_Slot_ID = cs.time_Slot_ID
				), '') AS meeting_days
		FROM Enrollment e
		JOIN Course_Section cs
			ON cs.CRN = e.CRN
			AND cs.semester_ID = e.semester_ID
		JOIN Course c ON c.course_ID = cs.course_ID
		LEFT JOIN Faculty f ON f.faculty_ID = cs.faculty_ID
		LEFT JOIN User u ON u.user_ID = f.faculty_ID
		WHERE e.student_ID = ?
			AND e.semester_ID = ?
			AND cs.is_Cancelled = 0
			{$ungradedFilter}
		ORDER BY c.course_ID, e.CRN",
		[$studentID, $semesterID]
	);

	$meetingTimes = [];
	foreach ($courses as &$course) {
		$timeSlotID = (int) $course['time_Slot_ID'];
		if (!array_key_exists($timeSlotID, $meetingTimes)) {
			$periods = all_rows(
				$pdo,
				"SELECT p.start_Time, p.end_Time
				 FROM Time_Slot_Period tsp
				 JOIN Period p ON p.period_ID = tsp.period_ID
				 WHERE tsp.time_Slot_ID = ?
				 ORDER BY p.period_ID",
				[$timeSlotID]
			);
			$meetingTimes[$timeSlotID] = $periods
				? format_time_range(
					(string) $periods[0]['start_Time'],
					(string) $periods[count($periods) - 1]['end_Time']
				)
				: 'TBA';
		}
		$course['meeting_days'] = $course['meeting_days'] !== ''
			? (string) $course['meeting_days']
			: 'To be announced';
		$course['meeting_time'] = $meetingTimes[$timeSlotID];
		$course['registration_status'] = 'Registered';
	}
	unset($course);

	return $courses;
}

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

/**
 * Returns all course sections for the current semester that the student
 * is eligible to register for, based on:
 *   - Prerequisites satisfied (best recorded grade meets minimum)
 *   - Course level matches student level
 *   - No holds
 *   - Credit limit not exceeded
 *   - No time conflicts with existing registration
 *   - Section has available seats
 *   - Not already registered / not already passed
 *
 * @param PDO $pdo
 * @param int $studentID
 * @param int $semesterID
 * @return array<int, array<string, mixed>> Eligible sections with course details
 */
function get_eligible_courses(
	PDO $pdo,
	int $studentID,
	int $semesterID
): array {
	$sections = all_rows(
		$pdo,
		"SELECT
			cs.CRN,
			cs.section_No,
			cs.course_ID,
			course.course_Name,
			course.course_Credits,
			course.course_Type,
			course.course_DSC,
			d.dept_Name,
			cs.faculty_ID,
			CONCAT(u.first_Name, ' ', u.last_Name) AS faculty_Name,
			cs.time_Slot_ID,
			cs.available_Seats,
			cs.is_Cancelled
		FROM Course_Section cs
		JOIN Course course ON course.course_ID = cs.course_ID
		JOIN Department d ON d.dept_ID = course.dept_ID
		JOIN User u ON u.user_ID = cs.faculty_ID
		WHERE cs.semester_ID = ?
			AND cs.is_Cancelled = 0
			AND cs.available_Seats > 0
		ORDER BY d.dept_Name, course.course_ID, cs.section_No",
		[$semesterID]
	);

	if (count($sections) === 0) {
		return [];
	}

	// Load the student's best grades for prerequisite checking
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
		$courseID = $row['course_ID'];
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

	// Student level
	$student = one(
		$pdo,
		'SELECT student_Type FROM Student WHERE student_ID = ?',
		[$studentID]
	);
	$studentType = $student ? (string) $student['student_Type'] : null;

	// Current credits for credit limit check
	$currentCreditsRow = one(
		$pdo,
		"SELECT COALESCE(SUM(Course.course_Credits), 0) AS total
		FROM Enrollment
		JOIN Course_Section ON Course_Section.CRN = Enrollment.CRN
		JOIN Course ON Course.course_ID = Course_Section.course_ID
		WHERE Enrollment.student_ID = ?
			AND Enrollment.semester_ID = ?",
		[$studentID, $semesterID]
	);
	$currentCredits = (int) ($currentCreditsRow['total'] ?? 0);
	$creditLimit = student_credit_limit($pdo, $studentID);

	// Already registered course IDs this semester
	$registeredCourses = all_rows(
		$pdo,
		"SELECT DISTINCT Course.course_ID
		FROM Enrollment
		JOIN Course_Section ON Course_Section.CRN = Enrollment.CRN
		JOIN Course ON Course.course_ID = Course_Section.course_ID
		WHERE Enrollment.student_ID = ?
			AND Enrollment.semester_ID = ?",
		[$studentID, $semesterID]
	);
	$registeredCourseIDs = array_column($registeredCourses, 'course_ID');

	// Passed course IDs (grade D or above)
	$passedCourseIDs = all_rows(
		$pdo,
		"SELECT DISTINCT course_ID
		FROM Student_History
		WHERE student_ID = ?
			AND Grade IN (
				'A', 'A-', 'B+', 'B', 'B-',
				'C+', 'C', 'C-', 'D+', 'D'
			)",
		[$studentID]
	);
	$passedCourseIDSet = array_column($passedCourseIDs, 'course_ID');

	// Student holds
	$hasHold = (bool) one(
		$pdo,
		"SELECT 1 FROM Student_Hold WHERE student_ID = ? LIMIT 1",
		[$studentID]
	);

	$eligible = [];

	foreach ($sections as $section) {
		$courseID = $section['course_ID'];

		// Already registered for this course this semester
		if (in_array($courseID, $registeredCourseIDs, true)) {
			continue;
		}

		// Already passed this course
		if (in_array($courseID, $passedCourseIDSet, true)) {
			continue;
		}

		// Holds block registration
		if ($hasHold) {
			continue;
		}

		// Course level matches student level
		if (
			($studentType === 'Undergraduate' && $section['course_Type'] === 'Graduate') ||
			($studentType === 'Graduate' && $section['course_Type'] === 'Undergraduate')
		) {
			continue;
		}

		// Prerequisites satisfied
		$prerequisites = all_rows(
			$pdo,
			"SELECT prerequisite_course_ID, min_Grade_Req
			FROM Course_Prerequisite
			WHERE course_ID = ?",
			[$courseID]
		);

		$prereqsMet = true;

		foreach ($prerequisites as $prereq) {
			$best = $bestGrades[$prereq['prerequisite_course_ID']] ?? null;

			if (
				!$best ||
				!grade_meets($best['grade'], (string) $prereq['min_Grade_Req'])
			) {
				$prereqsMet = false;
				break;
			}
		}

		if (!$prereqsMet) {
			continue;
		}

		// Credit limit
		if ($currentCredits + (int) $section['course_Credits'] > $creditLimit) {
			continue;
		}

		// Time conflict with existing registration
		$conflict = one(
			$pdo,
			"SELECT 1
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
					JOIN Period my_time ON my_time.period_ID = my_period.period_ID
					JOIN Time_Slot_Period new_period ON new_period.time_Slot_ID = ?
					JOIN Period new_time ON new_time.period_ID = new_period.period_ID
					WHERE my_period.time_Slot_ID = Course_Section.time_Slot_ID
						AND my_time.start_Time < new_time.end_Time
						AND new_time.start_Time < my_time.end_Time
				)
			LIMIT 1",
			[
				$section['time_Slot_ID'],
				$studentID,
				$semesterID,
				$section['time_Slot_ID']
			]
		);

		if ($conflict) {
			continue;
		}

		$eligible[] = $section;
	}

	return $eligible;
}

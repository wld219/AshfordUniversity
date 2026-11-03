<?php

declare(strict_types=1);

// Rosters action: save a grade and/or today's attendance for one student.
// Uses $pdo, $facultyID, $crn from the entry point.
//
// Rules enforced server-side:
//   - the section must belong to the signed-in faculty member,
//   - grades must be valid letter grades,
//   - grades require an open submission window or an approved exception,
//   - attendance may only be recorded while the section is meeting,
//   - every save is written to the audit log.

$studentID = (int) ($_POST['student_id'] ?? 0);
$grade = strtoupper(trim((string) ($_POST['grade'] ?? '')));
$attendance = $_POST['attendance'] ?? 'Present';
$today = date('Y-m-d');

// Make sure the course belongs to this faculty member
$courseSection = one(
	$pdo,
	"SELECT course_ID, semester_ID
		FROM Course_Section
		WHERE CRN = ?
		AND faculty_ID = ?",
		[$crn, $facultyID]
	);

if ($courseSection) {

	$errors = [];
	$savedSomething = false;


	// Attendance must be one of the allowed values
	if (!in_array($attendance, ['Present', 'Absent'], true)) {
		$attendance = 'Present';
	}


	// Grades: validate the value, then enforce the submission deadline
	// (or an approved grade exception).
	if ($grade !== '') {

		if (!valid_grade($grade)) {

			$errors[] = 'Grades must be a letter grade such as A, B+, or C-.';

		} else {

			[$gradeOpen, $gradeMessage] = grade_submission_status(
				$pdo,
				$studentID,
				$crn
			);

			if (!$gradeOpen) {

				$errors[] = $gradeMessage;

			} else {

				$pdo->prepare(
					"UPDATE Enrollment
					SET Grade = ?
					WHERE student_ID = ?
					AND CRN = ?"
				)->execute([
					$grade,
					$studentID,
					$crn
				]);

				audit_log(
					$pdo,
					'Update',
					'Enrollment',
					(string) $studentID,
					'Grade submitted for CRN ' . $crn
				);

				// Keep the student's course history in sync so the posted
				// grade immediately appears on the transcript and GPA.
				$pdo->prepare(
					"INSERT INTO Student_History
						(student_ID, CRN, course_ID, semester_ID, Grade)
					VALUES (?, ?, ?, ?, ?)
					ON DUPLICATE KEY UPDATE Grade = VALUES(Grade)"
				)->execute([
					$studentID,
					$crn,
					$courseSection['course_ID'],
					$courseSection['semester_ID'],
					$grade
				]);

				$savedSomething = true;
			}
		}
	}


	// Attendance: only while the section is actually meeting
	[$attendanceOpen, $attendanceMessage] = attendance_window_status(
		$pdo,
		$crn
	);

	if (!$attendanceOpen) {

		$errors[] = $attendanceMessage;

	} else {

		// Save today's attendance (VALUES() form keeps MySQL and MariaDB parity)
		$pdo->prepare(
			"INSERT INTO Attendance
				(
					student_ID,
					CRN,
					course_ID,
					attendance_Date,
					Present_Absent
				)
			VALUES (?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
				Present_Absent = VALUES(Present_Absent)"
		)->execute([
			$studentID,
			$crn,
			$courseSection['course_ID'],
			$today,
			$attendance
		]);

		audit_log(
			$pdo,
			'Update',
			'Attendance',
			(string) $studentID,
			'Attendance recorded for CRN ' . $crn
		);

		$savedSomething = true;
	}


	if (count($errors) > 0) {

		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => implode(' ', $errors)
		];

	} elseif ($savedSomething) {

		$_SESSION['flash'] = [
			'type' => 'success',
			'message' => 'Student record updated.'
		];
	}
}

header("Location: rosters.php?crn=$crn");
exit;

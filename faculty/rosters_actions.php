<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_once __DIR__ . '/attendance-window.php';

require_role(['Faculty']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit('Method not allowed.');
}

csrf_verify();

// Legacy roster save route: require its own authenticated faculty context.
$facultyID = (int) $_SESSION['user_id'];
$crn = filter_var($_POST['crn'] ?? null, FILTER_VALIDATE_INT);
$crn = $crn === false ? 0 : (int) $crn;
$studentID = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT);
$studentID = $studentID === false ? 0 : (int) $studentID;
$semesterID = filter_var($_POST['semester_ID'] ?? null, FILTER_VALIDATE_INT);
$semesterID = $semesterID === false ? 0 : (int) $semesterID;
$redirect = url('faculty/rosters.php?term=' . $semesterID . '&crn=' . $crn);

$section = one(
	$pdo,
	"SELECT cs.course_ID, cs.semester_ID
	 FROM Course_Section cs
	 JOIN Semester s ON s.semester_ID = cs.semester_ID
	 JOIN Enrollment e
	   ON e.CRN = cs.CRN AND e.semester_ID = cs.semester_ID
	 WHERE cs.CRN = ? AND cs.faculty_ID = ? AND cs.semester_ID = ?
	   AND cs.is_Cancelled = 0
	   AND s.semester_Name IN ('Fall 2026', 'Spring 2027')
	   AND e.student_ID = ?",
	[$crn, $facultyID, $semesterID, $studentID]
);
if (!$section) {
	http_response_code(403);
	exit('Section or student enrollment is not available to this Faculty account.');
}

// Rosters action: save a grade and/or today's attendance for one student.
//
// Rules enforced server-side:
//   - the section must belong to the signed-in faculty member,
//   - grades must be valid letter grades,
//   - grades require an open submission window or an approved exception,
//   - attendance may only be recorded while the section is meeting,
//   - every save is written to the audit log.

$grade = strtoupper(trim((string) ($_POST['grade'] ?? '')));
$attendance = $_POST['attendance'] ?? 'Present';
$today = (new DateTimeImmutable('today'))->format('Y-m-d');
$courseSection = $section;

if ($courseSection) {

	$errors = [];
	$savedSomething = false;


	// Attendance must be one of the allowed values
	if (!in_array($attendance, ['Present', 'Absent'], true)) {
		http_response_code(422);
		exit('Invalid attendance status. This database supports Present and Absent.');
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
					AND CRN = ?
					AND semester_ID = ?"
				)->execute([
					$grade,
					$studentID,
					$crn,
					$semesterID
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
	[$dateAllowed, $dateMessage] = attendance_date_allowed(
		$pdo,
		$crn,
		$today
	);
	if (!$dateAllowed) {
		$errors[] = $dateMessage;
	}
	[$attendanceOpen, $attendanceMessage] = attendance_window_status(
		$pdo,
		$crn
	);

	if (!$dateAllowed) {
		// The date/semester check above already explains why no attendance is saved.
	} elseif (!$attendanceOpen) {

		$errors[] = $attendanceMessage;

	} else {

		try {
			$pdo->beginTransaction();
			// The existing Attendance primary key safely updates a same-day mark.
			$pdo->prepare(
				"INSERT INTO Attendance
					(student_ID, CRN, course_ID, attendance_Date, Present_Absent)
				 VALUES (?, ?, ?, ?, ?)
				 ON DUPLICATE KEY UPDATE Present_Absent = VALUES(Present_Absent)"
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
			$pdo->commit();
			$savedSomething = true;
		} catch (Throwable $exception) {
			if ($pdo->inTransaction()) {
				$pdo->rollBack();
			}
			error_log('Roster attendance save failed: ' . $exception->getMessage());
			$errors[] = 'Attendance could not be saved.';
		}
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

header('Location: ' . $redirect);
exit;

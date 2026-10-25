<?php

declare(strict_types=1);

// Attendance calendar save action.
// Uses $pdo, $facultyID from the entry point.
//
// Saves Present/Absent/Not Recorded for all students on a specific date.
// Each student's attendance is upserted individually; unchanged values
// result in a no-op update.

$crn = (int) ($_POST['crn'] ?? 0);
$attendanceDate = post_date('attendance_date') ?? date('Y-m-d');

// Verify the section belongs to this faculty member
$section = one(
	$pdo,
	"SELECT course_ID, semester_ID
		FROM Course_Section
		WHERE CRN = ?
		AND faculty_ID = ?
		AND is_Cancelled = 0",
	[$crn, $facultyID]
);

if (!$section) {
	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => 'Section not found or not assigned to you.'
	];
	header('Location: ' . url('faculty/attendance-calendar.php'));
	exit;
}

// Verify the date is allowed
[$allowed, $message] = attendance_date_allowed($pdo, $crn, $attendanceDate);

if (!$allowed) {
	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => $message
	];
	header('Location: ' . url('faculty/attendance-calendar.php?crn=' . $crn));
	exit;
}

$attendanceData = $_POST['attendance'] ?? [];

if (!is_array($attendanceData)) {
	$attendanceData = [];
}

$savedCount = 0;
$errors = [];

try {

	$pdo->beginTransaction();

	foreach ($attendanceData as $studentIDRaw => $value) {
		$studentID = (int) $studentIDRaw;

		// Verify the student is enrolled in this section
		$enrolled = one(
			$pdo,
			'SELECT 1 FROM Enrollment WHERE student_ID = ? AND CRN = ?',
			[$studentID, $crn]
		);

		if (!$enrolled) {
			continue;
		}

		// Validate the value
		$presentAbsent = null;
		if ($value === 'Present') {
			$presentAbsent = 'Present';
		} elseif ($value === 'Absent') {
			$presentAbsent = 'Absent';
		}
		// Empty string or any other value = delete (not recorded)

		if ($presentAbsent !== null) {

			// Upsert the attendance record
			$pdo->prepare(
				"INSERT INTO Attendance
					(student_ID, CRN, course_ID, attendance_Date, Present_Absent)
				VALUES (?, ?, ?, ?, ?)
				ON DUPLICATE KEY UPDATE Present_Absent = VALUES(Present_Absent)"
			)->execute([
				$studentID,
				$crn,
				$section['course_ID'],
				$attendanceDate,
				$presentAbsent
			]);

			audit_log(
				$pdo,
				'Update',
				'Attendance',
				(string) $studentID,
				'Attendance recorded for CRN ' . $crn . ' on ' . $attendanceDate
			);

			$savedCount++;

		} else {

			// Value is empty: delete any existing record (not recorded)
			$pdo->prepare(
				'DELETE FROM Attendance
				WHERE student_ID = ? AND CRN = ? AND attendance_Date = ?'
			)->execute([
				$studentID,
				$crn,
				$attendanceDate
			]);

			audit_log(
				$pdo,
				'Delete',
				'Attendance',
				(string) $studentID,
				'Attendance removed for CRN ' . $crn . ' on ' . $attendanceDate
			);

			$savedCount++;
		}
	}

	$pdo->commit();

	$_SESSION['flash'] = [
		'type' => 'success',
		'message' => 'Attendance saved for ' . $savedCount . ' student(s) on ' . e($attendanceDate) . '.'
	];

} catch (Throwable $e) {

	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}

	error_log('Attendance calendar save failed: ' . $e->getMessage());

	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => 'Could not save attendance.'
	];
}

header('Location: ' . url('faculty/attendance-calendar.php?crn=' . $crn . '&date=' . $attendanceDate));
exit;

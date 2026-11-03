<?php

declare(strict_types=1);

// Weekly attendance save action.
// Uses $pdo, $facultyID from the entry point.
//
// Receives attendance[student_ID][date] = Present|Absent|'' and saves
// each cell individually in a transaction.

$crn = (int) ($_POST['crn'] ?? 0);
$weekStart = post_date('week_start') ?? date('Y-m-d');

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
	header('Location: ' . url('faculty/attendance-weekly.php'));
	exit;
}

// Compute all 7 dates of the week
$weekDates = [];
$dateObj = new DateTimeImmutable($weekStart);
$dateObj = $dateObj->modify('Monday this week');
for ($i = 0; $i < 7; $i++) {
	$weekDates[] = $dateObj->format('Y-m-d');
	$dateObj = $dateObj->modify('+1 day');
}

// Verify all dates are allowed
foreach ($weekDates as $d) {
	[$allowed, $message] = attendance_date_allowed($pdo, $crn, $d);
	if (!$allowed) {
		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => $message . ' (' . e($d) . ')'
		];
		header('Location: ' . url('faculty/attendance-weekly.php?crn=' . $crn . '&week=' . $weekStart));
		exit;
	}
}

$attendanceData = $_POST['attendance'] ?? [];

if (!is_array($attendanceData)) {
	$attendanceData = [];
}

$savedCount = 0;

try {

	$pdo->beginTransaction();

	foreach ($attendanceData as $studentIDRaw => $dateValues) {
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

		if (!is_array($dateValues)) {
			continue;
		}

		foreach ($dateValues as $attendanceDate => $value) {

			// Only process dates within this week
			if (!in_array($attendanceDate, $weekDates, true)) {
				continue;
			}

			// Validate the value
			$presentAbsent = null;
			if ($value === 'Present') {
				$presentAbsent = 'Present';
			} elseif ($value === 'Absent') {
				$presentAbsent = 'Absent';
			}

			if ($presentAbsent !== null) {

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
					'Weekly attendance: ' . $presentAbsent . ' for CRN ' . $crn . ' on ' . $attendanceDate
				);

				$savedCount++;

			} else {

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
					'Weekly attendance removed for CRN ' . $crn . ' on ' . $attendanceDate
				);

				$savedCount++;
			}
		}
	}

	$pdo->commit();

	$_SESSION['flash'] = [
		'type' => 'success',
		'message' => 'Weekly attendance saved for ' . $savedCount . ' cell(s).'
	];

} catch (Throwable $e) {

	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}

	error_log('Weekly attendance save failed: ' . $e->getMessage());

	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => 'Could not save weekly attendance.'
	];
}

header('Location: ' . url('faculty/attendance-weekly.php?crn=' . $crn . '&week=' . $weekStart));
exit;

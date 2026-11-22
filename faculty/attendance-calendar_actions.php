<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_once __DIR__ . '/attendance-window.php';
require_once __DIR__ . '/attendance-support.php';

require_role(['Faculty']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit('Method not allowed.');
}

csrf_verify();

$facultyId = (int) $_SESSION['user_id'];
$semesterOptions = faculty_attendance_semesters($pdo);
$semesterId = faculty_attendance_selected_semester(
	$semesterOptions,
	$_POST['semester_ID'] ?? null
);
$crn = filter_var($_POST['crn'] ?? null, FILTER_VALIDATE_INT);
$crn = $crn === false ? 0 : (int) $crn;
$attendanceDate = trim((string) ($_POST['attendance_date'] ?? ''));
$redirect = url('faculty/attendance-calendar.php?semester_ID=' . $semesterId . '&crn=' . $crn);

$fail = static function (string $message) use ($redirect): void {
	$_SESSION['flash'] = ['type' => 'error', 'message' => $message];
	header('Location: ' . $redirect);
	exit;
};

$section = faculty_attendance_section($pdo, $facultyId, $semesterId, $crn);
if (!$section) {
	$fail('This active course section is not assigned to you for the selected semester.');
}

if (!faculty_attendance_date_is_valid($attendanceDate) || !faculty_attendance_date_is_today($attendanceDate)) {
	$fail('Attendance can only be completed for today.');
}

[$dateAllowed, $dateMessage] = attendance_date_allowed($pdo, $crn, $attendanceDate);
if (!$dateAllowed) {
	$fail($dateMessage);
}
[$attendanceOpen, $windowMessage] = attendance_window_status($pdo, $crn);
if (!$attendanceOpen) {
	$fail($windowMessage);
}

$attendanceData = $_POST['attendance'] ?? [];
if (!is_array($attendanceData)) {
	$fail('Invalid attendance submission.');
}

$rosterRows = all_rows(
	$pdo,
	"SELECT e.student_ID
	 FROM Enrollment e
	 JOIN Course_Section cs
	   ON cs.CRN = e.CRN AND cs.semester_ID = e.semester_ID
	 WHERE e.CRN = ? AND e.semester_ID = ?
	   AND cs.faculty_ID = ? AND cs.is_Cancelled = 0",
	[$crn, $semesterId, $facultyId]
);
$roster = [];
foreach ($rosterRows as $row) {
	$roster[(int) $row['student_ID']] = true;
}

$updates = [];
foreach ($attendanceData as $studentIdRaw => $status) {
	if (!ctype_digit((string) $studentIdRaw)) {
		$fail('Invalid student ID.');
	}
	$studentId = (int) $studentIdRaw;
	if (!isset($roster[$studentId])) {
		$fail('A submitted student is not enrolled in this section for the selected semester.');
	}
	if ($status === '') {
		continue; // Leave any existing value unchanged; never delete attendance here.
	}
	if (!in_array($status, ['Present', 'Absent'], true)) {
		$fail('Invalid attendance status. This database supports Present and Absent.');
	}
	$updates[] = [$studentId, $status];
}

try {
	$pdo->beginTransaction();
	$save = $pdo->prepare(
		"INSERT INTO Attendance
		 (student_ID, CRN, course_ID, attendance_Date, Present_Absent)
		 VALUES (?, ?, ?, ?, ?)
		 ON DUPLICATE KEY UPDATE Present_Absent = VALUES(Present_Absent)"
	);
	foreach ($updates as [$studentId, $status]) {
		$save->execute([$studentId, $crn, $section['course_ID'], $attendanceDate, $status]);
		audit_log(
			$pdo,
			'Update',
			'Attendance',
			(string) $studentId,
			'Attendance recorded for CRN ' . $crn . ' on ' . $attendanceDate . ': ' . $status
		);
	}
	$pdo->commit();
	$_SESSION['flash'] = [
		'type' => 'success',
		'message' => 'Attendance saved for ' . count($updates) . ' student(s). Unmarked entries were left unchanged.'
	];
} catch (Throwable $exception) {
	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}
	error_log('Attendance calendar save failed: ' . $exception->getMessage());
	$_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not save attendance.'];
}

header('Location: ' . $redirect . '&date=' . rawurlencode($attendanceDate));
exit;

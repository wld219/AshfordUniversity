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
$redirect = url('faculty/attendance-weekly.php?semester_ID=' . $semesterId . '&crn=' . $crn);

$finish = static function (string $type, string $message) use ($redirect): void {
	$_SESSION['flash'] = ['type' => $type, 'message' => $message];
	header('Location: ' . $redirect);
	exit;
};

$section = faculty_attendance_section($pdo, $facultyId, $semesterId, $crn);
if (!$section) {
	$finish('error', 'This active course section is not assigned to you for the selected semester.');
}

$today = (new DateTimeImmutable('today'))->format('Y-m-d');
[$dateAllowed, $dateMessage] = attendance_date_allowed($pdo, $crn, $today);
if (!$dateAllowed) {
	$finish('error', $dateMessage);
}
[$attendanceOpen, $windowMessage] = attendance_window_status($pdo, $crn);
if (!$attendanceOpen) {
	$finish('error', $windowMessage);
}

$attendanceData = $_POST['attendance'] ?? [];
if (!is_array($attendanceData)) {
	$finish('error', 'Invalid attendance submission.');
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
foreach ($attendanceData as $studentIdRaw => $dates) {
	if (!ctype_digit((string) $studentIdRaw)) {
		$finish('error', 'Invalid student ID.');
	}
	$studentId = (int) $studentIdRaw;
	if (!isset($roster[$studentId]) || !is_array($dates)) {
		$finish('error', 'A submitted student is not enrolled in this section for the selected semester.');
	}
	foreach ($dates as $date => $status) {
		if (!is_string($date) || !faculty_attendance_date_is_valid($date) || $date !== $today) {
			$finish('error', 'Attendance can only be completed for today.');
		}
		if ($status === '') {
			continue; // Unmarked cells leave existing attendance unchanged.
		}
		if (!in_array($status, ['Present', 'Absent'], true)) {
			$finish('error', 'Invalid attendance status. This database supports Present and Absent.');
		}
		$updates[] = [$studentId, $date, $status];
	}
}

try {
	$pdo->beginTransaction();
	$save = $pdo->prepare(
		"INSERT INTO Attendance
		 (student_ID, CRN, course_ID, attendance_Date, Present_Absent)
		 VALUES (?, ?, ?, ?, ?)
		 ON DUPLICATE KEY UPDATE Present_Absent = VALUES(Present_Absent)"
	);
	foreach ($updates as [$studentId, $date, $status]) {
		$save->execute([$studentId, $crn, $section['course_ID'], $date, $status]);
		audit_log(
			$pdo,
			'Update',
			'Attendance',
			(string) $studentId,
			'Weekly attendance: ' . $status . ' for CRN ' . $crn . ' on ' . $date
		);
	}
	$pdo->commit();
	$finish('success', 'Attendance saved for ' . count($updates) . ' student/date entries.');
} catch (Throwable $exception) {
	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}
	error_log('Weekly attendance error: ' . $exception->getMessage());
	$finish('error', 'Unable to save attendance. Please try again.');
}

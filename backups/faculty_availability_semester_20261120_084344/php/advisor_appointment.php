<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_once '../includes/advising_appointment_helpers.php';

require_role(['Student']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ' . url('student/advisor.php'));
	exit;
}

csrf_verify();
$studentID = (int) $_SESSION['user_id'];
$date = trim((string) ($_POST['appointment_date'] ?? ''));
$time = trim((string) ($_POST['appointment_time'] ?? ''));
$reason = trim((string) ($_POST['reason'] ?? ''));
$slot = advising_parse_slot($date, $time);

try {
	if (!$slot || $slot <= new DateTimeImmutable('now') || mb_strlen($reason) > 500) {
		throw new RuntimeException('Please choose a valid future appointment time and a reason under 500 characters.');
	}

	$ownTransaction = !$pdo->inTransaction();
	if ($ownTransaction) {
		$pdo->beginTransaction();
	}
	try {
		$advisor = one($pdo, 'SELECT faculty_ID FROM Advisor WHERE student_ID = ? LIMIT 1', [$studentID]);
		if (!$advisor) {
			throw new RuntimeException('No advisor is assigned to your account.');
		}
		$facultyID = (int) $advisor['faculty_ID'];
		$facultyLock = $pdo->prepare('SELECT faculty_ID FROM Faculty WHERE faculty_ID = ? FOR UPDATE');
		$facultyLock->execute([$facultyID]);
		if (!$facultyLock->fetchColumn()) {
			throw new RuntimeException('Your assigned advisor is unavailable.');
		}
		$studentLock = $pdo->prepare('SELECT student_ID FROM Student WHERE student_ID = ? FOR UPDATE');
		$studentLock->execute([$studentID]);
		if (!$studentLock->fetchColumn()) {
			throw new RuntimeException('Student account was not found.');
		}

		if (!advising_slot_is_in_active_window($pdo, $facultyID, $slot)) {
			throw new RuntimeException('The full 30-minute appointment must fit within an active advisor availability window.');
		}
		$activeRequest = one(
			$pdo,
			"SELECT appointment_ID FROM Advising_Appointment WHERE student_ID = ? AND status IN ('Pending','Scheduled') LIMIT 1 FOR UPDATE",
			[$studentID]
		);
		if ($activeRequest) {
			throw new RuntimeException('You already have a pending or scheduled advising appointment.');
		}
		if (advising_slot_has_conflict($pdo, $facultyID, $studentID, $slot)) {
			throw new RuntimeException('The requested time overlaps an existing appointment. Please choose another time.');
		}

		$insert = $pdo->prepare(
			"INSERT INTO Advising_Appointment (student_ID, faculty_ID, requested_DateTime, status, student_Reason)
			 VALUES (?, ?, ?, 'Pending', ?)"
		);
		$insert->execute([$studentID, $facultyID, $slot->format('Y-m-d H:i:s'), $reason !== '' ? $reason : null]);
		audit_log($pdo, 'Create', 'Advising_Appointment', (string) $pdo->lastInsertId(), 'Student requested a 30-minute advising appointment.');
		if ($ownTransaction) {
			$pdo->commit();
		}
		$_SESSION['flash'] = ['type' => 'success', 'message' => 'Appointment request submitted to your advisor.'];
	} catch (Throwable $exception) {
		if ($ownTransaction && $pdo->inTransaction()) {
			$pdo->rollBack();
		}
		throw $exception;
	}
} catch (Throwable $exception) {
	error_log('Student advising appointment request rejected: ' . $exception->getMessage());
	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to submit your appointment request.',
	];
}

header('Location: ' . url('student/advisor.php'));
exit;

<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);
require_once '../includes/advising_workflow.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('student/advisor.php'));
    exit;
}

csrf_verify();

$studentID = (int) $_SESSION['user_id'];
$date = trim((string) ($_POST['requested_datetime'] ?? ''));
$reason = trim((string) ($_POST['reason'] ?? ''));

$parsed = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $date);

if (
    !$parsed ||
    $parsed->format('Y-m-d\TH:i') !== $date ||
    $parsed <= new DateTimeImmutable() ||
    mb_strlen($reason) > 500
) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Enter a valid future appointment date and reason.'
    ];
    header('Location: ' . url('student/advisor.php'));
    exit;
}

$advisor = one(
    $pdo,
    'SELECT faculty_ID FROM Advisor WHERE student_ID = ? LIMIT 1',
    [$studentID]
);

if (!$advisor) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'No advisor is assigned to your account.'
    ];
    header('Location: ' . url('student/advisor.php'));
    exit;
}

$facultyID = (int) $advisor['faculty_ID'];

$existing = one(
    $pdo,
    "SELECT appointment_ID
     FROM Advising_Appointment
     WHERE student_ID = ?
       AND status IN ('Pending', 'Scheduled')
     LIMIT 1",
    [$studentID]
);

if ($existing) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'You already have an active appointment request.'
    ];
    header('Location: ' . url('student/advisor.php'));
    exit;
}

try {
    if (aw_conflict($pdo,$facultyID,$parsed->format('Y-m-d H:i:s'),0)) {
        throw new RuntimeException('The requested time conflicts with an existing appointment.');
    }
    $stmt = $pdo->prepare(
        "INSERT INTO Advising_Appointment
         (student_ID, faculty_ID, requested_DateTime, status, student_Reason)
         VALUES (?, ?, ?, 'Pending', ?)"
    );

    $stmt->execute([
        $studentID,
        $facultyID,
        $parsed->format('Y-m-d H:i:s'),
        $reason !== '' ? $reason : null
    ]);

    aw_log($pdo,(int)$pdo->lastInsertId(),$studentID,$facultyID,'A student requested an advising appointment for '.$parsed->format('Y-m-d H:i:s').'.');
    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'Appointment request submitted.'
    ];
} catch (Throwable $e) {
    error_log('Appointment request failed: ' . $e->getMessage());

    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Unable to submit the appointment request.'
    ];
}

header('Location: ' . url('student/advisor.php'));
exit;

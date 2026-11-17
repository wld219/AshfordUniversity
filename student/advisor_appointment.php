<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);
require_once '../includes/advising_workflow.php';
require_once '../includes/advising_workflow.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('student/advisor.php'));
    exit;
}

csrf_verify();

$studentID = (int) $_SESSION['user_id'];
$date = trim((string) ($_POST['appointment_date'] ?? ''));
$time = trim((string) ($_POST['appointment_time'] ?? ''));
$reason = trim((string) ($_POST['reason'] ?? ''));

if (!$date || !$time || mb_strlen($reason) > 500) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Please select a date, time, and reason for the appointment.'
    ];
    header('Location: ' . url('student/advisor.php'));
    exit;
}

$datetime = $date . ' ' . $time . ':00';
$parsed = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $datetime);

if (!$parsed || $parsed <= new DateTimeImmutable()) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Please select a valid future appointment date and time.'
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

// Validate the selected time is within advisor's availability
$dayName = date('l', strtotime($date));
$timeOnly = $time;

$availability = one(
    $pdo,
    "SELECT fa.start_Time, fa.end_Time
     FROM Faculty_Availability fa
     JOIN Day d ON d.day_ID = fa.day_ID
     WHERE fa.faculty_ID = ?
       AND d.day_Of_Week = ?
       AND fa.is_active = TRUE
     LIMIT 1",
    [$facultyID, $dayName]
);

if (!$availability) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'The selected time is outside your advisor\'s availability window.'
    ];
    header('Location: ' . url('student/advisor.php'));
    exit;
}

$selectedTime = new DateTimeImmutable('1970-01-01 ' . $time);
$startTime = new DateTimeImmutable('1970-01-01 ' . $availability['start_Time']);
$endTime = new DateTimeImmutable('1970-01-01 ' . $availability['end_Time']);

if ($selectedTime < $startTime || $selectedTime >= $endTime) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'The selected time is outside your advisor\'s availability window.'
    ];
    header('Location: ' . url('student/advisor.php'));
    exit;
}

// Check for existing active appointment
$existing = one(
    $pdo,
    "SELECT appointment_ID
     FROM Appointment
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

// Check for conflict with existing appointments
if (aw_conflict($pdo, $facultyID, $datetime, 0)) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'The requested time conflicts with an existing appointment.'
    ];
    header('Location: ' . url('student/advisor.php'));
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO Appointment
         (student_ID, faculty_ID, slot_start, slot_end, topic, status, reason)
         VALUES (?, ?, ?, ?, ?, 'Pending', ?)"
    );

    $slotEnd = (clone $parsed)->modify('+30 minutes');

    $stmt->execute([
        $studentID,
        $facultyID,
        $parsed->format('Y-m-d H:i:s'),
        $slotEnd->format('Y-m-d H:i:s'),
        $reason !== '' ? $reason : null,
        $reason !== '' ? $reason : null
    ]);

    aw_log($pdo, (int) $pdo->lastInsertId(), $studentID, $facultyID,
        'A student requested an advising appointment for ' . $parsed->format('Y-m-d H:i:s') . '.');

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

<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);
require_once __DIR__ . '/attendance-window.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

csrf_verify();

$facultyID = (int) $_SESSION['user_id'];
$crn = filter_var($_POST['crn'] ?? null, FILTER_VALIDATE_INT);
$crn = $crn ?: 0;

$redirect = url('faculty/attendance-weekly.php?crn=' . $crn);

function attendance_finish(string $redirect, string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
    header('Location: ' . $redirect);
    exit;
}

$section = one(
    $pdo,
    "SELECT cs.course_ID
     FROM Course_Section cs
     JOIN Semester s ON s.semester_ID = cs.semester_ID
     WHERE cs.CRN = ?
       AND cs.faculty_ID = ?
       AND cs.is_Cancelled = 0
       AND s.semester_Name = 'Fall 2026'",
    [$crn, $facultyID]
);

if (!$section) {
    attendance_finish(
        $redirect, 'error',
        'This course section is not assigned to you.'
    );
}


[$attendanceOpen, $attendanceMessage] =
    attendance_date_allowed(
        $pdo, $crn,
        (new DateTimeImmutable(
            'now',
            new DateTimeZone('America/New_York')
        ))->format('Y-m-d')
    );

if (!$attendanceOpen) {
    attendance_finish($redirect, 'error', $attendanceMessage);
}

$data = $_POST['attendance'] ?? [];

if (!is_array($data)) {
    attendance_finish($redirect, 'error', 'Invalid attendance submission.');
}

$today = new DateTimeImmutable('today', new DateTimeZone('America/New_York'));
$monday = $today->modify('Monday this week');
$sunday = $monday->modify('+6 days');

$roster = all_rows(
    $pdo,
    "SELECT student_ID FROM Enrollment WHERE CRN = ?",
    [$crn]
);

$validStudents = [];
foreach ($roster as $row) {
    $validStudents[(int)$row['student_ID']] = true;
}

$updates = [];

foreach ($data as $studentIDRaw => $dates) {
    if (!ctype_digit((string)$studentIDRaw)) {
        attendance_finish($redirect, 'error', 'Invalid student ID.');
    }

    $studentID = (int)$studentIDRaw;

    if (!isset($validStudents[$studentID]) || !is_array($dates)) {
        attendance_finish($redirect, 'error', 'Invalid student enrollment.');
    }

    foreach ($dates as $dateString => $status) {
        if (!is_string($dateString) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            attendance_finish($redirect, 'error', 'Invalid attendance date.');
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dateString);

        if (!$date || $date->format('Y-m-d') !== $dateString ||
            $dateString !== $today->format('Y-m-d')) {
            attendance_finish(
                $redirect, 'error',
                'Attendance is permitted only for today.'
            );
        }

        // An unmarked cell does not change an existing record.
        if ($status === '') {
            continue;
        }

        if ($status !== 'Present' && $status !== 'Absent') {
            attendance_finish($redirect, 'error', 'Invalid attendance status.');
        }

        [$allowed, $message] = attendance_date_allowed(
            $pdo, $crn, $dateString
        );

        if (!$allowed) {
            attendance_finish($redirect, 'error', $message);
        }

        $updates[] = [$studentID, $dateString, $status];
    }
}

try {
    $pdo->beginTransaction();

    $save = $pdo->prepare(
        "INSERT INTO Attendance
         (student_ID, CRN, course_ID, attendance_Date, Present_Absent)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
         Present_Absent = VALUES(Present_Absent)"
    );

    foreach ($updates as [$studentID, $dateString, $status]) {
        $save->execute([
            $studentID,
            $crn,
            $section['course_ID'],
            $dateString,
            $status
        ]);

        audit_log(
            $pdo,
            'Update',
            'Attendance',
            (string)$studentID,
            'Weekly attendance: ' . $status .
            ' for CRN ' . $crn . ' on ' . $dateString
        );
    }

    $pdo->commit();

    attendance_finish(
        $redirect,
        'success',
        'Attendance saved successfully for ' .
        count($updates) . ' student/date entries.'
    );

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Weekly attendance error: ' . $e->getMessage());

    attendance_finish(
        $redirect,
        'error',
        'Unable to save attendance. Please try again.'
    );
}

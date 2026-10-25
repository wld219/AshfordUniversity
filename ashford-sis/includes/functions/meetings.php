<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/functions/windows.php';

require_role(['Faculty']);

$pdo = get_pdo();
$facultyID = (int) $_SESSION['user_id'];

// Grade submission window status
function grade_submission_status(PDO $pdo, int $studentID, int $crn): array {
    $section = one($pdo,
        'SELECT cs.semester_ID, s.grade_Deadline
           FROM Course_Section cs
           JOIN Semester s ON s.semester_ID = cs.semester_ID
          WHERE cs.CRN = ?', [$crn]);
    if (!$section) return [false, 'Section not found.'];
    if ($section['grade_Deadline'] !== null) {
        $deadline = new DateTimeImmutable((string) $section['grade_Deadline']);
        if (new DateTimeImmutable('now') > $deadline) {
            $exception = one($pdo,
                'SELECT 1 FROM Grade_Exception WHERE student_ID = ? AND CRN = ? AND approved_By_User_ID IS NOT NULL LIMIT 1',
                [$studentID, $crn]);
            if (!$exception) {
                return [false, 'The grade submission deadline for this semester has passed.'];
            }
        }
    }
    return [true, 'Open'];
}

// Attendance window status
function attendance_window_status(PDO $pdo, int $crn): array {
    $cancelled = (int) (one($pdo, 'SELECT is_Cancelled FROM Course_Section WHERE CRN = ?', [$crn])['is_Cancelled'] ?? 0);
    if ($cancelled) return [false, 'This section has been cancelled.'];
    $timeSlotID = (int) (one($pdo, 'SELECT time_Slot_ID FROM Course_Section WHERE CRN = ?', [$crn])['time_Slot_ID'] ?? 0);
    $todayDay = one($pdo,
        'SELECT d.day_ID FROM Time_Slot_Day tsd JOIN Day d ON d.day_ID = tsd.day_ID
         WHERE tsd.time_Slot_ID = ? AND d.day_Of_Week = DAYNAME(CURDATE()) LIMIT 1', [$timeSlotID]);
    if (!$todayDay) return [false, 'Attendance can only be recorded while the section is meeting.'];
    $inPeriod = one($pdo,
        'SELECT 1 FROM Time_Slot_Period tsp JOIN Period p ON p.period_ID = tsp.period_ID
         WHERE tsp.time_Slot_ID = ? AND CURTIME() >= p.start_Time AND CURTIME() <= p.end_Time LIMIT 1', [$timeSlotID]);
    return $inPeriod !== null ? [true, 'Open'] : [false, 'Attendance can only be recorded while the section is meeting.'];
}

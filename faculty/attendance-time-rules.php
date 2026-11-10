<?php
declare(strict_types=1);

/**
 * Returns whether attendance is currently open.
 * Uses the section's configured meeting days and time periods.
 * Fail closed if schedule information is unavailable.
 */
function ashford_attendance_window(PDO $pdo, int $crn): array
{
    $now = new DateTimeImmutable('now', new DateTimeZone('America/New_York'));
    $today = $now->format('Y-m-d');

    $section = one(
        $pdo,
        "SELECT cs.time_Slot_ID
         FROM Course_Section cs
         WHERE cs.CRN = ? AND cs.is_Cancelled = 0",
        [$crn]
    );

    if (!$section) {
        return [false, 'Section unavailable.', null];
    }

    $slot = (int)$section['time_Slot_ID'];

    $meeting = one(
        $pdo,
        "SELECT 1
         FROM Time_Slot_Day tsd
         JOIN Day d ON d.day_ID = tsd.day_ID
         WHERE tsd.time_Slot_ID = ?
           AND d.day_Of_Week = ?
         LIMIT 1",
        [$slot, $now->format('l')]
    );

    if (!$meeting) {
        return [false, 'This course does not meet today.', null];
    }

    // Detect the actual Period time columns from the database.
    $columns = $pdo->query("SHOW COLUMNS FROM Period")
                   ->fetchAll(PDO::FETCH_COLUMN);

    $startCandidates = ['start_Time', 'period_Start', 'start_time', 'startTime'];
    $endCandidates = ['end_Time', 'period_End', 'end_time', 'endTime'];

    $startColumn = null;
    $endColumn = null;

    foreach ($startCandidates as $candidate) {
        if (in_array($candidate, $columns, true)) {
            $startColumn = $candidate;
            break;
        }
    }

    foreach ($endCandidates as $candidate) {
        if (in_array($candidate, $columns, true)) {
            $endColumn = $candidate;
            break;
        }
    }

    if (!$startColumn || !$endColumn) {
        return [false, 'Class time configuration unavailable.', null];
    }

    $query = "SELECT MIN(p.`$startColumn`) AS begins,
                     MAX(p.`$endColumn`) AS finishes
              FROM Time_Slot_Period tsp
              JOIN Period p ON p.period_ID = tsp.period_ID
              WHERE tsp.time_Slot_ID = ?";

    $times = one($pdo, $query, [$slot]);

    if (!$times || !$times['begins'] || !$times['finishes']) {
        return [false, 'Class schedule time unavailable.', null];
    }

    $timezone = new DateTimeZone('America/New_York');

    $start = new DateTimeImmutable(
        $today . ' ' . $times['begins'], $timezone
    );
    $end = new DateTimeImmutable(
        $today . ' ' . $times['finishes'], $timezone
    );

    if ($end <= $start) {
        return [false, 'Invalid class time configuration.', null];
    }

    $deadline = $end->modify('+15 minutes');

    $label = $start->format('g:i A') . ' – ' .
             $end->format('g:i A') .
             ' (attendance closes ' . $deadline->format('g:i A') . ')';

    if ($now < $start) {
        return [false, 'Attendance opens when class begins.', $label];
    }

    if ($now > $deadline) {
        return [false, 'Attendance is closed for today.', $label];
    }

    [$allowed, $message] = attendance_date_allowed($pdo, $crn, $today);

    return [$allowed, $allowed ? 'Attendance is open.' : $message, $label];
}

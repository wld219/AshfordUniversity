<?php
declare(strict_types=1);

function ashford_attendance_window(PDO $pdo, int $crn): array
{
    $zone = new DateTimeZone('America/New_York');
    $now = new DateTimeImmutable('now', $zone);
    $today = $now->format('Y-m-d');

    $check = attendance_date_allowed($pdo, $crn, $today);
    if (!$check[0]) {
        return [false, $check[1]];
    }

    $stmt = $pdo->prepare(
        "SELECT MIN(p.start_Time) AS starts,
                MAX(p.end_Time) AS ends
         FROM Course_Section cs
         JOIN Time_Slot_Period tsp
           ON tsp.time_Slot_ID = cs.time_Slot_ID
         JOIN Period p ON p.period_ID = tsp.period_ID
         WHERE cs.CRN = ?"
    );
    $stmt->execute([$crn]);
    $times = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$times || !$times['starts'] || !$times['ends']) {
        return [false, 'Class times are not configured.'];
    }

    $start = new DateTimeImmutable(
        $today . ' ' . $times['starts'], $zone
    );
    $end = new DateTimeImmutable(
        $today . ' ' . $times['ends'], $zone
    );

    if ($end <= $start) {
        return [false, 'Invalid class schedule.'];
    }

    $close = $end->modify('+15 minutes');

    if ($now < $start) {
        return [false, 'Attendance opens at ' . $start->format('g:i A')];
    }
    if ($now > $close) {
        return [false, 'Attendance closed at ' . $close->format('g:i A')];
    }

    return [true, 'Attendance open until ' . $close->format('g:i A')];
}

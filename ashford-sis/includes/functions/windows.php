<?php

declare(strict_types=1);

// Registration and add/drop validation logic.
// All functions return [bool $ok, string|null $message].

require_once __DIR__ . '/../helpers/validation.php';

// Is the registration window open for the given semester?
function registration_window_status(PDO $pdo, int $semesterID): array
{
    $sem = one($pdo,
        'SELECT registration_Start, registration_End FROM Semester WHERE semester_ID = ?',
        [$semesterID]);

    if (!$sem) {
        return [false, 'Semester not found.'];
    }

    $now = new DateTimeImmutable('now');
    $start = new DateTimeImmutable((string) $sem['registration_Start']);
    $end = new DateTimeImmutable((string) $sem['registration_End']);

    if ($now < $start) {
        return [false, 'Registration has not yet opened.'];
    }

    if ($now > $end) {
        return [false, 'Registration window is closed.'];
    }

    return [true, null];
}

// Get the semester currently open for registration (or the nearest future one).
function current_registration_semester(PDO $pdo): ?int
{
    $now = new DateTimeImmutable('now');

    // Prefer a currently open window
    $open = one($pdo,
        'SELECT semester_ID FROM Semester
         WHERE registration_Start <= ? AND registration_End >= ?
         ORDER BY registration_Start ASC LIMIT 1',
        [$now->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s')]);

    if ($open) {
        return (int) $open['semester_ID'];
    }

    // Fall back to the next upcoming semester
    $upcoming = one($pdo,
        'SELECT semester_ID FROM Semester
         WHERE registration_Start > ?
         ORDER BY registration_Start ASC LIMIT 1',
        [$now->format('Y-m-d H:i:s')]);

    return $upcoming ? (int) $upcoming['semester_ID'] : null;
}

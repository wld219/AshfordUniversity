<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$allowedSemesters = [
    1 => 'Fall 2026',
    2 => 'Spring 2027',
];
$semesterArg = null;
$apply = false;
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--semester=')) {
        $semesterArg = substr($arg, strlen('--semester='));
    } elseif ($arg === '--apply') {
        $apply = true;
    } elseif ($arg === '--help') {
        echo "Usage: php scripts/reconcile_section_seats.php [--semester=1|2] [--apply]\n";
        echo "Dry-run is the default. --apply requires one verified target semester.\n";
        exit(0);
    } else {
        fwrite(STDERR, "Unknown argument: {$arg}\n");
        exit(2);
    }
}

if ($apply && $semesterArg === null) {
    fwrite(STDERR, "--apply requires --semester=1 or --semester=2.\n");
    exit(2);
}
if ($semesterArg !== null && (!ctype_digit($semesterArg) || !isset($allowedSemesters[(int)$semesterArg]))) {
    fwrite(STDERR, "Allowed semester IDs are 1 (Fall 2026) and 2 (Spring 2027).\n");
    exit(2);
}

$semesterIds = $semesterArg === null ? array_keys($allowedSemesters) : [(int)$semesterArg];
$sectionSql = "SELECT cs.CRN, cs.available_Seats,
        CASE
            WHEN cs.lecture_ID IS NOT NULL THEN l.avail_Seats
            WHEN cs.lab_ID IS NOT NULL THEN b.no_Of_Workstations
            ELSE NULL
        END AS capacity,
        (SELECT COUNT(*) FROM Enrollment e
         WHERE e.CRN = cs.CRN AND e.semester_ID = cs.semester_ID) AS enrolled
    FROM Course_Section cs
    LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
    LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID
    WHERE cs.semester_ID = ? AND cs.is_Cancelled = 0
    ORDER BY cs.CRN";
$select = $pdo->prepare($sectionSql . ($apply ? ' FOR UPDATE' : ''));
$update = $pdo->prepare('UPDATE Course_Section SET available_Seats = ? WHERE CRN = ? AND semester_ID = ? AND is_Cancelled = 0');

foreach ($semesterIds as $semesterId) {
    $semesterName = $allowedSemesters[$semesterId];
    if ($apply) {
        $pdo->beginTransaction();
    }
    try {
        $select->execute([$semesterId]);
        $sections = $select->fetchAll();
        $changes = [];
        $roomless = 0;
        $overCapacity = 0;
        foreach ($sections as $section) {
            $capacity = $section['capacity'] === null ? null : (int)$section['capacity'];
            $enrolled = (int)$section['enrolled'];
            $expected = $capacity === null ? 0 : max(0, $capacity - $enrolled);
            if ($capacity === null) {
                $roomless++;
            } elseif ($enrolled > $capacity) {
                $overCapacity++;
            }
            if ((int)$section['available_Seats'] !== $expected) {
                $changes[] = [
                    'CRN' => (int)$section['CRN'],
                    'from' => (int)$section['available_Seats'],
                    'to' => $expected,
                ];
                if ($apply) {
                    $update->execute([$expected, (int)$section['CRN'], $semesterId]);
                }
            }
        }
        if ($apply) {
            $select->execute([$semesterId]);
            foreach ($select->fetchAll() as $section) {
                $capacity = $section['capacity'] === null ? null : (int)$section['capacity'];
                $expected = $capacity === null ? 0 : max(0, $capacity - (int)$section['enrolled']);
                if ((int)$section['available_Seats'] !== $expected) {
                    throw new RuntimeException('Seat counter validation failed for CRN ' . $section['CRN']);
                }
            }
            $pdo->commit();
        }
        echo $semesterName . ': active_sections=' . count($sections)
            . ', changes=' . count($changes)
            . ', roomless=' . $roomless
            . ', occupied_over_capacity=' . $overCapacity
            . ', mode=' . ($apply ? 'APPLIED' : 'DRY-RUN') . "\n";
        foreach ($changes as $change) {
            echo '  CRN ' . $change['CRN'] . ': ' . $change['from'] . ' -> ' . $change['to'] . "\n";
        }
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, $semesterName . ' failed: ' . $error->getMessage() . "\n");
        exit(1);
    }
}

<?php

declare(strict_types=1);

/** Shared, semester-scoped helpers for Faculty attendance pages and actions. */
function faculty_attendance_semesters(PDO $pdo): array
{
    return all_rows(
        $pdo,
        "SELECT semester_ID, semester_Name, semester_Start
         FROM Semester
         WHERE semester_Name IN ('Fall 2026', 'Spring 2027')
         ORDER BY CASE semester_Name WHEN 'Fall 2026' THEN 1 ELSE 2 END"
    );
}

function faculty_attendance_selected_semester(array $semesters, $requested): int
{
    $byName = [];
    $validIds = [];
    foreach ($semesters as $semester) {
        $id = (int) $semester['semester_ID'];
        $byName[(string) $semester['semester_Name']] = $id;
        $validIds[$id] = true;
    }

    $requestedId = filter_var($requested, FILTER_VALIDATE_INT);
    if ($requestedId !== false && isset($validIds[(int) $requestedId])) {
        return (int) $requestedId;
    }

    return $byName['Fall 2026'] ?? (int) ($semesters[0]['semester_ID'] ?? 0);
}

function faculty_attendance_sections(PDO $pdo, int $facultyId, int $semesterId): array
{
    if ($semesterId <= 0) {
        return [];
    }

    return all_rows(
        $pdo,
        "SELECT cs.CRN, cs.course_ID, c.course_Name, c.course_Credits,
                cs.semester_ID, cs.time_Slot_ID, cs.section_No, cs.faculty_ID,
                s.semester_Name, s.semester_Start
         FROM Course_Section cs
         JOIN Course c ON c.course_ID = cs.course_ID
         JOIN Semester s ON s.semester_ID = cs.semester_ID
         WHERE cs.faculty_ID = ?
           AND cs.semester_ID = ?
           AND cs.is_Cancelled = 0
         ORDER BY c.course_ID, cs.section_No, cs.CRN",
        [$facultyId, $semesterId]
    );
}

function faculty_attendance_section(PDO $pdo, int $facultyId, int $semesterId, int $crn): ?array
{
    if ($semesterId <= 0 || $crn <= 0) {
        return null;
    }

    return one(
        $pdo,
        "SELECT cs.CRN, cs.course_ID, c.course_Name, c.course_Credits,
                cs.semester_ID, cs.time_Slot_ID, cs.section_No, cs.faculty_ID,
                s.semester_Name, s.semester_Start
         FROM Course_Section cs
         JOIN Course c ON c.course_ID = cs.course_ID
         JOIN Semester s ON s.semester_ID = cs.semester_ID
         WHERE cs.CRN = ?
           AND cs.faculty_ID = ?
           AND cs.semester_ID = ?
           AND cs.is_Cancelled = 0",
        [$crn, $facultyId, $semesterId]
    ) ?: null;
}

function faculty_attendance_roster(PDO $pdo, array $section, string $date): array
{
    return all_rows(
        $pdo,
        "SELECT e.student_ID, u.first_Name, u.last_Name,
                a.Present_Absent
         FROM Enrollment e
         JOIN Course_Section cs
           ON cs.CRN = e.CRN AND cs.semester_ID = e.semester_ID
         JOIN User u ON u.user_ID = e.student_ID
         LEFT JOIN Attendance a
           ON a.student_ID = e.student_ID
          AND a.CRN = e.CRN
          AND a.attendance_Date = ?
          AND a.attendance_Date <= CURDATE()
         WHERE e.CRN = ?
           AND e.semester_ID = ?
           AND cs.faculty_ID = ?
           AND cs.is_Cancelled = 0
         ORDER BY u.last_Name, u.first_Name, e.student_ID",
        [
            $date,
            (int) $section['CRN'],
            (int) $section['semester_ID'],
            (int) $section['faculty_ID'],
        ]
    );
}

function faculty_attendance_date_is_today(string $date): bool
{
    return $date === (new DateTimeImmutable('today'))->format('Y-m-d');
}

function faculty_attendance_date_is_valid(string $date): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

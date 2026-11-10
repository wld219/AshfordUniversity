<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);
$facultyID = (int) $_SESSION['user_id'];

$semester = ($_GET['term'] ?? 'fall') === 'spring' ? 'spring' : 'fall';
$sections = [];
$roster = [];
$attendanceHistory = [];
$crn = 0;
$selectedSection = null;

if ($semester === 'fall') {
    $sections = all_rows($pdo,
        "SELECT cs.CRN, c.course_ID, c.course_Name
         FROM Course_Section cs
         JOIN Course c ON c.course_ID = cs.course_ID
         JOIN Semester s ON s.semester_ID = cs.semester_ID
         WHERE cs.faculty_ID = ?
           AND s.semester_Name = 'Fall 2026'
           AND cs.is_Cancelled = 0
         ORDER BY c.course_ID, cs.CRN",
        [$facultyID]
    );

    $allowedCRNs = array_map(
        static fn(array $s): int => (int)$s['CRN'],
        $sections
    );

    $requested = filter_input(INPUT_GET, 'crn', FILTER_VALIDATE_INT);
    $crn = $requested && in_array($requested, $allowedCRNs, true)
        ? $requested : ($allowedCRNs[0] ?? 0);

    foreach ($sections as $section) {
        if ((int)$section['CRN'] === $crn) {
            $selectedSection = $section;
            break;
        }
    }

    if ($crn > 0) {
        $roster = all_rows($pdo,
            "SELECT
                e.student_ID,
                u.first_Name,
                u.last_Name,
                l.user_Email,
                e.Grade,
                st.major_ID,
                st.student_Year,
                st.student_Type,
                COALESCE(a.present_count, 0) AS present_count,
                COALESCE(a.absent_count, 0) AS absent_count,
                COALESCE(a.record_count, 0) AS record_count
             FROM Enrollment e
             JOIN User u ON u.user_ID = e.student_ID
             LEFT JOIN Login l ON l.user_ID = e.student_ID
             LEFT JOIN Student st ON st.student_ID = e.student_ID
             JOIN Course_Section cs ON cs.CRN = e.CRN
             JOIN Semester sem ON sem.semester_ID = cs.semester_ID
             LEFT JOIN (
                SELECT student_ID, CRN,
                    SUM(CASE WHEN Present_Absent = 'Present' THEN 1 ELSE 0 END)
                        AS present_count,
                    SUM(CASE WHEN Present_Absent = 'Absent' THEN 1 ELSE 0 END)
                        AS absent_count,
                    COUNT(*) AS record_count
                FROM Attendance
                GROUP BY student_ID, CRN
             ) a ON a.student_ID = e.student_ID AND a.CRN = e.CRN
             WHERE e.CRN = ?
               AND cs.faculty_ID = ?
               AND sem.semester_Name = 'Fall 2026'
               AND cs.is_Cancelled = 0
             ORDER BY u.last_Name, u.first_Name",
            [$crn, $facultyID]
        );

        $historyRows = all_rows($pdo,
            "SELECT a.student_ID, a.attendance_Date, a.Present_Absent
             FROM Attendance a
             JOIN Enrollment e
               ON e.student_ID = a.student_ID AND e.CRN = a.CRN
             JOIN Course_Section cs ON cs.CRN = e.CRN
             JOIN Semester s ON s.semester_ID = cs.semester_ID
             WHERE a.CRN = ?
               AND cs.faculty_ID = ?
               AND s.semester_Name = 'Fall 2026'
               AND cs.is_Cancelled = 0
             ORDER BY a.attendance_Date DESC",
            [$crn, $facultyID]
        );

        foreach ($historyRows as $entry) {
            $attendanceHistory[(int)$entry['student_ID']][] = $entry;
        }
    }
}

$totalStudents = count($roster);
$gradesPosted = 0;
$totalPresent = 0;
$totalRecorded = 0;

foreach ($roster as $student) {
    if ($student['Grade'] !== null && trim((string)$student['Grade']) !== '') {
        $gradesPosted++;
    }
    $totalPresent += (int)$student['present_count'];
    $totalRecorded += (int)$student['record_count'];
}

$averageAttendance = $totalRecorded > 0
    ? round(($totalPresent / $totalRecorded) * 100, 1)
    : null;

page_start('Course Rosters', 'Faculty', 'rosters.php');
flash();
require_once __DIR__ . '/partials/rosters_view.php';
page_end();

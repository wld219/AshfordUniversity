<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);
$facultyID = (int) $_SESSION['user_id'];

// Build the term selector from this faculty member's actual assigned sections.
$semesterOptions = all_rows(
    $pdo,
    "SELECT semester_ID, semester_Name, semester_Start
     FROM (
        SELECT DISTINCT s.semester_ID, s.semester_Name, s.semester_Start
        FROM Semester s
        WHERE s.semester_Name IN ('Fall 2026', 'Spring 2027')
        UNION
        SELECT DISTINCT s.semester_ID, s.semester_Name, s.semester_Start
        FROM Semester s
        JOIN Course_Section cs ON cs.semester_ID = s.semester_ID
        WHERE cs.faculty_ID = ? AND cs.is_Cancelled = 0
        UNION
        SELECT DISTINCT s.semester_ID, s.semester_Name, s.semester_Start
        FROM Semester s
        JOIN Faculty_History fh ON fh.semester_ID = s.semester_ID
        WHERE fh.faculty_ID = ?
     ) assigned_terms
     ORDER BY semester_Start DESC, semester_ID DESC",
    [$facultyID, $facultyID]
);
$semesterOptionsById = [];
foreach ($semesterOptions as $option) {
    $semesterOptionsById[(int)$option['semester_ID']] = $option;
}

$termParam = trim((string)($_GET['term'] ?? ''));
$legacyTermNames = [
    'fall' => 'Fall 2026',
    'spring' => 'Spring 2027',
];
$semesterId = 0;
if (ctype_digit($termParam) && isset($semesterOptionsById[(int)$termParam])) {
    $semesterId = (int)$termParam;
} elseif (isset($legacyTermNames[strtolower($termParam)])) {
    $legacyName = $legacyTermNames[strtolower($termParam)];
    foreach ($semesterOptions as $option) {
        if ($option['semester_Name'] === $legacyName) {
            $semesterId = (int)$option['semester_ID'];
            break;
        }
    }
}
if ($semesterId === 0 && $semesterOptions) {
    // Retain Fall 2026 as the original default when it is assigned here.
    foreach ($semesterOptions as $option) {
        if ($option['semester_Name'] === 'Fall 2026') {
            $semesterId = (int)$option['semester_ID'];
            break;
        }
    }
    $semesterId = $semesterId ?: (int)$semesterOptions[0]['semester_ID'];
}
$semesterName = $semesterOptionsById[$semesterId]['semester_Name'] ?? '';
$semester = (string)$semesterId;
$isHistoricalSemester = $semesterName !== ''
    && !in_array($semesterName, ['Fall 2026', 'Spring 2027'], true);

// Handle midterm grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_midterm_grade') {
    csrf_verify();
    if ($isHistoricalSemester) {
        redirect_with_flash(
            url('faculty/rosters.php?term=' . $semester),
            'error',
            'Historical academic records are read-only.'
        );
    }
    $studentID = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT);
    $crn = filter_var($_POST['crn'] ?? null, FILTER_VALIDATE_INT);
    $midtermGrade = trim((string) ($_POST['midterm_grade'] ?? ''));

    if (!$studentID || !$crn) {
        redirect_with_flash(
            url('faculty/rosters.php'),
            'error',
            'Invalid student or course section.'
        );
    }

    // Verify the section belongs to this faculty
    $section = one(
        $pdo,
        'SELECT cs.CRN
         FROM Course_Section cs
         JOIN Enrollment e ON e.CRN = cs.CRN AND e.semester_ID = cs.semester_ID
         WHERE cs.CRN = ? AND cs.faculty_ID = ? AND cs.semester_ID = ?
           AND cs.is_Cancelled = 0 AND e.student_ID = ?',
        [$crn, $facultyID, $semesterId, $studentID]
    );

    if (!$section) {
        redirect_with_flash(
            url('faculty/rosters.php'),
            'error',
            'Course section not found or not assigned to you.'
        );
    }

    // Validate grade
    $validGrades = ['A','A-','B+','B','B-','C+','C','C-','D+','D','F','W','I',''];
    if (!in_array($midtermGrade, $validGrades, true)) {
        redirect_with_flash(
            url('faculty/rosters.php'),
            'error',
            'Invalid midterm grade.'
        );
    }

    try {
        $pdo->beginTransaction();
        $pdo->prepare(
            'UPDATE Enrollment
             SET midterm_Grade = ?,
                 midterm_Submitted_At = CASE WHEN ? <> "" THEN NOW() ELSE NULL END
             WHERE student_ID = ? AND CRN = ? AND semester_ID = ?'
        )->execute([$midtermGrade, $midtermGrade, $studentID, $crn, $semesterId]);
        audit_log($pdo, 'Update', 'Enrollment', (string) $crn, 'Midterm grade updated for student ' . (string) $studentID);
        $pdo->commit();
        redirect_with_flash(
            url('faculty/rosters.php?term=' . $semester . '&crn=' . $crn),
            'success',
            'Midterm grade updated successfully.'
        );
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Midterm grade update failed: ' . $e->getMessage());
        redirect_with_flash(
            url('faculty/rosters.php'),
            'error',
            'Midterm grade could not be updated.'
        );
    }
    exit;
}

$sections = [];
$roster = [];
$attendanceHistory = [];
$crn = 0;
$selectedSection = null;

if ($isHistoricalSemester) {
    $sections = all_rows($pdo,
        "SELECT fh.CRN, fh.course_ID, c.course_Name
         FROM Faculty_History fh
         JOIN Course c ON c.course_ID = fh.course_ID
         WHERE fh.faculty_ID = ? AND fh.semester_ID = ?
         ORDER BY c.course_ID, fh.CRN",
        [$facultyID, $semesterId]
    );
} else {
    $sections = all_rows($pdo,
        "SELECT cs.CRN, c.course_ID, c.course_Name
         FROM Course_Section cs
         JOIN Course c ON c.course_ID = cs.course_ID
         WHERE cs.faculty_ID = ? AND cs.semester_ID = ?
           AND cs.is_Cancelled = 0
         ORDER BY c.course_ID, cs.CRN",
        [$facultyID, $semesterId]
    );
}

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
    if ($isHistoricalSemester) {
        $roster = all_rows($pdo,
            "SELECT
                h.student_ID,
                NULL AS midterm_Grade,
                h.Grade,
                u.first_Name,
                u.last_Name,
                l.user_Email,
                st.major_ID,
                st.student_Year,
                st.student_Type,
                0 AS present_count,
                0 AS absent_count,
                0 AS record_count
             FROM Faculty_History fh
             JOIN Student_History h
               ON h.CRN = fh.CRN AND h.semester_ID = fh.semester_ID
              AND h.course_ID = fh.course_ID
             JOIN User u ON u.user_ID = h.student_ID
             LEFT JOIN Login l ON l.user_ID = h.student_ID
             LEFT JOIN Student st ON st.student_ID = h.student_ID
             WHERE fh.faculty_ID = ? AND fh.CRN = ? AND fh.semester_ID = ?
             ORDER BY u.last_Name, u.first_Name",
            [$facultyID, $crn, $semesterId]
        );
    } else {
        $roster = all_rows($pdo,
            "SELECT
                e.student_ID,
                e.midterm_Grade,
                e.Grade,
                u.first_Name,
                u.last_Name,
                l.user_Email,
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
             JOIN Course_Section cs
               ON cs.CRN = e.CRN AND cs.semester_ID = e.semester_ID
             JOIN Semester sem ON sem.semester_ID = cs.semester_ID
             LEFT JOIN (
                SELECT student_ID, CRN,
                    SUM(CASE WHEN Present_Absent = 'Present' THEN 1 ELSE 0 END)
                        AS present_count,
                    SUM(CASE WHEN Present_Absent = 'Absent' THEN 1 ELSE 0 END)
                        AS absent_count,
                    COUNT(*) AS record_count
                FROM Attendance
                WHERE attendance_Date <= CURDATE()
                GROUP BY student_ID, CRN
             ) a ON a.student_ID = e.student_ID AND a.CRN = e.CRN
             WHERE e.CRN = ?
               AND cs.faculty_ID = ?
               AND sem.semester_ID = ?
               AND e.semester_ID = cs.semester_ID
               AND cs.is_Cancelled = 0
             ORDER BY u.last_Name, u.first_Name",
            [$crn, $facultyID, $semesterId]
        );
    }

    $historyRows = $isHistoricalSemester ? [] : all_rows($pdo,
            "SELECT a.student_ID, a.attendance_Date, a.Present_Absent
             FROM Attendance a
             JOIN Enrollment e
               ON e.student_ID = a.student_ID AND e.CRN = a.CRN
             JOIN Course_Section cs
               ON cs.CRN = e.CRN AND cs.semester_ID = e.semester_ID
             JOIN Semester s ON s.semester_ID = cs.semester_ID
             WHERE a.CRN = ?
               AND cs.faculty_ID = ?
               AND s.semester_ID = ?
               AND cs.is_Cancelled = 0
               AND a.attendance_Date <= CURDATE()
             ORDER BY a.attendance_Date DESC",
            [$crn, $facultyID, $semesterId]
        );

    foreach ($historyRows as $entry) {
        $attendanceHistory[(int)$entry['student_ID']][] = $entry;
    }
}

$totalStudents = count($roster);
$gradesPosted = 0;
$midtermPosted = 0;
$totalPresent = 0;
$totalRecorded = 0;

foreach ($roster as $student) {
    if ($student['Grade'] !== null && trim((string)$student['Grade']) !== '') {
        $gradesPosted++;
    }
    if ($student['midterm_Grade'] !== null && trim((string)$student['midterm_Grade']) !== '') {
        $midtermPosted++;
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

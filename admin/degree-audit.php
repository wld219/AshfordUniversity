<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin']);

$studentID = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
$studentID = $studentID ?: 0;
$query = trim((string) ($_GET['q'] ?? ''));
$students = [];
$selectedStudent = null;
$audits = [];

if ($query !== '') {
    $students = all_rows(
        $pdo,
        "SELECT u.user_ID, u.first_Name, u.last_Name
         FROM User u
         JOIN Student s ON s.student_ID = u.user_ID
         WHERE CAST(u.user_ID AS CHAR) LIKE ?
            OR u.first_Name LIKE ?
            OR u.last_Name LIKE ?
         ORDER BY u.last_Name, u.first_Name
         LIMIT 30",
        array_fill(0, 3, '%' . $query . '%')
    );
}

if ($studentID > 0) {
    $selectedStudent = one(
        $pdo,
        "SELECT u.user_ID, u.first_Name, u.last_Name
         FROM User u
         JOIN Student s ON s.student_ID = u.user_ID
         WHERE u.user_ID = ?",
        [$studentID]
    );

    if ($selectedStudent) {
        // Reuse the same academic calculations as the Student Portal.
        require __DIR__ . '/../student/degree_audit_data.php';
    }
}


// Detect whether this database has the optional course_Code column.
$courseCodeColumn = $pdo->query(
    "SHOW COLUMNS FROM Course LIKE 'course_Code'"
)->fetch();

$courseCodeExpression = $courseCodeColumn
    ? 'c.course_Code'
    : 'CAST(c.course_ID AS CHAR)';

$semesterRecords = [];
$semesterTotals = [];
$cumulativePoints = 0.0;
$cumulativeGpaCredits = 0;
$earnedCredits = 0;

if ($selectedStudent) {
    // Prefer academic history when the same CRN also exists in Enrollment.
    $records = all_rows(
        $pdo,
        "SELECT h.CRN, h.semester_ID, h.Grade,
                cs.section_No, {$courseCodeExpression} AS course_Code, c.course_Name,
                c.course_Credits, s.semester_Name,
                1 AS record_priority
         FROM Student_History h
         JOIN Course_Section cs ON cs.CRN = h.CRN
         JOIN Course c ON c.course_ID = h.course_ID
         JOIN Semester s ON s.semester_ID = h.semester_ID
         WHERE h.student_ID = ?

         UNION ALL

         SELECT en.CRN, en.semester_ID, en.Grade,
                cs.section_No, {$courseCodeExpression} AS course_Code, c.course_Name,
                c.course_Credits, s.semester_Name,
                2 AS record_priority
         FROM Enrollment en
         JOIN Course_Section cs ON cs.CRN = en.CRN
         JOIN Course c ON c.course_ID = cs.course_ID
         JOIN Semester s ON s.semester_ID = en.semester_ID
         WHERE en.student_ID = ?

         ORDER BY semester_ID, CRN, record_priority",
        [$studentID, $studentID]
    );

    $seen = [];

    foreach ($records as $record) {
        $semesterKey = (int) $record['semester_ID'];
        $crn = (int) $record['CRN'];
        $key = $semesterKey . ':' . $crn;

        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;

        $semesterRecords[$semesterKey]['name'] = $record['semester_Name'];
        $semesterRecords[$semesterKey]['id'] = $semesterKey;
        $semesterRecords[$semesterKey]['courses'][] = $record;

        $credits = (int) $record['course_Credits'];
        $grade = trim((string) ($record['Grade'] ?? ''));
        $points = $grade !== '' ? grade_points($grade) : null;

        if (!isset($semesterTotals[$semesterKey])) {
            $semesterTotals[$semesterKey] = [
                'points' => 0.0,
                'credits' => 0,
            ];
        }

        if ($points !== null) {
            $semesterTotals[$semesterKey]['points'] += $points * $credits;
            $semesterTotals[$semesterKey]['credits'] += $credits;
            $cumulativePoints += $points * $credits;
            $cumulativeGpaCredits += $credits;

            if ($points > 0) {
                $earnedCredits += $credits;
            }
        }
    }

    // Show both planned semesters, even if the student has no courses yet.
    $terms = all_rows(
        $pdo,
        "SELECT semester_ID, semester_Name
         FROM Semester
         WHERE semester_Name IN ('Fall 2026', 'Spring 2027')
         ORDER BY semester_Start, semester_ID",
        []
    );

    foreach ($terms as $term) {
        $id = (int) $term['semester_ID'];
        if (!isset($semesterRecords[$id])) {
            $semesterRecords[$id] = [
                'id' => $id,
                'name' => $term['semester_Name'],
                'courses' => [],
            ];
        }
    }

    // Put Fall 2026 first, Spring 2027 second, then other recorded terms.
    uasort($semesterRecords, static function ($a, $b) {
        $order = ['Fall 2026' => 0, 'Spring 2027' => 1];
        $aa = $order[$a['name']] ?? 2;
        $bb = $order[$b['name']] ?? 2;
        return ($aa <=> $bb) ?: ($a['id'] <=> $b['id']);
    });
}

$cumulativeGpa = $cumulativeGpaCredits > 0
    ? number_format($cumulativePoints / $cumulativeGpaCredits, 2)
    : 'N/A';

$majorNames = [];
$minorName = 'Not declared';

if ($selectedStudent) {
    foreach ($audits as $audit) {
        if ($audit['type'] === 'Major') {
            $majorNames[] = $audit['name'];
        } elseif ($audit['type'] === 'Minor') {
            $minorName = $audit['name'];
        }
    }
}

$adminDegreeAuditHolds = [];

if ($selectedStudent) {
    $stmt = $pdo->prepare(
        "SELECT sh.hold_ID, h.hold_Type, sh.hold_Date, NULL AS hold_Reason
         FROM student_hold sh
         INNER JOIN hold h ON h.hold_ID = sh.hold_ID
         WHERE sh.student_ID = ?
         ORDER BY sh.hold_Date DESC, sh.hold_ID"
    );
    $stmt->execute([(int)$selectedStudent['user_ID']]);
    $adminDegreeAuditHolds = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

page_start('Degree Audit', 'Admin', 'degree-audit.php');
require __DIR__ . '/partials/degree_audit_admin_view.php';
page_end();

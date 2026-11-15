<?php
declare(strict_types=1);
require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_role(['Student']);
$studentID = (int) $_SESSION['user_id'];
if (isset($_POST['drop'])) {
    csrf_verify();
    require_once __DIR__ . '/schedule_actions.php';
}
// Inspect optional grading structures without modifying the schema.
$hasTable = static function (PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
};
$hasColumn = static function (PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
};
$midtermJoin = '';
$midtermSelect = 'NULL AS midterm_grade';
if ($hasTable($pdo, 'Midterm_Grade') && $hasColumn($pdo, 'Midterm_Grade', 'midterm_Grade') && $hasColumn($pdo, 'Midterm_Grade', 'is_Published')) {
    $midtermJoin = ' LEFT JOIN Midterm_Grade mg ON mg.student_ID = e.student_ID AND mg.CRN = e.CRN AND mg.is_Published = 1';
    $midtermSelect = 'mg.midterm_Grade AS midterm_grade';
}
$currentJoin = '';
$currentSelect = 'NULL AS current_grade';
if ($hasTable($pdo, 'Overall_Grade') && $hasColumn($pdo, 'Overall_Grade', 'overall_Grade')) {
    $currentJoin = ' LEFT JOIN Overall_Grade og ON og.student_ID = e.student_ID AND og.CRN = e.CRN';
    // Only display explicitly published current grades when publication is supported.
    if ($hasColumn($pdo, 'Overall_Grade', 'is_Published')) {
        $currentJoin .= ' AND og.is_Published = 1';
        $currentSelect = 'og.overall_Grade AS current_grade';
    }
}
$finalSelect = $hasColumn($pdo, 'Enrollment', 'Grade') ? 'e.Grade AS final_grade' : 'NULL AS final_grade';
$slotSelect = $hasColumn($pdo, 'Course_Section', 'time_Slot_ID') ? 'cs.time_Slot_ID' : 'NULL AS time_Slot_ID';
$schedule = all_rows($pdo, "
    SELECT c.course_ID, c.course_Name, c.course_Credits,
           cs.CRN, cs.section_No, $slotSelect,
           s.semester_ID, s.semester_Name,
           u.first_Name, u.last_Name,
           $currentSelect, $midtermSelect, $finalSelect
    FROM Enrollment e
    JOIN Course_Section cs ON cs.CRN = e.CRN
    JOIN Course c ON c.course_ID = cs.course_ID
    JOIN Semester s ON s.semester_ID = cs.semester_ID
    LEFT JOIN User u ON u.user_ID = cs.faculty_ID
    $midtermJoin $currentJoin
    WHERE e.student_ID = ?
    ORDER BY s.semester_ID DESC, c.course_ID, cs.section_No
", [$studentID]) ?? [];
$semesters = [];
foreach ($schedule as $course) {
    $semesters[(int)$course['semester_ID']] = (string)$course['semester_Name'];
}
$selectedSemester = filter_input(INPUT_GET, 'semester', FILTER_VALIDATE_INT);
if (!$selectedSemester || !isset($semesters[$selectedSemester])) {
    $selectedSemester = $semesters ? (int)array_key_first($semesters) : 0;
}
$termCourses = array_values(array_filter($schedule, static fn($course) => (int)$course['semester_ID'] === $selectedSemester));
$totalCredits = array_sum(array_map(static fn($course) => (float)$course['course_Credits'], $termCourses));
page_start('My Schedule', 'Student', 'schedule.php');
flash();
require_once __DIR__ . '/partials/schedule_view.php';
page_end();

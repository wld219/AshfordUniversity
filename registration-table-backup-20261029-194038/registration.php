<?php
declare(strict_types=1);
require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_role(['Student']);
$studentID = (int) $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $crn = (int) ($_POST['crn'] ?? 0);
    require_once __DIR__ . '/registration_actions.php';
}
$registrationNotice = current_registration_semester($pdo);
$windowOpen = false;
$windowMessage = 'No registration semester is available.';
$semesterName = 'No semester available';
if ($registrationNotice !== null) {
    [$windowOpen, , $windowMessage] = registration_window_status($pdo, (int) $registrationNotice);
    $term = one($pdo, 'SELECT semester_Name FROM Semester WHERE semester_ID = ?', [(int) $registrationNotice]);
    $semesterName = $term['semester_Name'] ?? $semesterName;
}
$search = trim((string) ($_GET['search'] ?? ''));
$search = substr($search, 0, 100);
$department = trim((string) ($_GET['department'] ?? ''));
$department = substr($department, 0, 100);
$departments = all_rows($pdo, 'SELECT dept_ID, dept_Name FROM Department ORDER BY dept_Name') ?? [];
$sections = [];
if ($registrationNotice !== null) {
    $sql = 'SELECT c.course_ID, c.course_Name, c.course_Credits, d.dept_Name,
                   cs.CRN, cs.section_No, cs.available_Seats, cs.time_Slot_ID
            FROM Course_Section cs
            JOIN Course c ON c.course_ID = cs.course_ID
            JOIN Department d ON d.dept_ID = c.dept_ID
            WHERE cs.semester_ID = ? AND cs.is_Cancelled = 0';
    $params = [(int) $registrationNotice];
    if ($search !== '') {
        $sql .= ' AND (c.course_Name LIKE ? OR CAST(c.course_ID AS CHAR) LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    if ($department !== '' && ctype_digit($department)) {
        $sql .= ' AND c.dept_ID = ?';
        $params[] = (int) $department;
    }
    $sql .= ' ORDER BY c.course_ID, cs.section_No';
    $sections = all_rows($pdo, $sql, $params) ?? [];
}
$creditRow = $registrationNotice === null ? null : one($pdo,
    'SELECT COALESCE(SUM(c.course_Credits), 0) AS total FROM Enrollment en
     JOIN Course_Section cs ON cs.CRN = en.CRN
     JOIN Course c ON c.course_ID = cs.course_ID
     WHERE en.student_ID = ? AND en.semester_ID = ?',
    [$studentID, (int) $registrationNotice]);
$registeredCredits = (int) ($creditRow['total'] ?? 0);
$creditLimit = student_credit_limit($pdo, $studentID);
$holdRow = one($pdo, 'SELECT COUNT(*) AS total FROM Student_Hold WHERE student_ID = ?', [$studentID]);
$holdCount = (int) ($holdRow['total'] ?? 0);
page_start('Course Registration', 'Student', 'registration.php');
flash();
require __DIR__ . '/partials/registration_view.php';
page_end();

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

// ASHFORD_REG_FLEX_SEARCH_V1
$crnFilter = trim((string) ($_GET['crn_filter'] ?? ''));
$facultyFilter = trim((string) ($_GET['faculty'] ?? ''));
$seatsFilter = trim((string) ($_GET['seats'] ?? ''));

$crnFilter = substr($crnFilter, 0, 30);
$facultyFilter = substr($facultyFilter, 0, 30);

if ($crnFilter !== '' && !ctype_digit($crnFilter)) {
    $crnFilter = '0';
}
if ($facultyFilter !== '' && !ctype_digit($facultyFilter)) {
    $facultyFilter = '0';
}
if (!in_array($seatsFilter, ['', 'open', 'full'], true)) {
    $seatsFilter = '';
}

$facultyOptions = all_rows(
    $pdo,
    'SELECT f.faculty_ID,
            TRIM(CONCAT(u.first_Name, CHAR(32), u.last_Name)) AS faculty_Name
     FROM Faculty f
     JOIN User u ON u.user_ID = f.faculty_ID
     ORDER BY u.last_Name, u.first_Name'
) ?? [];

$sections = [];
if ($registrationNotice !== null) {
    $sql = 'SELECT c.course_ID, c.course_Name, c.course_Credits, d.dept_Name,
                   cs.CRN, cs.section_No, cs.available_Seats, cs.time_Slot_ID,
                   TRIM(CONCAT(u.first_Name, CHAR(32), u.last_Name)) AS faculty_Name
            FROM Course_Section cs
            JOIN Course c ON c.course_ID = cs.course_ID
            JOIN Department d ON d.dept_ID = c.dept_ID
            LEFT JOIN Faculty f ON f.faculty_ID = cs.faculty_ID
            LEFT JOIN User u ON u.user_ID = f.faculty_ID
            WHERE cs.semester_ID = ? AND cs.is_Cancelled = 0';
    $params = [(int) $registrationNotice];
    if ($search !== '') {
        // Multiple course terms use OR logic.
        $terms = array_values(array_unique(array_filter(
            array_map('trim', explode(',', $search)),
            static fn($value) => $value !== ''
        )));

        $terms = array_slice($terms, 0, 10);
        $conditions = [];

        foreach ($terms as $term) {
            $conditions[] = '(c.course_Name LIKE ? OR CAST(c.course_ID AS CHAR) LIKE ?)';
            $params[] = '%' . $term . '%';
            $params[] = '%' . $term . '%';
        }

        if ($conditions) {
            $sql .= ' AND (' . implode(' OR ', $conditions) . ')';
        }
    }
    if ($department !== '' && ctype_digit($department)) {
        $sql .= ' AND c.dept_ID = ?';
        $params[] = (int) $department;
    }

    if ($crnFilter !== '') {
        $sql .= ' AND cs.CRN = ?';
        $params[] = (int) $crnFilter;
    }

    if ($facultyFilter !== '') {
        $sql .= ' AND cs.faculty_ID = ?';
        $params[] = (int) $facultyFilter;
    }

    if ($seatsFilter === 'open') {
        $sql .= ' AND cs.available_Seats > 0';
    } elseif ($seatsFilter === 'full') {
        $sql .= ' AND cs.available_Seats <= 0';
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
// ASHFORD_ADD_DROP_V1: Student's enrolled sections for this registration term.
$myRegisteredCourses = [];
if ($registrationNotice !== null) {
    $myRegisteredCourses = all_rows($pdo,
        'SELECT en.CRN, en.Grade, c.course_ID, c.course_Name, c.course_Credits,
                cs.section_No, cs.available_Seats
         FROM Enrollment en
         JOIN Course_Section cs ON cs.CRN = en.CRN
         JOIN Course c ON c.course_ID = cs.course_ID
         WHERE en.student_ID = ? AND en.semester_ID = ?
         ORDER BY c.course_ID, cs.section_No',
        [$studentID, (int) $registrationNotice]) ?? [];
}
$myRegisteredCRNs = array_fill_keys(array_map(
    static fn($row) => (int) $row['CRN'], $myRegisteredCourses
), true);

page_start('Course Registration', 'Student', 'registration.php');
flash();
require __DIR__ . '/partials/registration_view.php';
page_end();

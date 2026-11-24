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
$semesterOptions = all_rows(
    $pdo,
    "SELECT semester_ID, semester_Name
     FROM Semester
     WHERE semester_Name IN ('Fall 2026', 'Spring 2027')
     ORDER BY CASE semester_Name WHEN 'Fall 2026' THEN 1 WHEN 'Spring 2027' THEN 2 END"
) ?? [];
$semesterByName = [];
foreach ($semesterOptions as $option) {
    $semesterByName[(string) $option['semester_Name']] = (int) $option['semester_ID'];
}
$defaultSemesterID = $semesterByName['Fall 2026'] ?? current_registration_semester($pdo);
$requestedSemesterID = filter_var($_GET['semester'] ?? null, FILTER_VALIDATE_INT);
$validSemesterIDs = array_map(static fn($option) => (int) $option['semester_ID'], $semesterOptions);
$registrationNotice = $requestedSemesterID !== false && $requestedSemesterID !== null
    && in_array((int) $requestedSemesterID, $validSemesterIDs, true)
        ? (int) $requestedSemesterID
        : $defaultSemesterID;
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
$levelFilter = trim((string) ($_GET['level'] ?? ''));
$dayFilter = trim((string) ($_GET['day'] ?? ''));
$timeFilter = trim((string) ($_GET['time'] ?? ''));

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
if (!in_array($levelFilter, ['', 'Undergraduate', 'Graduate'], true)) {
    $levelFilter = '';
}
$validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
if (!in_array($dayFilter, array_merge([''], $validDays), true)) {
    $dayFilter = '';
}
if (!in_array($timeFilter, ['', 'morning', 'afternoon', 'evening'], true)) {
    $timeFilter = '';
}
$dayOptions = all_rows($pdo, 'SELECT day_Of_Week FROM Day ORDER BY day_ID') ?? [];

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
    $sql = 'SELECT c.course_ID, c.course_Name, c.course_Credits, c.course_Type, d.dept_Name,
                   cs.CRN, cs.section_No, cs.available_Seats, cs.time_Slot_ID,
                   cs.lecture_ID, cs.lab_ID,
                   CASE WHEN cs.lecture_ID IS NOT NULL AND cs.lab_ID IS NOT NULL THEN "Lecture / Lab"
                        WHEN cs.lab_ID IS NOT NULL THEN "Lab" ELSE "Lecture" END AS section_Type,
                   (SELECT GROUP_CONCAT(dy.day_Of_Week ORDER BY dy.day_ID SEPARATOR ", ")
                    FROM Time_Slot_Day tsd JOIN Day dy ON dy.day_ID = tsd.day_ID
                    WHERE tsd.time_Slot_ID = cs.time_Slot_ID) AS meeting_Days,
                   (SELECT MIN(p.start_Time) FROM Time_Slot_Period tsp
                    JOIN Period p ON p.period_ID = tsp.period_ID WHERE tsp.time_Slot_ID = cs.time_Slot_ID) AS meeting_Start,
                   (SELECT MAX(p.end_Time) FROM Time_Slot_Period tsp
                    JOIN Period p ON p.period_ID = tsp.period_ID WHERE tsp.time_Slot_ID = cs.time_Slot_ID) AS meeting_End,
                   lr.room_Number AS lecture_Room_Number, lb.Bldg_Name AS lecture_Building_Name,
                   labr.room_Number AS lab_Room_Number, labb.Bldg_Name AS lab_Building_Name,
                   TRIM(CONCAT(u.first_Name, CHAR(32), u.last_Name)) AS faculty_Name
            FROM Course_Section cs
            JOIN Course c ON c.course_ID = cs.course_ID
            JOIN Department d ON d.dept_ID = c.dept_ID
            LEFT JOIN Faculty f ON f.faculty_ID = cs.faculty_ID
            LEFT JOIN User u ON u.user_ID = f.faculty_ID
            LEFT JOIN Room lr ON lr.room_ID = cs.lecture_ID
            LEFT JOIN Building lb ON lb.Bldg_ID = lr.Bldg_ID
            LEFT JOIN Room labr ON labr.room_ID = cs.lab_ID
            LEFT JOIN Building labb ON labb.Bldg_ID = labr.Bldg_ID
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
            $conditions[] = '(c.course_Name LIKE ? OR c.course_ID LIKE ?)';
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

    if ($levelFilter !== '') {
        $sql .= ' AND c.course_Type = ?';
        $params[] = $levelFilter;
    }
    if ($dayFilter !== '') {
        $sql .= ' AND EXISTS (
            SELECT 1 FROM Time_Slot_Day filter_day
            JOIN Day filter_day_name ON filter_day_name.day_ID = filter_day.day_ID
            WHERE filter_day.time_Slot_ID = cs.time_Slot_ID AND filter_day_name.day_Of_Week = ?
        )';
        $params[] = $dayFilter;
    }
    if ($timeFilter !== '') {
        $timeRange = [
            'morning' => ['00:00:00', '11:59:59'],
            'afternoon' => ['12:00:00', '16:59:59'],
            'evening' => ['17:00:00', '23:59:59'],
        ][$timeFilter];
        $sql .= ' AND EXISTS (
            SELECT 1 FROM Time_Slot_Period filter_period_slot
            JOIN Period filter_period ON filter_period.period_ID = filter_period_slot.period_ID
            WHERE filter_period_slot.time_Slot_ID = cs.time_Slot_ID
              AND filter_period.start_Time >= ? AND filter_period.start_Time <= ?
        )';
        array_push($params, $timeRange[0], $timeRange[1]);
    }

    $sql .= ' ORDER BY c.course_ID, cs.section_No';
    $sections = all_rows($pdo, $sql, $params) ?? [];
}
$creditRow = $registrationNotice === null ? null : one($pdo,
    'SELECT COALESCE(SUM(c.course_Credits), 0) AS total FROM Enrollment en
     JOIN Course_Section cs ON cs.CRN = en.CRN AND cs.semester_ID = en.semester_ID
     JOIN Course c ON c.course_ID = cs.course_ID
     WHERE en.student_ID = ? AND en.semester_ID = ? AND cs.is_Cancelled = 0',
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
                cs.section_No, cs.available_Seats, cs.time_Slot_ID, cs.lecture_ID, cs.lab_ID,
                CASE WHEN cs.lecture_ID IS NOT NULL AND cs.lab_ID IS NOT NULL THEN "Lecture / Lab"
                     WHEN cs.lab_ID IS NOT NULL THEN "Lab" ELSE "Lecture" END AS section_Type,
                (SELECT GROUP_CONCAT(dy.day_Of_Week ORDER BY dy.day_ID SEPARATOR ", ")
                 FROM Time_Slot_Day tsd JOIN Day dy ON dy.day_ID = tsd.day_ID
                 WHERE tsd.time_Slot_ID = cs.time_Slot_ID) AS meeting_Days,
                (SELECT MIN(p.start_Time) FROM Time_Slot_Period tsp
                 JOIN Period p ON p.period_ID = tsp.period_ID WHERE tsp.time_Slot_ID = cs.time_Slot_ID) AS meeting_Start,
                (SELECT MAX(p.end_Time) FROM Time_Slot_Period tsp
                 JOIN Period p ON p.period_ID = tsp.period_ID WHERE tsp.time_Slot_ID = cs.time_Slot_ID) AS meeting_End,
                lr.room_Number AS lecture_Room_Number, lb.Bldg_Name AS lecture_Building_Name,
                labr.room_Number AS lab_Room_Number, labb.Bldg_Name AS lab_Building_Name
         FROM Enrollment en
         JOIN Course_Section cs ON cs.CRN = en.CRN AND cs.semester_ID = en.semester_ID
         JOIN Course c ON c.course_ID = cs.course_ID
         LEFT JOIN Room lr ON lr.room_ID = cs.lecture_ID
         LEFT JOIN Building lb ON lb.Bldg_ID = lr.Bldg_ID
         LEFT JOIN Room labr ON labr.room_ID = cs.lab_ID
         LEFT JOIN Building labb ON labb.Bldg_ID = labr.Bldg_ID
         WHERE en.student_ID = ? AND en.semester_ID = ? AND cs.is_Cancelled = 0
         ORDER BY c.course_ID, cs.section_No',
        [$studentID, (int) $registrationNotice]) ?? [];
}
$myRegisteredCRNs = array_fill_keys(array_map(
    static fn($row) => (int) $row['CRN'], $myRegisteredCourses
), true);

// Prerequisite check: which prerequisites are not met for each section.
$sectionPrereqStatus = [];
if ($registrationNotice !== null && count($sections) > 0) {
    $crns = array_map(static fn($row) => (int) $row['CRN'], $sections);
    $placeholders = implode(',', array_fill(0, count($crns), '?'));
    $prereqRows = all_rows(
        $pdo,
        "SELECT
                cp.course_ID,
                cp.prerequisite_course_ID,
                cp.min_Grade_Req,
                pre.course_Name AS prereq_Name
            FROM Course_Prerequisite cp
            JOIN Course pre ON pre.course_ID = cp.prerequisite_course_ID
            WHERE cp.course_ID IN (
                SELECT c.course_ID FROM Course_Section cs
                JOIN Course c ON c.course_ID = cs.course_ID
                WHERE cs.CRN IN ($placeholders)
            )",
        $crns
    ) ?? [];
    // Build passed courses set for this student
    $passedCourses = [];
    $passedRows = all_rows(
        $pdo,
        "SELECT sh.course_ID, sh.Grade FROM Student_History sh WHERE sh.student_ID = ?",
        [$studentID]
    ) ?? [];
    foreach ($passedRows as $row) {
        $points = grade_points($row['Grade']);
        if ($points !== null) {
            $passedCourses[(string) $row['course_ID']] = $row['Grade'];
        }
    }
    // Group prereqs by course_ID
    $prereqByCourse = [];
    foreach ($prereqRows as $row) {
        $prereqByCourse[(string) $row['course_ID']][] = $row;
    }
    // Determine status per section
    foreach ($sections as $section) {
        $courseID = (string) $section['course_ID'];
        $prereqs = $prereqByCourse[$courseID] ?? [];
        $unmet = [];
        foreach ($prereqs as $prereq) {
            $passedGrade = $passedCourses[(string) $prereq['prerequisite_course_ID']] ?? null;
            $points = grade_points($passedGrade);
            $requiredPoints = grade_points($prereq['min_Grade_Req']);
            if ($points === null || ($requiredPoints !== null && $points < $requiredPoints)) {
                $unmet[] = $prereq;
            }
        }
        $sectionPrereqStatus[(int) $section['CRN']] = [
            'hasPrereqs' => count($prereqs) > 0,
            'unmet' => $unmet,
        ];
    }
}

// Time conflict check: registered course time slots for this semester.
$registeredTimeSlots = [];
if ($registrationNotice !== null && count($myRegisteredCourses) > 0) {
    $registeredCRNs = array_map(static fn($row) => (int) $row['CRN'], $myRegisteredCourses);
    $regPlaceholders = implode(',', array_fill(0, count($registeredCRNs), '?'));
    $registeredTimeSlots = all_rows(
        $pdo,
        "SELECT
                cs.CRN,
                cs.time_Slot_ID,
                tsd.day_ID,
                d.day_Of_Week,
                p.start_Time,
                p.end_Time
            FROM Course_Section cs
            JOIN Time_Slot_Day tsd ON tsd.time_Slot_ID = cs.time_Slot_ID
            JOIN Day d ON d.day_ID = tsd.day_ID
            JOIN Time_Slot_Period tsp ON tsp.time_Slot_ID = cs.time_Slot_ID
            JOIN Period p ON p.period_ID = tsp.period_ID
            WHERE cs.CRN IN ($regPlaceholders)
            ORDER BY d.day_ID, p.start_Time",
        $registeredCRNs
    ) ?? [];
}

// Waitlist status for each section.
$waitlistStatus = [];
if ($registrationNotice !== null && count($sections) > 0) {
    $crns = array_map(static fn($row) => (int) $row['CRN'], $sections);
    $placeholders = implode(',', array_fill(0, count($crns), '?'));
    $waitlistRows = all_rows(
        $pdo,
        "SELECT
                CRN,
                COUNT(*) AS waitlist_count,
                MAX(position) AS max_position
            FROM Waitlist
            WHERE CRN IN ($placeholders)
                AND semester_ID = ?
                AND enrolled_At IS NULL
            GROUP BY CRN",
        array_merge($crns, [(int) $registrationNotice])
    ) ?? [];
    foreach ($waitlistRows as $row) {
        $waitlistStatus[(int) $row['CRN']] = [
            'count' => (int) $row['waitlist_count'],
            'max_position' => (int) $row['max_position'],
        ];
    }
    // Check if student is already waitlisted for any section
    $studentWaitlist = all_rows(
        $pdo,
        'SELECT CRN FROM Waitlist WHERE student_ID = ? AND semester_ID = ? AND enrolled_At IS NULL',
        [$studentID, (int) $registrationNotice]
    ) ?? [];
    $studentWaitlistedCRNs = array_map(static fn($row) => (int) $row['CRN'], $studentWaitlist);
} else {
    $studentWaitlistedCRNs = [];
}

page_start('Course Registration', 'Student', 'registration.php');
flash();
require_once __DIR__ . '/../includes/demo_schedule_notice.php';
demo_schedule_notice((int) ($registrationNotice ?? 0));
require __DIR__ . '/partials/registration_view.php';
page_end();

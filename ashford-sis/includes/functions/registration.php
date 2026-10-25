<?php

declare(strict_types=1);

// Registration validation: add / drop / credit limits / prerequisites / holds / time conflicts.

require_once __DIR__ . '/windows.php';
require_once __DIR__ . '/../helpers/validation.php';

// Letter grades that count as "passed" for retake prevention.
const PASSING_GRADES = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D'];

// Full add-course validation. Returns [bool $ok, string|null $message].
function validate_add_course(
    PDO $pdo,
    int $studentID,
    int $crn
): array {
    $semesterID = current_registration_semester($pdo);
    if ($semesterID === null) {
        return [false, 'No active registration semester.'];
    }

    // 1. WINDOW
    [$open, $msg] = registration_window_status($pdo, $semesterID);
    if (!$open) {
        return [false, 'Registration blocked: ' . $msg];
    }

    // 2. SECTION EXISTS, NOT CANCELLED, IN SEMESTER
    $section = one($pdo,
        'SELECT cs.*, c.course_Name, c.course_Credits, c.course_Type
           FROM Course_Section cs
           JOIN Course c ON c.course_ID = cs.course_ID
          WHERE cs.CRN = ? AND cs.semester_ID = ?',
        [$crn, $semesterID]);
    if (!$section) {
        return [false, 'Course section not found.'];
    }
    if ((int) $section['is_Cancelled'] === 1) {
        return [false, 'This section has been cancelled.'];
    }

    // 3. STUDENT STATUS
    $student = one($pdo,
        'SELECT student_Type, enrollment_Status FROM Student WHERE student_ID = ?',
        [$studentID]);
    if (!$student) {
        return [false, 'Student record not found.'];
    }

    // 4. HOLDS
    $hold = one($pdo,
        'SELECT 1 FROM Student_Hold WHERE student_ID = ? LIMIT 1',
        [$studentID]);
    if ($hold) {
        return [false, 'Registration blocked: you have a hold.'];
    }

    // 5. CROSS-LEVEL
    if ($student['student_Type'] !== $section['course_Type']) {
        $want = $section['course_Type'] === 'Undergraduate' ? 'undergraduate' : 'graduate';
        return [false, "Registration blocked: {$want} courses are not open to {$student['student_Type']} students."];
    }

    // 6. DUPLICATE SECTION
    $already = one($pdo,
        'SELECT 1 FROM Enrollment WHERE student_ID = ? AND CRN = ?',
        [$studentID, $crn]);
    if ($already) {
        return [false, 'You are already registered for this section.'];
    }

    // 7. ALREADY PASSED (any section, any term)
    $passedGrades = "'" . implode("', '", PASSING_GRADES) . "'";
    $passed = one($pdo,
        "SELECT 1 FROM Enrollment e
           JOIN Course_Section cs2 ON cs2.CRN = e.CRN
          WHERE e.student_ID = ? AND cs2.course_ID = ?
            AND e.Grade IN ($passedGrades)
          LIMIT 1",
        [$studentID, $section['course_ID']]);
    if ($passed) {
        return [false, 'Registration blocked: you already passed this course.'];
    }

    // 8. SEATS
    if ((int) $section['available_Seats'] <= 0) {
        return [false, 'Registration blocked: no seats are available.'];
    }

    // 9. TIME CONFLICT
    $conflict = one($pdo,
        'SELECT 1 FROM Enrollment e
           JOIN Course_Section cs2 ON cs2.CRN = e.CRN
           JOIN Time_Slot_Day tsd_a ON tsd_a.time_Slot_ID = cs.time_Slot_ID
           JOIN Time_Slot_Day tsd_b ON tsd_b.time_Slot_ID = cs2.time_Slot_ID
                                      AND tsd_b.day_ID = tsd_a.day_ID
           JOIN Time_Slot_Period tsp_a ON tsp_a.time_Slot_ID = cs.time_Slot_ID
           JOIN Period p_a ON p_a.period_ID = tsp_a.period_ID
           JOIN Time_Slot_Period tsp_b ON tsp_b.time_Slot_ID = cs2.time_Slot_ID
           JOIN Period p_b ON p_b.period_ID = tsp_b.period_ID
          WHERE e.student_ID = ? AND e.CRN != ?
            AND p_a.start_Time < p_b.end_Time
            AND p_b.start_Time < p_a.end_Time
          LIMIT 1',
        [$section['time_Slot_ID'], $section['time_Slot_ID'], $studentID, $crn]);
    if ($conflict) {
        return [false, 'Registration blocked: time conflict with an existing course.'];
    }

    // 10. CREDIT LIMIT (in-progress credits only)
    $maxCredits = ($student['enrollment_Status'] === 'Full-Time') ? 16 : 8;
    $current = (int) (one($pdo,
        'SELECT COALESCE(SUM(c.course_Credits), 0) AS total
           FROM Enrollment e
           JOIN Course_Section cs2 ON cs2.CRN = e.CRN
           JOIN Course c ON c.course_ID = cs2.course_ID
          WHERE e.student_ID = ? AND e.semester_ID = ? AND e.Grade IS NULL',
        [$studentID, $semesterID])['total'] ?? 0);

    if ($current + (int) $section['course_Credits'] > $maxCredits) {
        return [false, "Registration blocked: this would exceed your {$maxCredits}-credit limit."];
    }

    return [true, null];
}

// Persist an approved add inside a transaction with row lock.
function commit_add_course(PDO $pdo, int $studentID, int $crn, int $semesterID): array
{
    try {
        $pdo->beginTransaction();

        $locked = one($pdo,
            'SELECT semester_ID, available_Seats FROM Course_Section WHERE CRN = ? FOR UPDATE',
            [$crn]);
        if (!$locked || (int) $locked['available_Seats'] <= 0) {
            $pdo->rollBack();
            return [false, 'This course section is no longer available.'];
        }

        $pdo->prepare(
            'INSERT INTO Enrollment (student_ID, CRN, semester_ID) VALUES (?, ?, ?)'
        )->execute([$studentID, $crn, $semesterID]);

        $pdo->prepare(
            'UPDATE Course_Section SET available_Seats = available_Seats - 1
               WHERE CRN = ? AND available_Seats > 0'
        )->execute([$crn]);

        $pdo->prepare(
            'INSERT INTO Audit_Log (actor_User_ID, action, entity_Type, entity_ID, details)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$studentID, 'Create', 'Enrollment', (string) $crn, 'Student registration']);

        $pdo->commit();

        return [true, 'Course registration successful.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return [false, 'Registration could not be completed.'];
    }
}

// Drop validation: window check + ownership.
function validate_drop_course(PDO $pdo, int $studentID, int $crn): array
{
    $enrollment = one($pdo,
        'SELECT e.semester_ID FROM Enrollment e WHERE e.student_ID = ? AND e.CRN = ?',
        [$studentID, $crn]);
    if (!$enrollment) {
        return [false, 'You are not registered for this section.'];
    }

    [$open, $msg] = registration_window_status($pdo, (int) $enrollment['semester_ID']);
    if (!$open) {
        return [false, 'Drop blocked: ' . $msg];
    }

    return [true, null];
}

function commit_drop_course(PDO $pdo, int $studentID, int $crn): array
{
    try {
        $pdo->beginTransaction();

        $row = one($pdo,
            'SELECT semester_ID FROM Enrollment WHERE student_ID = ? AND CRN = ?',
            [$studentID, $crn]);
        if (!$row) {
            $pdo->rollBack();
            return [false, 'Enrollment not found.'];
        }

        $pdo->prepare('DELETE FROM Enrollment WHERE student_ID = ? AND CRN = ?')
            ->execute([$studentID, $crn]);

        $pdo->prepare('UPDATE Course_Section SET available_Seats = available_Seats + 1 WHERE CRN = ?')
            ->execute([$crn]);

        $pdo->prepare(
            'INSERT INTO Audit_Log (actor_User_ID, action, entity_Type, entity_ID, details)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$studentID, 'Delete', 'Enrollment', (string) $crn, 'Student drop']);

        $pdo->commit();

        return [true, 'Course dropped successfully.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return [false, 'Drop could not be completed.'];
    }
}

// -----------------------------------------------------
// Major / Minor declaration validation
// -----------------------------------------------------

function declare_major(PDO $pdo, int $studentID, int $majorID): array
{
    $count = (int) (one($pdo,
        'SELECT COUNT(*) AS c FROM Student_Major WHERE student_ID = ?',
        [$studentID])['c'] ?? 0);

    if ($count >= 2) {
        return [false, 'Maximum of 2 majors reached.'];
    }

    $exists = one($pdo,
        'SELECT 1 FROM Student_Major WHERE student_ID = ? AND major_ID = ?',
        [$studentID, $majorID]);
    if ($exists) {
        return [false, 'You already declared this major.'];
    }

    try {
        $pdo->beginTransaction();
        $pdo->prepare(
            'INSERT INTO Student_Major (student_ID, major_ID, date_Of_Choice) VALUES (?, ?, CURDATE())'
        )->execute([$studentID, $majorID]);
        $pdo->prepare(
            'INSERT INTO Audit_Log (actor_User_ID, action, entity_Type, entity_ID, details)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$studentID, 'Create', 'Student_Major', (string) $majorID, 'Major declared']);
        $pdo->commit();
        return [true, 'Major declared.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return [false, 'Major declaration failed.'];
    }
}

function declare_minor(PDO $pdo, int $studentID, int $minorID): array
{
    $majorCount = (int) (one($pdo,
        'SELECT COUNT(*) AS c FROM Student_Major WHERE student_ID = ?',
        [$studentID])['c'] ?? 0);
    if ($majorCount < 1) {
        return [false, 'You must declare a major before declaring a minor.'];
    }

    $minorCount = (int) (one($pdo,
        'SELECT COUNT(*) AS c FROM Student_Minor WHERE student_ID = ?',
        [$studentID])['c'] ?? 0);
    if ($minorCount >= 1) {
        return [false, 'Maximum of 1 minor reached.'];
    }

    $total = $majorCount + $minorCount;
    if ($total + 1 > 2) {
        return [false, 'Total combined majors and minors cannot exceed two.'];
    }

    $exists = one($pdo,
        'SELECT 1 FROM Student_Minor WHERE student_ID = ? AND minor_ID = ?',
        [$studentID, $minorID]);
    if ($exists) {
        return [false, 'You already declared this minor.'];
    }

    try {
        $pdo->beginTransaction();
        $pdo->prepare(
            'INSERT INTO Student_Minor (student_ID, minor_ID, date_Of_Choice) VALUES (?, ?, CURDATE())'
        )->execute([$studentID, $minorID]);
        $pdo->prepare(
            'INSERT INTO Audit_Log (actor_User_ID, action, entity_Type, entity_ID, details)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$studentID, 'Create', 'Student_Minor', (string) $minorID, 'Minor declared']);
        $pdo->commit();
        return [true, 'Minor declared.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return [false, 'Minor declaration failed.'];
    }
}

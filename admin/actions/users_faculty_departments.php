<?php
declare(strict_types=1);

// Included from admin/users.php after authentication and CSRF verification.

if (!$isFullAccess && !$isLockManager) {
    $_SESSION['users_error'] = 'You cannot manage faculty departments.';
    return;
}

$facultyID = post_int('faculty_id') ?? 0;
$departmentID = post_int('department_id') ?? 0;

if ($facultyID <= 0 || $departmentID <= 0) {
    $_SESSION['users_error'] = 'Invalid faculty or department ID.';
    return;
}

try {
    $pdo->beginTransaction();

    // Lock faculty record to serialize concurrent department updates.
    $faculty = one(
        $pdo,
        'SELECT faculty_ID FROM Faculty WHERE faculty_ID = ? FOR UPDATE',
        [$facultyID]
    );

    if (!$faculty) {
        throw new RuntimeException('Faculty member not found.');
    }

    $department = one(
        $pdo,
        'SELECT dept_ID FROM Department WHERE dept_ID = ?',
        [$departmentID]
    );

    if (!$department) {
        throw new RuntimeException('Department not found.');
    }

    $stmt = $pdo->prepare(
        'SELECT dept_ID
         FROM Faculty_Department
         WHERE faculty_ID = ?
         ORDER BY dept_ID FOR UPDATE'
    );
    $stmt->execute([$facultyID]);

    $existing = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    $exists = in_array($departmentID, $existing, true);

    if ($action === 'faculty_dept_add') {
        if ($exists) {
            throw new RuntimeException('Department already assigned.');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO Faculty_Department
             (faculty_ID, dept_ID, percent_Time, date_Of_Appointment)
             VALUES (?, ?, 0, CURDATE())'
        );
        $stmt->execute([$facultyID, $departmentID]);

    } elseif ($action === 'faculty_dept_remove') {
        if (!$exists) {
            throw new RuntimeException('Department assignment not found.');
        }

        if (count($existing) <= 1) {
            throw new RuntimeException(
                'A faculty member must retain at least one department.'
            );
        }

        // Do not remove a department while the faculty member is
        // assigned to course sections in that department.
        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM Course_Section cs
             JOIN Course c ON c.course_ID = cs.course_ID
             JOIN Semester sem ON sem.semester_ID = cs.semester_ID
             WHERE cs.faculty_ID = ?
               AND c.dept_ID = ?
               AND (
                   sem.semester_Start IS NULL
                   OR sem.semester_Start >= CURDATE()
                   OR sem.semester_ID = (
                       SELECT s2.semester_ID
                       FROM Semester s2
                       WHERE s2.semester_Start <= CURDATE()
                       ORDER BY s2.semester_Start DESC, s2.semester_ID DESC
                       LIMIT 1
                   )
               )'
        );
        $stmt->execute([$facultyID, $departmentID]);

        if ((int)$stmt->fetchColumn() > 0) {
            throw new RuntimeException(
                'Cannot remove a department with existing teaching assignments.'
            );
        }

        $stmt = $pdo->prepare(
            'DELETE FROM Faculty_Department
             WHERE faculty_ID = ? AND dept_ID = ?'
        );
        $stmt->execute([$facultyID, $departmentID]);
    }

    // Recalculate to exactly 100.00%, including rounding remainder.
    $stmt = $pdo->prepare(
        'SELECT dept_ID FROM Faculty_Department
         WHERE faculty_ID = ? ORDER BY dept_ID'
    );
    $stmt->execute([$facultyID]);

    $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    $count = count($ids);

    if ($count === 0) {
        throw new RuntimeException('Faculty must have a department.');
    }

    $base = intdiv(10000, $count);
    $remainder = 10000 % $count;

    $update = $pdo->prepare(
        'UPDATE Faculty_Department
         SET percent_Time = ?
         WHERE faculty_ID = ? AND dept_ID = ?'
    );

    foreach ($ids as $index => $id) {
        $hundredths = $base + ($index < $remainder ? 1 : 0);
        $percentage = number_format($hundredths / 100, 2, '.', '');
        $update->execute([$percentage, $facultyID, $id]);
    }

    $pdo->commit();

    $_SESSION['users_save_result'] = [
        'type' => 'success',
        'message' => $action === 'faculty_dept_add'
            ? 'Department added successfully.'
            : 'Department removed successfully.'
    ];

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Faculty department management: ' . $e->getMessage());

    // ASHFORD_FACULTY_FRIENDLY_ERRORS_V1
    $errorMessage = $e->getMessage();

    if (
        str_contains($errorMessage, 'Faculty may belong to at most 3 departments')
    ) {
        $errorMessage = 'Department limit reached. A faculty member can be assigned to a maximum of 3 departments. Remove an existing department before adding another.';
    }

    $_SESSION['users_save_result'] = [
        'type' => 'error',
        'message' => str_contains(
            $e->getMessage(),
            'Faculty may belong to at most 3 departments'
        )
            ? $errorMessage
            : ($e instanceof RuntimeException
                ? $e->getMessage()
                : 'Unable to update faculty departments.')
    ];
}

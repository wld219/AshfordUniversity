<?php

declare(strict_types=1);

// Cancel a course section. Uses $pdo, $adminID, $error from the entry point.
// Authorized administrators can cancel regardless of enrollment or semester date.
// Enrollment records remain intact; cancellation is recorded and notifications attempted.

require_write_access();

$crn = post_int('crn') ?? 0;
if ($crn <= 0) {
    $error = 'Invalid course section.';
    return;
}

$reason = post_str('reason', 255);
if ($reason === '') {
    $error = 'Please provide a reason for cancelling this section.';
    return;
}

try {
    $pdo->beginTransaction();

    $section = one(
        $pdo,
        'SELECT CRN, semester_ID, is_Cancelled FROM Course_Section WHERE CRN = ? FOR UPDATE',
        [$crn]
    );

    if (!$section) {
        $pdo->rollBack();
        $error = 'Section not found.';
        return;
    }

    if ((int) $section['is_Cancelled'] === 1) {
        $pdo->rollBack();
        $error = 'This section is already cancelled.';
        return;
    }

    $enrolled = section_enrolled_count($pdo, $crn);

    $pdo->prepare('UPDATE Course_Section SET is_Cancelled = 1 WHERE CRN = ?')
        ->execute([$crn]);

    $pdo->prepare(
        'INSERT INTO Section_Cancellation (CRN, enrolled_Count, reason, cancelled_By_User_ID)
         VALUES (?, ?, ?, ?)'
    )->execute([$crn, $enrolled, $reason, $adminID]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Section cancellation failed: ' . $e->getMessage());
    $error = 'The section could not be cancelled.';
    return;
}

try {
    audit_log(
        $pdo,
        'Cancel',
        'Course_Section',
        (string) $crn,
        "Section cancelled; {$enrolled} enrolled; Reason: {$reason}"
    );
} catch (Throwable $e) {
    error_log('Section cancellation audit failed: ' . $e->getMessage());
}

try {
    if (master_schedule_get($pdo, (int) $section['semester_ID']) !== null) {
        master_schedule_touch($pdo, (int) $section['semester_ID'], $adminID);
    }
} catch (Throwable $e) {
    error_log('Section cancellation schedule update failed: ' . $e->getMessage());
}

$notified = 0;
try {
    $notified = notify_section_cancellation($pdo, $crn);
} catch (Throwable $e) {
    error_log('Section cancellation notification failed: ' . $e->getMessage());
}

redirect_with_flash(
    url('admin/sections.php'),
    'success',
    "Section cancelled successfully. {$enrolled} enrollment record(s) preserved. {$notified} notification(s) reported sent."
);

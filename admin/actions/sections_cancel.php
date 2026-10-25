<?php

declare(strict_types=1);

// Cancel a section.
//
// Rules:
//   - the semester must not have started,
//   - fewer than 10 students may be enrolled,
//   - enrolled students are notified by email,
//   - enrollment records are preserved (the section is flagged, not deleted).
//
// Uses $pdo, $adminID, $error from the entry point.

$crn = post_int('crn') ?? 0;

$section = one(
	$pdo,
	"SELECT cs.*, s.semester_Start
	FROM Course_Section cs
	JOIN Semester s ON s.semester_ID = cs.semester_ID
	WHERE cs.CRN = ?",
	[$crn]
);

if (!$section) {
	$error = 'Section not found.';
	return;
}

if ((int) $section['is_Cancelled'] === 1) {
	$error = 'This section is already cancelled.';
	return;
}

if (
	$section['semester_Start'] !== null &&
	new DateTimeImmutable('now') >= new DateTimeImmutable((string) $section['semester_Start'])
) {
	$error = 'A section may only be cancelled before the semester starts.';
	return;
}

if (section_enrolled_count($pdo, $crn) >= 10) {
	$error = 'A section with 10 or more students enrolled cannot be cancelled.';
	return;
}

$reason = post_str('reason', 255) ?: 'Cancelled by administration';

try {

	$pdo->beginTransaction();

	$enrolled = section_enrolled_count($pdo, $crn);

	$pdo->prepare('UPDATE Course_Section SET is_Cancelled = 1 WHERE CRN = ?')
		->execute([$crn]);

	$pdo->prepare(
		'INSERT INTO Section_Cancellation
			(CRN, enrolled_Count, reason, cancelled_By_User_ID)
		VALUES (?, ?, ?, ?)'
	)->execute([$crn, $enrolled, $reason, $adminID]);

	$pdo->commit();

	// Notify affected students (email; log-only in development)
	$notified = notify_section_cancellation($pdo, $crn);

	audit_log(
		$pdo,
		'Cancel',
		'Course_Section',
		(string) $crn,
		"Section cancelled; {$notified} notification(s) sent"
	);

	if (master_schedule_get($pdo, (int) $section['semester_ID']) !== null) {
		master_schedule_touch($pdo, (int) $section['semester_ID'], $adminID);
	}

	redirect_with_flash(
		url('admin/sections.php'),
		'success',
		"Section cancelled; {$notified} enrolled student(s) notified."
	);

} catch (Throwable $e) {

	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}

	error_log('Section cancellation failed: ' . $e->getMessage());

	$error = 'The section could not be cancelled.';
}

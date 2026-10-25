<?php

declare(strict_types=1);

// Holds action: add or remove a student hold.
// Uses $pdo, $error from the entry point.

$action = post_str('action', 20);

if ($action === 'add_hold') {

	$studentID = post_int('student_id') ?? 0;
	$holdID = post_int('hold_id') ?? 0;
	$holdDate = post_date('hold_date') ?? date('Y-m-d');

	$student = one(
		$pdo,
		"SELECT student_ID FROM Student WHERE student_ID = ?
		AND student_ID IN (SELECT user_ID FROM User WHERE user_Type = 'Student')",
		[$studentID]
	);

	$hold = one(
		$pdo,
		'SELECT hold_ID FROM Hold WHERE hold_ID = ?',
		[$holdID]
	);

	if (!$student || !$hold) {
		$error = 'The student and hold type must both exist.';
		return;
	}

	try {
		$pdo->prepare(
			'INSERT INTO Student_Hold (hold_ID, student_ID, hold_Date) VALUES (?, ?, ?)'
		)->execute([$holdID, $studentID, $holdDate]);

		audit_log($pdo, 'Create', 'Student_Hold', (string) $studentID, "Hold {$holdID} added");

		redirect_with_flash(url('admin/holds.php'), 'success', 'Hold added.');

	} catch (Throwable $e) {
		$error = 'That hold is already on the student record.';
	}
}

if ($action === 'remove_hold') {

	$studentID = post_int('student_id') ?? 0;
	$holdID = post_int('hold_id') ?? 0;

	$pdo->prepare('DELETE FROM Student_Hold WHERE hold_ID = ? AND student_ID = ?')
		->execute([$holdID, $studentID]);

	audit_log($pdo, 'Delete', 'Student_Hold', (string) $studentID, "Hold {$holdID} removed");

	redirect_with_flash(url('admin/holds.php'), 'success', 'Hold removed.');
}

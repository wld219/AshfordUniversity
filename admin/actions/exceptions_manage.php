<?php

declare(strict_types=1);

// Grade exception action: approve or remove.
// Uses $pdo, $adminID, $error from the entry point.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

$action = post_str('action', 20);

if ($action === 'approve') {

	$studentID = post_int('student_id') ?? 0;
	$crn = post_int('crn') ?? 0;
	$reason = post_str('reason', 255);

	$enrollment = one(
		$pdo,
		'SELECT 1 FROM Enrollment WHERE student_ID = ? AND CRN = ?',
		[$studentID, $crn]
	);

	if (!$enrollment) {
		$error = 'There is no enrollment for that student and section.';
		return;
	}

	if ($reason === '') {
		$error = 'A reason is required for a grade exception.';
		return;
	}

	$existing = one(
		$pdo,
		'SELECT 1 FROM Grade_Exception WHERE student_ID = ? AND CRN = ?',
		[$studentID, $crn]
	);

	if ($existing) {
		$error = 'A grade exception already exists for that enrollment.';
		return;
	}

	$pdo->prepare(
		'INSERT INTO Grade_Exception
			(student_ID, CRN, reason, approved_By_User_ID)
		VALUES (?, ?, ?, ?)'
	)->execute([$studentID, $crn, $reason, $adminID]);

	audit_log(
		$pdo,
		'Approve',
		'Grade_Exception',
		(string) $studentID,
		"Grade exception approved for CRN {$crn}: {$reason}"
	);

	redirect_with_flash(
		url('admin/exceptions.php'),
		'success',
		'Grade exception recorded.'
	);
}

if ($action === 'remove') {

	$exceptionID = post_int('exception_id') ?? 0;

	$pdo->prepare('DELETE FROM Grade_Exception WHERE exception_ID = ?')
		->execute([$exceptionID]);

	audit_log($pdo, 'Delete', 'Grade_Exception', (string) $exceptionID, 'Grade exception removed');

	redirect_with_flash(url('admin/exceptions.php'), 'success', 'Grade exception removed.');
}

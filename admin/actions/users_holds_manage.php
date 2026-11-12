<?php

declare(strict_types=1);

// Holds action: add or remove a student hold.
// Uses $pdo, $error from the entry point.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

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

		// Notify the student only after the hold is successfully saved.
		try {
			require_once dirname(__DIR__, 2) . '/includes/mail.php';
			require_once dirname(__DIR__, 2) . '/includes/functions/notifications.php';

			$holdDetails = one(
				$pdo,
				'SELECT hold_Type FROM Hold WHERE hold_ID = ?',
				[$holdID]
			);

			$holdType = (string) ($holdDetails['hold_Type'] ?? 'Student hold');

			$subject = 'Ashford University - Hold Added';

			$message = "A hold has been placed on your student account.\n\n"
				. "Hold Type: {$holdType}\n"
				. "Date: {$holdDate}\n\n"
				. "Please sign in to your Student Portal to review your hold.\n"
				. "Contact the appropriate university office if you need assistance.\n\n"
				. "Ashford University";

			$senderID = (int) ($_SESSION['user_id'] ?? 0);

			if ($senderID > 0) {
				send_student_notification(
					$pdo,
					$senderID,
					$studentID,
					$subject,
					$message
				);
			}
		} catch (Throwable $notificationError) {
			error_log(
				'Hold notification failed: ' . $notificationError->getMessage()
			);
		}

		redirect_with_flash(url('admin/users.php?view=' . $studentID), 'success', 'Hold added.');

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

	redirect_with_flash(url('admin/users.php?view=' . $studentID), 'success', 'Hold removed.');
}

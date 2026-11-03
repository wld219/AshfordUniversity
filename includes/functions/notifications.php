<?php

declare(strict_types=1);

// -----------------------------------------------------
// Section cancellation notifications.
// -----------------------------------------------------

// Notify affected students by email after a section cancellation.
// Logs each notification to the Notification table.
// Returns the number of notification emails accepted.
function notify_section_cancellation(PDO $pdo, int $crn): int
{
	$students = all_rows(
		$pdo,
		"SELECT DISTINCT u.first_Name, u.last_Name, l.user_Email, u.user_ID
		FROM Enrollment e
		JOIN User u ON u.user_ID = e.student_ID
		JOIN Login l ON l.user_ID = e.student_ID
		WHERE e.CRN = ?",
		[$crn]
	);

	$section = one(
		$pdo,
		"SELECT cs.CRN, c.course_ID, c.course_Name, s.semester_Name
		FROM Course_Section cs
		JOIN Course c ON c.course_ID = cs.course_ID
		JOIN Semester s ON s.semester_ID = cs.semester_ID
		WHERE cs.CRN = ?",
		[$crn]
	);

	if (!$section) {
		return 0;
	}

	$sent = 0;

	foreach ($students as $student) {

		$name = trim((string) $student['first_Name'] . ' ' . (string) $student['last_Name']);

		$body = "Hello {$name},\n\n"
			. "Your section {$section['course_ID']} - {$section['course_Name']} "
			. "(CRN {$section['CRN']}) for {$section['semester_Name']} has been cancelled "
			. "by the university. Your enrollment record is preserved, and you should "
			. "register for another section before the registration deadline.\n\n"
			. "-- Ashford University";

		// Log to Notification table
		$pdo->prepare(
			"INSERT INTO Notification (sender_user_ID, recipient_user_ID, subject, message)
			 VALUES (?, ?, ?, ?)"
		)->execute([
			0, // system sender
			(int) $student['user_ID'],
			'Ashford University course section cancelled',
			$body
		]);

		try {

			if (!function_exists('send_generic_email')) {
				break;
			}

			send_generic_email(
				(string) $student['user_Email'],
				$name,
				'Ashford University course section cancelled',
				$body
			);

			$sent++;

		} catch (Throwable $e) {
			error_log('Cancellation notification failed: ' . $e->getMessage());
		}
	}

	return $sent;
}

// -----------------------------------------------------
// General student notification (faculty -> student).
// -----------------------------------------------------

// Send a notification from a faculty member to a specific student.
// Logs to the Notification table and attempts email delivery.
// Returns true if the notification was logged.
function send_student_notification(
	PDO $pdo,
	int $senderUserID,
	int $recipientStudentID,
	string $subject,
	string $message
): bool {
	try {
		$pdo->prepare(
			"INSERT INTO Notification (sender_user_ID, recipient_user_ID, subject, message)
			 VALUES (?, ?, ?, ?)"
		)->execute([
			$senderUserID,
			$recipientStudentID,
			$subject,
			$message
		]);

		// Attempt email delivery if available
		$recipient = one(
			$pdo,
			"SELECT l.user_Email, u.first_Name, u.last_Name
			 FROM User u
			 JOIN Login l ON l.user_ID = u.user_ID
			 WHERE u.user_ID = ?",
			[$recipientStudentID]
		);

		if ($recipient && function_exists('send_generic_email')) {
			$name = trim((string) $recipient['first_Name'] . ' ' . (string) $recipient['last_Name']);
			try {
				send_generic_email(
					(string) $recipient['user_Email'],
					$name,
					$subject,
					$message
				);
			} catch (Throwable $e) {
				error_log('Student notification email failed: ' . $e->getMessage());
			}
		}

		return true;
	} catch (Throwable $e) {
		error_log('Student notification failed: ' . $e->getMessage());
		return false;
	}
}

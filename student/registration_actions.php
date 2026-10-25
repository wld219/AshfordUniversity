<?php

declare(strict_types=1);

// Registration action: check every rule, then register with a row lock so
// two students cannot claim the same last seat.
// Uses $pdo, $studentID from the entry point.

$canRegister = false;

if ($crn > 0) {

	[$canRegister, $message] = registration_check(
		$pdo,
		$studentID,
		$crn
	);

	if ($canRegister) {

		try {

			$pdo->beginTransaction();

			// Lock the section row so two students cannot claim the
			// last remaining seat at the same time.
			$section = one(
				$pdo,
				"SELECT semester_ID, available_Seats
				FROM Course_Section
				WHERE CRN = ?
				FOR UPDATE",
				[$crn]
			);

			if (!$section) {

				$pdo->rollBack();

				$_SESSION['flash'] = [
					'type' => 'error',
					'message' => 'This course section no longer exists.'
				];

			} elseif ((int) $section['available_Seats'] <= 0) {

				$pdo->rollBack();

				$_SESSION['flash'] = [
					'type' => 'error',
					'message' => 'This course section is full.'
				];

			} else {

				// Add the student to the course
				$pdo->prepare(
					"INSERT INTO Enrollment
						(student_ID, CRN, semester_ID)
					VALUES (?, ?, ?)"
				)->execute([
					$studentID,
					$crn,
					$section['semester_ID']
				]);

				// Reduce the number of available seats
				$pdo->prepare(
					"UPDATE Course_Section
					SET available_Seats = available_Seats - 1
					WHERE CRN = ?
					AND available_Seats > 0"
				)->execute([$crn]);

				$pdo->commit();

				audit_log(
					$pdo,
					'Create',
					'Enrollment',
					(string) $crn,
					'Student registration'
				);

				$_SESSION['flash'] = [
					'type' => 'success',
					'message' => 'Course registration successful.'
				];
			}

		} catch (Throwable $error) {

			if ($pdo->inTransaction()) {
				$pdo->rollBack();
			}

			error_log('Registration failed: ' . $error->getMessage());

			$_SESSION['flash'] = [
				'type' => 'error',
				'message' => 'Registration could not be completed.'
			];
		}

	} else {

		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => $message
		];
	}
}

header('Location: registration.php');
exit;

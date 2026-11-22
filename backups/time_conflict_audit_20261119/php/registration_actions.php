<?php

declare(strict_types=1);

// Registration action: check every rule, then register with a row lock so
// two students cannot claim the same last seat.
// Uses $pdo, $studentID from the entry point.

// ASHFORD_ADD_DROP_V1: existing forms default to Add for compatibility.
$registrationAction = (string) ($_POST['registration_action'] ?? 'add');
if (!in_array($registrationAction, ['add', 'drop', 'waitlist'], true)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid registration action.'];
    header('Location: registration.php');
    exit;
}
if ($registrationAction === 'drop') {
    try {
        if ($crn <= 0) {
            throw new RuntimeException('Please select a valid course section.');
        }
        $pdo->beginTransaction();
        // Lock section first, matching the Add transaction's lock ordering.
        $section = one($pdo,
            'SELECT semester_ID FROM Course_Section WHERE CRN = ? FOR UPDATE',
            [$crn]);
        if (!$section) {
            throw new RuntimeException('Course section not found.');
        }
        $semesterID = (int) $section['semester_ID'];
        [$dropOpen, $dropMessage] = drop_window_status($pdo, $semesterID);
        if (!$dropOpen) {
            throw new RuntimeException($dropMessage);
        }
        $enrollment = one($pdo,
            'SELECT Grade FROM Enrollment
             WHERE student_ID = ? AND CRN = ? AND semester_ID = ? FOR UPDATE',
            [$studentID, $crn, $semesterID]);
        if (!$enrollment) {
            throw new RuntimeException('You are not enrolled in this course section.');
        }
        if ($enrollment['Grade'] !== null && trim((string) $enrollment['Grade']) !== '') {
            throw new RuntimeException('A course with a recorded grade cannot be dropped here.');
        }
        $history = one($pdo,
            'SELECT 1 FROM Student_History WHERE student_ID = ? AND CRN = ? LIMIT 1',
            [$studentID, $crn]);
        if ($history) {
            throw new RuntimeException('This section is linked to completed academic history and cannot be dropped here.');
        }
        $delete = $pdo->prepare(
            'DELETE FROM Enrollment WHERE student_ID = ? AND CRN = ? AND semester_ID = ?'
        );
        $delete->execute([$studentID, $crn, $semesterID]);
        if ($delete->rowCount() !== 1) {
            throw new RuntimeException('The enrollment could not be removed.');
        }
        $capacityRow = one($pdo,
            'SELECT COALESCE(l.avail_Seats, b.no_Of_Workstations) AS capacity
             FROM Course_Section cs
             LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
             LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID
             WHERE cs.CRN = ? AND cs.semester_ID = ?',
            [$crn, $semesterID]);
        $remainingRow = one($pdo,
            'SELECT COUNT(*) AS enrolled FROM Enrollment WHERE CRN = ? AND semester_ID = ?',
            [$crn, $semesterID]);
        $availableSeats = $capacityRow && $capacityRow['capacity'] !== null
            ? max(0, (int)$capacityRow['capacity'] - (int)($remainingRow['enrolled'] ?? 0))
            : 0;
        $pdo->prepare(
            'UPDATE Course_Section SET available_Seats = ? WHERE CRN = ? AND semester_ID = ?'
        )->execute([$availableSeats, $crn, $semesterID]);
        $pdo->commit();
        try {
            audit_log($pdo, 'Delete', 'Enrollment', (string) $crn, 'Student course drop');
        } catch (Throwable $auditError) {
            error_log('Drop audit failed: ' . $auditError->getMessage());
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Course dropped successfully.'];
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Drop request: ' . $error->getMessage());
        $_SESSION['flash'] = ['type' => 'error', 'message' => $error instanceof RuntimeException
            ? $error->getMessage() : 'Course drop could not be completed.'];
    }
    header('Location: registration.php');
    exit;
}

if ($registrationAction === 'waitlist') {
    try {
        if ($crn <= 0) {
            throw new RuntimeException('Please select a valid course section.');
        }
        $section = one($pdo,
            'SELECT semester_ID, available_Seats FROM Course_Section WHERE CRN = ?',
            [$crn]);
        if (!$section) {
            throw new RuntimeException('Course section not found.');
        }
        $semesterID = (int) $section['semester_ID'];
        if ((int) $section['available_Seats'] > 0) {
            throw new RuntimeException('This section still has open seats. Register instead of waitlisting.');
        }
        // Check already registered
        $already = one($pdo,
            'SELECT 1 FROM Enrollment WHERE student_ID = ? AND CRN = ? AND semester_ID = ?',
            [$studentID, $crn, $semesterID]);
        if ($already) {
            throw new RuntimeException('You are already registered for this section.');
        }
        // Check already waitlisted
        $alreadyWaitlisted = one($pdo,
            'SELECT 1 FROM Waitlist WHERE student_ID = ? AND CRN = ? AND semester_ID = ? AND enrolled_At IS NULL',
            [$studentID, $crn, $semesterID]);
        if ($alreadyWaitlisted) {
            throw new RuntimeException('You are already on the waitlist for this section.');
        }
        // Get next position
        $maxPos = one($pdo,
            'SELECT COALESCE(MAX(position), 0) AS maxPos FROM Waitlist WHERE CRN = ? AND semester_ID = ? AND enrolled_At IS NULL',
            [$crn, $semesterID]);
        $nextPos = (int) ($maxPos['maxPos'] ?? 0) + 1;
        $pdo->prepare(
            'INSERT INTO Waitlist (student_ID, CRN, semester_ID, position)
             VALUES (?, ?, ?, ?)'
        )->execute([$studentID, $crn, $semesterID, $nextPos]);
        audit_log($pdo, 'Create', 'Waitlist', (string) $crn, 'Student waitlisted for section');
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'You have been added to the waitlist. You will be notified if a seat opens.'];
    } catch (Throwable $error) {
        error_log('Waitlist request: ' . $error->getMessage());
        $_SESSION['flash'] = ['type' => 'error', 'message' => $error instanceof RuntimeException
            ? $error->getMessage() : 'Could not add to waitlist.'];
    }
    header('Location: registration.php');
    exit;
}

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

<?php

declare(strict_types=1);

// Major/minor actions. Uses $pdo, $studentID from the entry point.
// Every branch validates its input against the specification rules and
// redirects with a flash message.

$action = post_str('action', 30);

// Current declared majors
$currentMajors = all_rows(
	$pdo,
	'SELECT major_ID FROM Student_Major WHERE student_ID = ?',
	[$studentID]
);

	$currentMajorIDs = array_map(
		static fn(array $row): int => (int) $row['major_ID'],
		$currentMajors
	);

	$currentMinor = one(
		$pdo,
		'SELECT minor_ID FROM Student_Minor WHERE student_ID = ? LIMIT 1',
		[$studentID]
	);

	// Combined majors + minors must never exceed two.
	$currentTotal = count($currentMajorIDs) + (int) ($currentMinor !== null);

	if ($action === 'add_major') {

		$majorID = post_int('major_id') ?? 0;

		if (in_array($majorID, $currentMajorIDs, true)) {
			redirect_with_flash(
				url('student/programs.php'),
				'error',
				'You have already declared that major.'
			);
		}

		if (count($currentMajorIDs) >= 2) {
			redirect_with_flash(
				url('student/programs.php'),
				'error',
				'You may declare at most two majors. Remove one before adding another.'
			);
		}

		if ($currentTotal >= 2) {
			redirect_with_flash(
				url('student/programs.php'),
				'error',
				'Cannot add major — would exceed the combined limit of two majors and minors.'
			);
		}

		$major = one(
			$pdo,
			'SELECT major_ID, dept_ID FROM Major WHERE major_ID = ?',
			[$majorID]
		);

		if (!$major) {
			redirect_with_flash(
				url('student/programs.php'),
				'error',
				'The selected major was not found.'
			);
		}

		try {

			$pdo->beginTransaction();

			$pdo->prepare(
				'INSERT INTO Student_Major (student_ID, major_ID, date_Of_Choice)
				VALUES (?, ?, CURDATE())'
			)->execute([$studentID, $majorID]);

			// The first declared major becomes the primary major on Student.
			if (count($currentMajorIDs) === 0) {

				$pdo->prepare(
					'UPDATE Student SET major_ID = ? WHERE student_ID = ?'
				)->execute([$majorID, $studentID]);
			}

			audit_log($pdo, 'Update', 'Student_Major', (string) $majorID, 'Major declared');

			$pdo->commit();

			redirect_with_flash(
				url('student/programs.php'),
				'success',
				'Major declared.'
			);

		} catch (PDOException $e) {

			if ($pdo->inTransaction()) {
				$pdo->rollBack();
			}

			error_log('Add major failed: ' . $e->getMessage());

			redirect_with_flash(
				url('student/programs.php'),
				'error',
				'The major could not be declared.'
			);
		}
	}

if ($action === 'remove_major') {

	$majorID = post_int('major_id') ?? 0;

	if (!in_array($majorID, $currentMajorIDs, true)) {
		redirect_with_flash(
			url('student/programs.php'),
			'error',
			'You have not declared that major.'
		);
	}

	if (count($currentMajorIDs) <= 1) {
		redirect_with_flash(
			url('student/programs.php'),
			'error',
			'You must keep at least one major. Declare another major first.'
		);
	}

	try {

		$pdo->beginTransaction();

		$pdo->prepare(
			'DELETE FROM Student_Major WHERE student_ID = ? AND major_ID = ?'
		)->execute([$studentID, $majorID]);

		// If the primary major was removed, promote the remaining one.
		$student = one(
			$pdo,
			'SELECT major_ID FROM Student WHERE student_ID = ?',
			[$studentID]
		);

		if ($student && (int) $student['major_ID'] === $majorID) {

			$remaining = all_rows(
				$pdo,
				'SELECT major_ID FROM Student_Major WHERE student_ID = ? ORDER BY date_Of_Choice LIMIT 1',
				[$studentID]
			);

			$pdo->prepare(
				'UPDATE Student SET major_ID = ? WHERE student_ID = ?'
			)->execute([
				(int) ($remaining[0]['major_ID'] ?? 0),
				$studentID
			]);
		}

		audit_log($pdo, 'Update', 'Student_Major', (string) $majorID, 'Major removed');

		$pdo->commit();

		redirect_with_flash(
			url('student/programs.php'),
			'success',
			'Major removed.'
		);

	} catch (PDOException $e) {

		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}

		redirect_with_flash(
			url('student/programs.php'),
			'error',
			'The major could not be removed.'
		);
	}
}

if ($action === 'set_primary_major') {

	$majorID = post_int('major_id') ?? 0;

	if (!in_array($majorID, $currentMajorIDs, true)) {
		redirect_with_flash(
			url('student/programs.php'),
			'error',
			'You have not declared that major.'
		);
	}

	$pdo->prepare(
		'UPDATE Student SET major_ID = ? WHERE student_ID = ?'
	)->execute([$majorID, $studentID]);

	audit_log($pdo, 'Update', 'Student', (string) $majorID, 'Primary major changed');

	redirect_with_flash(
		url('student/programs.php'),
		'success',
		'Primary major updated.'
	);
}

	if ($action === 'add_minor') {

		$minorID = post_int('minor_id') ?? 0;

		// A minor requires at least one declared major.
		if (count($currentMajorIDs) < 1) {
			redirect_with_flash(
				url('student/programs.php'),
				'error',
				'You must declare a major before declaring a minor.'
			);
		}

		if ($currentMinor) {
			redirect_with_flash(
				url('student/programs.php'),
				'error',
				'You may declare at most one minor. Remove your current minor first.'
			);
		}

		if ($currentTotal >= 2) {
			redirect_with_flash(
				url('student/programs.php'),
				'error',
				'Cannot add minor — would exceed the combined limit of two majors and minors.'
			);
		}

		$minor = one(
			$pdo,
			'SELECT minor_ID FROM Minor WHERE minor_ID = ?',
			[$minorID]
		);

		if (!$minor) {
			redirect_with_flash(
				url('student/programs.php'),
				'error',
				'The selected minor was not found.'
			);
		}

		$pdo->prepare(
			'INSERT INTO Student_Minor (student_ID, minor_ID, date_Of_Choice)
			VALUES (?, ?, CURDATE())'
		)->execute([$studentID, $minorID]);

		audit_log($pdo, 'Update', 'Student_Minor', (string) $minorID, 'Minor declared');

		redirect_with_flash(
			url('student/programs.php'),
			'success',
			'Minor declared.'
		);
	}

	if ($action === 'remove_minor') {

	$minorID = post_int('minor_id') ?? 0;

	if (!$currentMinor || (int) $currentMinor['minor_ID'] !== $minorID) {
		redirect_with_flash(
			url('student/programs.php'),
			'error',
			'You have not declared that minor.'
		);
	}

	$pdo->prepare(
		'DELETE FROM Student_Minor WHERE student_ID = ? AND minor_ID = ?'
	)->execute([$studentID, $minorID]);

	audit_log($pdo, 'Update', 'Student_Minor', (string) $minorID, 'Minor removed');

	redirect_with_flash(
		url('student/programs.php'),
		'success',
		'Minor removed.'
	);
}

<?php

declare(strict_types=1);

// Advisee management actions.
// Uses $pdo, $facultyID from the entry point.
//
// Actions:
//   - remove_advisee: remove a student from this faculty's advising load
//   - assign_advisee: assign an unassigned student to this faculty
//
// Every change is audit-logged.

$action = post_str('action', 30);

// Current load for limit checks
$currentLoad = (int) (one(
	$pdo,
	'SELECT COUNT(*) AS total FROM Advisor WHERE faculty_ID = ?',
	[$facultyID]
)['total'] ?? 0);

if ($action === 'remove_advisee') {

	$studentID = post_int('student_id') ?? 0;

	// Verify the student is assigned to this faculty
	$assigned = one(
		$pdo,
		'SELECT 1 FROM Advisor WHERE student_ID = ? AND faculty_ID = ?',
		[$studentID, $facultyID]
	);

	if (!$assigned) {
		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => 'That student is not your advisee.'
		];
		header('Location: ' . url('faculty/advisees.php'));
		exit;
	}

	try {

		$pdo->beginTransaction();

		$pdo->prepare(
			'DELETE FROM Advisor WHERE student_ID = ? AND faculty_ID = ?'
		)->execute([$studentID, $facultyID]);

		audit_log(
			$pdo,
			'Delete',
			'Advisor',
			(string) $studentID,
			'Faculty ' . $facultyID . ' removed advisee ' . $studentID
		);

		$pdo->commit();

		$_SESSION['flash'] = [
			'type' => 'success',
			'message' => 'Advisee removed.'
		];

	} catch (Throwable $e) {

		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}

		error_log('Remove advisee failed: ' . $e->getMessage());

		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => 'Could not remove advisee.'
		];
	}

	header('Location: ' . url('faculty/advisees.php'));
	exit;
}

if ($action === 'assign_advisee') {

	$studentID = post_int('student_id') ?? 0;

	// Student must not already have an advisor
	$existing = one(
		$pdo,
		'SELECT faculty_ID FROM Advisor WHERE student_ID = ? LIMIT 1',
		[$studentID]
	);

	if ($existing) {
		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => 'That student already has an assigned advisor.'
		];
		header('Location: ' . url('faculty/advisees.php'));
		exit;
	}

	// Full-time faculty max 15 advisees
	if ($currentLoad >= 15) {
		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => 'You have reached the 15-advisee limit.'
		];
		header('Location: ' . url('faculty/advisees.php'));
		exit;
	}

	// Verify the student exists
	$student = one(
		$pdo,
		'SELECT student_ID, first_Name, last_Name, major_ID FROM Student WHERE student_ID = ?',
		[$studentID]
	);

	if (!$student) {
		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => 'Student not found.'
		];
		header('Location: ' . url('faculty/advisees.php'));
		exit;
	}

	// Verify the faculty shares a department with the student's major
	$majorDept = one(
		$pdo,
		'SELECT dept_ID FROM Major WHERE major_ID = ?',
		[(int) $student['major_ID']]
	);

	if (!$majorDept) {
		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => 'Student has no declared major with a department.'
		];
		header('Location: ' . url('faculty/advisees.php'));
		exit;
	}

	$deptMatch = one(
		$pdo,
		'SELECT 1 FROM Faculty_Department WHERE faculty_ID = ? AND dept_ID = ? LIMIT 1',
		[$facultyID, (int) $majorDept['dept_ID']]
	);

	if (!$deptMatch) {
		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => 'You are not in the student\'s major department.'
		];
		header('Location: ' . url('faculty/advisees.php'));
		exit;
	}

	try {

		$pdo->beginTransaction();

		$pdo->prepare(
			'INSERT INTO Advisor (student_ID, faculty_ID, date_Of_Appnt)
			 VALUES (?, ?, CURDATE())'
		)->execute([$studentID, $facultyID]);

		audit_log(
			$pdo,
			'Create',
			'Advisor',
			(string) $studentID,
			'Faculty ' . $facultyID . ' assigned advisee ' . $studentID
		);

		$pdo->commit();

		$_SESSION['flash'] = [
			'type' => 'success',
			'message' => 'Advisee assigned.'
		];

	} catch (Throwable $e) {

		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}

		error_log('Assign advisee failed: ' . $e->getMessage());

		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => 'Could not assign advisee.'
		];
	}

	header('Location: ' . url('faculty/advisees.php'));
	exit;
}

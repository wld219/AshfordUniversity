<?php

declare(strict_types=1);

// Advisors action: assign or remove an advisor.
// Uses $pdo, $error from the entry point.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

$action = post_str('action', 20);

if ($action === 'assign') {

	$facultyID = post_int('faculty_id') ?? 0;
	$studentID = post_int('student_id') ?? 0;
	$date = post_date('date_Of_Appnt') ?? date('Y-m-d');

	$student = one(
		$pdo,
		"SELECT student_ID FROM Student WHERE student_ID = ?
		AND student_ID IN (SELECT user_ID FROM User WHERE user_Type = 'Student')",
		[$studentID]
	);

	if (!$student) {
		$error = 'The student must exist.';
		return;
	}

	// Duplicates for the same pair are not allowed
	$existing = one(
		$pdo,
		'SELECT 1 FROM Advisor WHERE faculty_ID = ? AND student_ID = ?',
		[$facultyID, $studentID]
	);

	if ($existing) {
		$error = 'That advisor assignment already exists.';
		return;
	}

	// Faculty workload rules (specification):
	//   - full-time faculty may have at most 15 advisees
	//   - part-time faculty may not have advisees
	[$canAdvise, $adviseMessage] = faculty_can_have_advisee(
		$pdo,
		$facultyID
	);

	if (!$canAdvise) {
		$error = $adviseMessage;
		return;
	}

	// Advisor must share a department with the student's major (SRS 3.2.3b).
	$studentMajor = one(
		$pdo,
		'SELECT m.dept_ID
		 FROM Student s
		 JOIN Major m ON m.major_ID = s.major_ID
		 WHERE s.student_ID = ?',
		[$studentID]
	);

	if ($studentMajor) {
		$advisorDepts = all_rows(
			$pdo,
			'SELECT dept_ID FROM Faculty_Department WHERE faculty_ID = ?',
			[$facultyID]
		);

		$deptMatch = false;
		foreach ($advisorDepts as $d) {
			if ((int) $d['dept_ID'] === (int) $studentMajor['dept_ID']) {
				$deptMatch = true;
				break;
			}
		}

		if (!$deptMatch) {
			$error = 'Advisor must be from the student’s department.';
			return;
		}
	}

	$pdo->prepare(
		'INSERT INTO Advisor (faculty_ID, student_ID, date_Of_Appnt)
		VALUES (?, ?, ?)'
	)->execute([$facultyID, $studentID, $date]);

	audit_log($pdo, 'Create', 'Advisor', (string) $facultyID, "Advisee {$studentID} assigned");

	redirect_with_flash(url('admin/advisors.php'), 'success', 'Advisor assigned.');
}

if ($action === 'remove') {

	$facultyID = post_int('faculty_id') ?? 0;
	$studentID = post_int('student_id') ?? 0;

	// Students must keep at least one advisor
	$count = (int) (one(
		$pdo,
		'SELECT COUNT(*) AS total FROM Advisor WHERE student_ID = ?',
		[$studentID]
	)['total'] ?? 0);

	if ($count <= 1) {

		$error = 'Each student must keep at least one advisor.';
		return;
	}

	$pdo->prepare('DELETE FROM Advisor WHERE faculty_ID = ? AND student_ID = ?')
		->execute([$facultyID, $studentID]);

	audit_log($pdo, 'Delete', 'Advisor', (string) $facultyID, "Advisee {$studentID} removed");

	redirect_with_flash(url('admin/advisors.php'), 'success', 'Advisor assignment removed.');
}

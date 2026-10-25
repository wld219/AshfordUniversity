<?php

declare(strict_types=1);

// Semesters action: update windows or create a semester.
// Uses $pdo, $error from the entry point.

$action = post_str('action', 20);

if ($action === 'update') {

	$semesterID = post_int('semester_id') ?? 0;
	$semesterStart = post_date('semester_start');
	$registrationStart = post_date('registration_start');
	$registrationEnd = post_date('registration_end');
	$dropDeadline = post_date('drop_deadline');
	$gradeDeadline = post_str('grade_deadline', 16);

	// grade_deadline accepts "YYYY-MM-DD HH:MM" or "YYYY-MM-DD"
	if ($gradeDeadline !== '') {
		$normalized = date_create_from_format('Y-m-d H:i', $gradeDeadline)
			?: date_create_from_format('Y-m-d H:i:s', $gradeDeadline)
			?: date_create_from_format('Y-m-d', $gradeDeadline . ' 23:59');

		$gradeDeadline = $normalized !== false
			? $normalized->format('Y-m-d H:i:s')
			: '';
	}

	$pdo->prepare(
		'UPDATE Semester
		SET semester_Start = ?, registration_Start = ?, registration_End = ?,
			drop_Deadline = ?, grade_Deadline = ?
		WHERE semester_ID = ?'
	)->execute([
		$semesterStart,
		$registrationStart,
		$registrationEnd,
		$dropDeadline,
		$gradeDeadline !== '' ? $gradeDeadline : null,
		$semesterID
	]);

	audit_log($pdo, 'Update', 'Semester', (string) $semesterID, 'Semester windows updated');

	redirect_with_flash(
		url('admin/semesters.php'),
		'success',
		'Semester settings saved.'
	);
}

if ($action === 'create') {

	$semesterName = post_str('semester_name', 50);
	$semesterID = post_int('semester_id') ?? 0;

	if ($semesterName === '' || $semesterID <= 0) {
		$error = 'A semester name and numeric ID are required.';
	} else {

		$duplicate = one(
			$pdo,
			'SELECT semester_ID FROM Semester WHERE semester_ID = ? OR semester_Name = ?',
			[$semesterID, $semesterName]
		);

		if ($duplicate) {
			$error = 'That semester ID or name already exists.';
		} else {

			$pdo->prepare(
				'INSERT INTO Semester (semester_ID, semester_Name) VALUES (?, ?)'
			)->execute([$semesterID, $semesterName]);

			audit_log($pdo, 'Create', 'Semester', (string) $semesterID);

			redirect_with_flash(
				url('admin/semesters.php'),
				'success',
				'Semester created.'
			);
		}
	}
}

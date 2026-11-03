<?php

declare(strict_types=1);

// Academic actions: majors, minors, and their requirements.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

// Create a major
if ($action === 'create_major') {

	$majorID = post_int('major_id') ?? 0;
	$name = post_str('major_name', 100);
	$deptID = post_int('dept_id') ?? 0;

	if ($majorID <= 0 || $name === '' || $deptID <= 0) {
		$error = 'Major ID, name, and department are required.';
	} else {
		try {
			$pdo->prepare('INSERT INTO Major (major_ID, major_Name, dept_ID) VALUES (?, ?, ?)')
				->execute([$majorID, $name, $deptID]);

			audit_log($pdo, 'Create', 'Major', (string) $majorID);

			redirect_with_flash(url('admin/academic.php'), 'success', 'Major created.');
		} catch (Throwable $e) {
			$error = 'That major ID already exists.';
		}
	}
}

// Create a minor
if ($action === 'create_minor') {

	$minorID = post_int('minor_id') ?? 0;
	$name = post_str('minor_name', 100);
	$deptID = post_int('dept_id') ?? 0;

	if ($minorID <= 0 || $name === '' || $deptID <= 0) {
		$error = 'Minor ID, name, and department are required.';
	} else {
		try {
			$pdo->prepare('INSERT INTO Minor (minor_ID, minor_Name, dept_ID) VALUES (?, ?, ?)')
				->execute([$minorID, $name, $deptID]);

			audit_log($pdo, 'Create', 'Minor', (string) $minorID);

			redirect_with_flash(url('admin/academic.php'), 'success', 'Minor created.');
		} catch (Throwable $e) {
			$error = 'That minor ID already exists.';
		}
	}
}

// Add a major or minor requirement
if ($action === 'add_requirement') {

	$programType = post_str('program_type', 10);
	$programID = post_int('program_id') ?? 0;
	$courseID = post_int('course_id') ?? 0;
	$minGrade = strtoupper(post_str('min_grade', 5));

	$table = $programType === 'minor' ? 'Minor_Requirement' : 'Major_Requirement';
	$idColumn = $programType === 'minor' ? 'minor_ID' : 'major_ID';

	if ($programID <= 0 || $courseID <= 0 || grade_points($minGrade) === null) {
		$error = 'Choose a program, a course, and a valid minimum grade.';
	} else {

		try {
			$pdo->prepare(
				"INSERT INTO {$table} ({$idColumn}, course_ID, min_Grade_Req) VALUES (?, ?, ?)"
			)->execute([$programID, $courseID, $minGrade]);

			audit_log($pdo, 'Create', $table, (string) $programID, 'Requirement added');

			redirect_with_flash(url('admin/academic.php'), 'success', 'Requirement added.');

		} catch (Throwable $e) {
			$error = 'That requirement already exists.';
		}
	}
}

// Remove a major or minor requirement
if ($action === 'remove_requirement') {

	$programType = post_str('program_type', 10);
	$programID = post_int('program_id') ?? 0;
	$courseID = post_int('course_id') ?? 0;

	$table = $programType === 'minor' ? 'Minor_Requirement' : 'Major_Requirement';
	$idColumn = $programType === 'minor' ? 'minor_ID' : 'major_ID';

	$pdo->prepare("DELETE FROM {$table} WHERE {$idColumn} = ? AND course_ID = ?")
		->execute([$programID, $courseID]);

	audit_log($pdo, 'Delete', $table, (string) $programID, 'Requirement removed');

	redirect_with_flash(url('admin/academic.php'), 'success', 'Requirement removed.');
}

<?php

declare(strict_types=1);

// Academic actions: courses and prerequisites.

// Save a course (create or update)
if ($action === 'save_course') {

	$courseID = post_int('course_id') ?? 0;
	$deptID = post_int('dept_id') ?? 0;
	$name = post_str('course_name', 100);
	$credits = post_int('credits') ?? 0;
	$type = post_str('course_type', 20);
	$description = post_str('course_dsc', 2000);
	$existingID = post_int('existing_course_id');

	if ($courseID <= 0 || $deptID <= 0 || $name === '' || $credits <= 0) {
		$error = 'Course ID, department, name, and credits are required.';
	} elseif (!in_array($type, ['Undergraduate', 'Graduate'], true)) {
		$error = 'Course level must be Undergraduate or Graduate.';
	} else {

		if ($existingID !== null) {

			$pdo->prepare(
				'UPDATE Course
				SET course_ID = ?, dept_ID = ?, course_Name = ?, course_Credits = ?,
					course_Type = ?, course_DSC = ?
				WHERE course_ID = ?'
			)->execute([
				$courseID, $deptID, $name, $credits, $type, $description, $existingID
			]);

			audit_log($pdo, 'Update', 'Course', (string) $courseID, 'Course updated');

			redirect_with_flash(url('admin/academic.php'), 'success', 'Course updated.');

		} else {

			try {
				$pdo->prepare(
					'INSERT INTO Course
						(course_ID, dept_ID, course_Name, course_Credits, course_DSC, course_Type)
					VALUES (?, ?, ?, ?, ?, ?)'
				)->execute([$courseID, $deptID, $name, $credits, $description, $type]);

				audit_log($pdo, 'Create', 'Course', (string) $courseID);

				redirect_with_flash(url('admin/academic.php'), 'success', 'Course created.');

			} catch (Throwable $e) {
				$error = 'That course ID already exists.';
			}
		}
	}
}

// Add a prerequisite
if ($action === 'add_prerequisite') {

	$courseID = post_int('course_id') ?? 0;
	$prereqID = post_int('prereq_id') ?? 0;
	$minGrade = strtoupper(post_str('min_grade', 5));

	if ($courseID <= 0 || $prereqID <= 0 || $courseID === $prereqID) {
		$error = 'Choose two different courses.';
	} elseif (grade_points($minGrade) === null) {
		$error = 'Minimum grade must be a letter grade such as C or B+.';
	} else {

		try {
			$pdo->prepare(
				'INSERT INTO Course_Prerequisite (course_ID, prerequisite_course_ID, min_Grade_Req)
				VALUES (?, ?, ?)'
			)->execute([$courseID, $prereqID, $minGrade]);

			audit_log($pdo, 'Create', 'Course_Prerequisite', (string) $courseID, 'Prerequisite added');

			redirect_with_flash(url('admin/academic.php'), 'success', 'Prerequisite added.');

		} catch (Throwable $e) {
			$error = 'That prerequisite already exists for this course.';
		}
	}
}

// Remove a prerequisite
if ($action === 'remove_prerequisite') {

	$courseID = post_int('course_id') ?? 0;
	$prereqID = post_int('prereq_id') ?? 0;

	$pdo->prepare(
		'DELETE FROM Course_Prerequisite WHERE course_ID = ? AND prerequisite_course_ID = ?'
	)->execute([$courseID, $prereqID]);

	audit_log($pdo, 'Delete', 'Course_Prerequisite', (string) $courseID, 'Prerequisite removed');

	redirect_with_flash(url('admin/academic.php'), 'success', 'Prerequisite removed.');
}

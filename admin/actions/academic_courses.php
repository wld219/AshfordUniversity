<?php

declare(strict_types=1);

// Academic actions: courses and prerequisites.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

// Save a course (create or update)
if ($action === 'save_course') {
    $courseID = trim((string) ($_POST['course_id'] ?? ''));
    $existingID = trim((string) ($_POST['existing_course_id'] ?? ''));
    $deptRaw = trim((string) ($_POST['dept_id'] ?? ''));
    $creditsRaw = trim((string) ($_POST['credits'] ?? ''));
    $name = trim((string) ($_POST['course_name'] ?? ''));
    $type = trim((string) ($_POST['course_type'] ?? ''));
    $description = trim((string) ($_POST['course_dsc'] ?? ''));

    if ($courseID === '' || mb_strlen($courseID) > 10 ||
        ($existingID !== '' && mb_strlen($existingID) > 10)) {
        $error = 'Course ID is required and must be at most 10 characters.';
    } elseif ($name === '' || mb_strlen($name) > 255) {
        $error = 'Course name is required and must be at most 255 characters.';
    } elseif ($description === '' || mb_strlen($description) > 500) {
        $error = 'Description is required and must be at most 500 characters.';
    } elseif (!preg_match('/^[1-9][0-9]*$/D', $deptRaw) ||
              filter_var($deptRaw, FILTER_VALIDATE_INT) === false) {
        $error = 'Select a valid department.';
    } elseif (!preg_match('/^[1-9][0-9]*$/D', $creditsRaw) ||
              strlen($creditsRaw) > 10 ||
              (float) $creditsRaw > 2147483647) {
        $error = 'Credits must be a positive whole number within the MySQL INT range.';
    } elseif (!in_array($type, ['Undergraduate', 'Graduate'], true)) {
        $error = 'Select Undergraduate or Graduate.';
    } else {
        $deptID = (int) $deptRaw;
        $credits = (int) $creditsRaw;

        $department = one($pdo,
            'SELECT dept_ID FROM Department WHERE dept_ID = ?', [$deptID]);

        $oldCourse = $existingID !== ''
            ? one($pdo, 'SELECT course_ID FROM Course WHERE course_ID = ?', [$existingID])
            : null;

        $duplicate = one($pdo,
            'SELECT course_ID FROM Course WHERE course_ID = ?', [$courseID]);

        if ($department === null) {
            $error = 'The selected department does not exist.';
        } elseif ($existingID !== '' && $oldCourse === null) {
            $error = 'The course being edited no longer exists.';
        } elseif ($duplicate !== null && (
            $existingID === '' || $duplicate['course_ID'] !== $existingID
        )) {
            $error = 'That Course ID already exists. Choose another.';
        } else {
            try {
                if ($existingID !== '') {
                    $pdo->prepare(
                        'UPDATE Course
                         SET course_ID = ?, dept_ID = ?, course_Name = ?,
                             course_Credits = ?, course_Type = ?, course_DSC = ?
                         WHERE course_ID = ?'
                    )->execute([
                        $courseID, $deptID, $name, $credits,
                        $type, $description, $existingID
                    ]);
                    audit_log($pdo, 'Update', 'Course', $courseID, 'Course updated');
                    redirect_with_flash(url('admin/academic.php'), 'success', 'Course updated.');
                } else {
                    $pdo->prepare(
                        'INSERT INTO Course
                         (course_ID, dept_ID, course_Name, course_Credits, course_DSC, course_Type)
                         VALUES (?, ?, ?, ?, ?, ?)'
                    )->execute([
                        $courseID, $deptID, $name, $credits, $description, $type
                    ]);
                    audit_log($pdo, 'Create', 'Course', $courseID);
                    redirect_with_flash(url('admin/academic.php'), 'success', 'Course created.');
                }
            } catch (PDOException $e) {
                $sqlState = (string) $e->getCode();
                $driverCode = (int) ($e->errorInfo[1] ?? 0);
                if ($driverCode === 1062) {
                    $error = 'That Course ID already exists.';
                } elseif ($driverCode === 1451 || $driverCode === 1452) {
                    $error = 'This change conflicts with an existing academic relationship.';
                } else {
                    error_log('Academic course save failed: ' . $e->getMessage());
                    $error = 'Could not save the course. Check the server error log.';
                }
            }
        }
    }
}

// Add a prerequisite
if ($action === 'add_prerequisite') {

	$courseID = post_str('course_id', 10);
	$prereqID = post_str('prereq_id', 10);
	$minGrade = strtoupper(post_str('min_grade', 5));

	if ($courseID === '' || $prereqID === '' || $courseID === $prereqID) {
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

	$courseID = post_str('course_id', 10);
	$prereqID = post_str('prereq_id', 10);

	$pdo->prepare(
		'DELETE FROM Course_Prerequisite WHERE course_ID = ? AND prerequisite_course_ID = ?'
	)->execute([$courseID, $prereqID]);

	audit_log($pdo, 'Delete', 'Course_Prerequisite', (string) $courseID, 'Prerequisite removed');

	redirect_with_flash(url('admin/academic.php'), 'success', 'Prerequisite removed.');
}

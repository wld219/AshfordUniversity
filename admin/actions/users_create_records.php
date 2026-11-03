<?php

declare(strict_types=1);

// User account creation (admin portal). Uses $pdo and the helpers loaded by
// the entry point. Creates the shared User + Login rows and the role row,
// all inside one transaction, then writes the audit entry.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

// Create the student's program rows (major, level, credit limits).
function create_student_record(PDO $pdo, int $userID): void
{
	$majorID = post_int('major_id') ?? 0;

	$major = one(
		$pdo,
		'SELECT dept_ID FROM Major WHERE major_ID = ?',
		[$majorID]
	);

	if (!$major) {
		throw new RuntimeException('Selected major was not found.');
	}

	$isGraduate = post_str('student_type', 20) === 'Graduate';
	$isPartTime = post_str('undergraduate_student_type', 20) === 'Part-Time';

	$pdo->prepare(
		'INSERT INTO Student
			(student_ID, major_ID, student_Year, student_Type)
		VALUES (?, ?, 1, ?)'
	)->execute([
		$userID,
		$majorID,
		$isGraduate ? 'Graduate' : 'Undergraduate',
	]);

	if ($isGraduate) {

		$pdo->prepare(
			'INSERT INTO Graduate_Student
				(student_ID, dept_ID, Program, graduate_Student_Type)
			VALUES (?, ?, ?, ?)'
		)->execute([
			$userID,
			(int) $major['dept_ID'],
			"Master's",
			'Full-Time',
		]);

		$pdo->prepare(
			'INSERT INTO Full_Time_Graduate
				(student_ID, year, credits_Earned, Thesis)
			VALUES (?, 1, 0, NULL)'
		)->execute([$userID]);

	} elseif ($isPartTime) {

		$pdo->prepare(
			'INSERT INTO Undergraduate
				(student_ID, dept_ID, undergraduate_student_Type)
			VALUES (?, ?, ?)'
		)->execute([$userID, (int) $major['dept_ID'], 'Part-Time']);

		$pdo->prepare(
			'INSERT INTO Part_Time_Undergraduate
				(student_ID, Status, min_Credits, max_Credits, credits_Earned)
			VALUES (?, ?, 4, 8, 0)'
		)->execute([$userID, 'Freshman']);

	} else {

		$pdo->prepare(
			'INSERT INTO Undergraduate
				(student_ID, dept_ID, undergraduate_student_Type)
			VALUES (?, ?, ?)'
		)->execute([$userID, (int) $major['dept_ID'], 'Full-Time']);

		$pdo->prepare(
			'INSERT INTO Full_Time_Undergraduate
				(student_ID, Status, min_Credits, max_Credits, credits_Earned)
			VALUES (?, ?, 12, 16, 0)'
		)->execute([$userID, 'Freshman']);
	}

	$pdo->prepare(
		'INSERT INTO Student_Major
			(student_ID, major_ID, date_Of_Choice)
		VALUES (?, ?, CURDATE())'
	)->execute([$userID, $majorID]);
}

// Create the faculty record (office, rank, type, section count).
function create_faculty_record(PDO $pdo, int $userID): void
{
	$officeID = post_int('office_id') ?? 0;

	$office = one(
		$pdo,
		'SELECT office_ID FROM Office WHERE office_ID = ?',
		[$officeID]
	);

	if (!$office) {
		throw new RuntimeException('Selected office was not found.');
	}

	$isPartTime = post_str('faculty_type', 20) === 'Part-Time';

	$pdo->prepare(
		'INSERT INTO Faculty
			(faculty_ID, office_ID, Specialty, faculty_Rank,
			faculty_Type, no_of_Classes)
		VALUES (?, ?, ?, ?, ?, 0)'
	)->execute([
		$userID,
		$officeID,
		post_str('specialty', 100) ?: 'General',
		post_str('faculty_rank', 50) ?: 'Assistant Professor',
		$isPartTime ? 'Part-Time' : 'Full-Time',
	]);

	if ($isPartTime) {

		$pdo->prepare(
			'INSERT INTO Part_Time_Faculty
				(faculty_ID, no_of_Classes, DOA)
			VALUES (?, 0, CURDATE())'
		)->execute([$userID]);

	} else {

		$pdo->prepare(
			'INSERT INTO Full_Time_Faculty
				(faculty_ID, no_of_Classes)
			VALUES (?, 0)'
		)->execute([$userID]);
	}
}

// Create the administrator or statistics-staff role row.
function create_staff_record(PDO $pdo, int $userID, string $role): void
{
	if ($role === 'Admin') {

		$level = post_str('security_level', 20) === 'Read-Only'
			? 'Read-Only'
			: 'Full-Access';

		$pdo->prepare(
			'INSERT INTO Admin (user_ID, security_Level) VALUES (?, ?)'
		)->execute([$userID, $level]);

		return;
	}

	$pdo->prepare(
		'INSERT INTO StatStaff (user_ID, employment_Type, access_Level)
		VALUES (?, ?, ?)'
	)->execute([
		$userID,
		post_str('employment_type', 20) === 'Part-Time'
			? 'Part-Time'
			: 'Full-Time',
		'Read-Only',
	]);
}

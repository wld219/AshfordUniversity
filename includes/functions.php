<?php

declare(strict_types=1);

/*
 * Core database helpers and the module aggregator.
 *
 * Domain logic lives in includes/functions/*.php:
 *   grades.php      grade points and minimum-grade comparison
 *   students.php    student credit limits
 *   time.php        time-slot labels and overlap primitives
 *   windows.php     registration and drop window checks
 *   registration.php section eligibility checks
 *   meetings.php    attendance and grade window checks
 *   faculty.php     faculty workload rules
 *   semester.php    semester schedule locks
 *   notifications.php section cancellation emails
 *   schedules.php   schedule row loading
 *   validation.php  section/semester validation
 *   master_schedule.php schedule records (status, version, publish)
 */

/*
 * Graduate credit limits: the schema stores min/max_Credits only for
 * undergraduate students, so these constants are the documented defaults
 * for graduate registrations. Adjust here if the specification changes.
 */
const GRADUATE_FULL_TIME_CREDIT_LIMIT = 12;
const GRADUATE_PART_TIME_CREDIT_LIMIT = 6;

// Default credit limits used when a student has no enrollment-status row.
const DEFAULT_UNDERGRADUATE_CREDIT_LIMIT = 16;
const DEFAULT_PART_TIME_UNDERGRADUATE_CREDIT_LIMIT = 8;

foreach ([
	'grades.php',
	'students.php',
	'time.php',
	'windows.php',
	'registration.php',
	'registration_course.php',
	'registration_check.php',
	'meetings.php',
	'faculty.php',
	'semester.php',
	'notifications.php',
	'schedules.php',
	'validation_row.php',
	'validation_semester.php',
	'validation_assignment.php',
	'master_schedule.php',
] as $module) {
	require_once __DIR__ . '/functions/' . $module;
}


// Get one row from the database
function one(
	PDO $pdo,
	string $sql,
	array $params = []
): ?array {

	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);

	$row = $stmt->fetch();

	if ($row) {
		return $row;
	}

	return null;
}


// Get multiple rows from the database
function all_rows(
	PDO $pdo,
	string $sql,
	array $params = []
): array {

	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);

	return $stmt->fetchAll();
}


// Get the ID for a semester
function semester_id(
	PDO $pdo,
	string $name = 'Fall 2026'
): ?int {

	$sql = "SELECT semester_ID
			FROM Semester
			WHERE semester_Name = ?
			LIMIT 1";

	$semester = one(
		$pdo,
		$sql,
		[$name]
	);

	if ($semester) {
		return (int) $semester['semester_ID'];
	}

	return null;
}

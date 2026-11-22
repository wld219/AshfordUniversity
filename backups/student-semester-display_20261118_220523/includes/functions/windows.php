<?php

declare(strict_types=1);

// Semester registration and drop windows.
//
// A NULL date means no deadline is configured (documented behaviour:
// deadlines only apply once the university configures them).

// Is the given datetime inside the semester registration window?
function registration_window_status(
	PDO $pdo,
	int $semesterID
): array {

	$semester = one(
		$pdo,
		"SELECT registration_Start, registration_End, drop_Deadline
		FROM Semester
		WHERE semester_ID = ?",
		[$semesterID]
	);

	if (!$semester) {
		return [false, false, 'Semester not found.'];
	}

	$now = new DateTimeImmutable('now');

	$start = $semester['registration_Start'] !== null
		? new DateTimeImmutable((string) $semester['registration_Start'])
		: null;

	$end = $semester['registration_End'] !== null
		? DateTimeImmutable::createFromFormat(
			'Y-m-d H:i:s',
			(string) $semester['registration_End'] . ' 23:59:59'
		)
		: null;

	if ($start !== null && $now < $start) {
		return [false, false, 'Registration for this semester has not opened yet.'];
	}

	if ($end !== null && $now > $end) {
		return [false, false, 'The registration deadline for this semester has passed.'];
	}

	return [true, true, 'Open'];
}

/*
 * The semester a student should currently be registering for: the newest
 * term that has open, non-cancelled sections and an open registration
 * window. When no window is open this falls back to the newest term with
 * sections, so pages still render instead of showing nothing.
 */
function current_registration_semester(PDO $pdo): ?int
{
	$semesters = all_rows(
		$pdo,
		"SELECT s.semester_ID
		FROM Semester s
		JOIN Course_Section cs
			ON cs.semester_ID = s.semester_ID AND cs.is_Cancelled = 0
		GROUP BY s.semester_ID
		ORDER BY s.semester_Name DESC"
	);

	$newestWithSections = null;

	foreach ($semesters as $row) {

		$semesterID = (int) $row['semester_ID'];

		if ($newestWithSections === null) {
			$newestWithSections = $semesterID;
		}

		[$windowOpen] = registration_window_status($pdo, $semesterID);

		if ($windowOpen) {
			return $semesterID;
		}
	}

	return $newestWithSections;
}

// Has the drop deadline for a semester passed?
function drop_window_status(
	PDO $pdo,
	int $semesterID
): array {

	$semester = one(
		$pdo,
		"SELECT drop_Deadline
		FROM Semester
		WHERE semester_ID = ?",
		[$semesterID]
	);

	if (!$semester || $semester['drop_Deadline'] === null) {
		return [true, 'Open'];
	}

	$deadline = DateTimeImmutable::createFromFormat(
		'Y-m-d H:i:s',
		(string) $semester['drop_Deadline'] . ' 23:59:59'
	);

	if ($deadline !== false && new DateTimeImmutable('now') > $deadline) {
		return [false, 'The drop deadline for this semester has passed.'];
	}

	return [true, 'Open'];
}

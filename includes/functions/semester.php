<?php

declare(strict_types=1);

// Semester schedule locks.

// Are schedule fields (course, faculty, room, time slot) still editable?
// Locked when the semester has started or its start date is unknown. A
// missing date must not make an unverified historical schedule editable.
function semester_schedule_locked(PDO $pdo, int $semesterID): bool
{
	$semester = one(
		$pdo,
		'SELECT semester_Start FROM Semester WHERE semester_ID = ?',
		[$semesterID]
	);

	if (!$semester || $semester['semester_Start'] === null) {
		return true;
	}

	return new DateTimeImmutable('now') >= new DateTimeImmutable((string) $semester['semester_Start']);
}

// How many students are enrolled in a section?
function section_enrolled_count(PDO $pdo, int $crn): int
{
	return (int) (one(
		$pdo,
		'SELECT COUNT(*) AS total FROM Enrollment WHERE CRN = ?',
		[$crn]
	)['total'] ?? 0);
}

<?php

declare(strict_types=1);

// Master_Schedule record helpers (status, optimistic concurrency, audit).

function master_schedule_get(PDO $pdo, int $semesterID): ?array
{
	return one(
		$pdo,
		'SELECT * FROM Master_Schedule WHERE semester_ID = ?',
		[$semesterID]
	);
}

// Create the schedule record for a semester (fails when one already exists).
function master_schedule_create(PDO $pdo, int $semesterID, int $adminID): array
{
	$semester = one(
		$pdo,
		'SELECT semester_ID FROM Semester WHERE semester_ID = ?',
		[$semesterID]
	);

	if (!$semester) {
		return [false, 'The selected semester does not exist.'];
	}

	if (master_schedule_get($pdo, $semesterID) !== null) {
		return [false, 'A master schedule already exists for this semester.'];
	}

	try {

		$pdo->prepare(
			'INSERT INTO Master_Schedule (semester_ID, status, created_By_User_ID)
			VALUES (?, ?, ?)'
		)->execute([$semesterID, 'Draft', $adminID]);

	} catch (Throwable $e) {

		error_log('Master schedule creation failed: ' . $e->getMessage());

		return [false, 'The schedule could not be created.'];
	}

	audit_log(
		$pdo,
		'Create',
		'Master_Schedule',
		(string) $semesterID,
		'Master schedule created (Draft)'
	);

	return [true, 'Master schedule created for the selected semester.'];
}

/**
 * Bump the schedule version (optimistic concurrency).
 *
 * @return array{ok: bool, message: string}
 */
function master_schedule_touch(
	PDO $pdo,
	int $semesterID,
	int $adminID,
	?int $expectedVersion = null
): array {

	$schedule = master_schedule_get($pdo, $semesterID);

	if ($schedule === null) {
		return [false, 'No master schedule exists for this semester yet.'];
	}

	if (
		$expectedVersion !== null &&
		(int) $schedule['version'] !== $expectedVersion
	) {
		return [
			false,
			'Another administrator changed this schedule. Reload it before saving.'
		];
	}

	$pdo->prepare(
		'UPDATE Master_Schedule
		SET version = version + 1, updated_By_User_ID = ?, updated_At = NOW()
		WHERE semester_ID = ?'
	)->execute([$adminID, $semesterID]);

	return [true, 'Schedule version updated.'];
}

/**
 * Publish a schedule. Only a conflict-free validation may be published.
 *
 * @return array{ok: bool, message: string}
 */
function master_schedule_publish(
	PDO $pdo,
	int $semesterID,
	int $adminID,
	?int $expectedVersion = null
): array {

	$schedule = master_schedule_get($pdo, $semesterID);

	if ($schedule === null) {
		return [false, 'No master schedule exists for this semester yet.'];
	}

	if (
		$expectedVersion !== null &&
		(int) $schedule['version'] !== $expectedVersion
	) {
		return [
			false,
			'Another administrator changed this schedule. Reload it before publishing.'
		];
	}

	$validation = validate_semester_schedule($pdo, $semesterID);

	if (!$validation['valid']) {

		$errorCount = count($validation['issues']);

		return [
			false,
			"Cannot publish: {$errorCount} validation issue(s) remain. "
			. 'Resolve all conflicts and missing assignments first.'
		];
	}

	$pdo->prepare(
		"UPDATE Master_Schedule
		SET status = 'Published',
			version = version + 1,
			updated_By_User_ID = ?,
			updated_At = NOW(),
			published_By_User_ID = ?,
			published_At = NOW()
		WHERE semester_ID = ?"
	)->execute([$adminID, $adminID, $semesterID]);

	audit_log(
		$pdo,
		'Publish',
		'Master_Schedule',
		(string) $semesterID,
		'Master schedule published'
	);

	return [true, 'Master schedule published.'];
}

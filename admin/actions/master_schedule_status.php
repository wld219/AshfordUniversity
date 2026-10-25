<?php

declare(strict_types=1);

// Master schedule status actions: create, publish, reopen.
// Uses $pdo, $adminID, $isFullAccess from the entry point. Redirects on
// success, or leaves $error set for the view.

if (!$isFullAccess) {
	redirect_with_flash(
		url('admin/master-schedule.php'),
		'error',
		'You have read-only access and cannot change the master schedule.'
	);
}

$semesterID = post_int('semester_id') ?? 0;
$expectedVersion = post_int('version');

// Create the schedule record for a semester
if ($action === 'create') {

	[$ok, $message] = master_schedule_create($pdo, $semesterID, $adminID);

	redirect_with_flash(
		url('admin/master-schedule.php') . '?semester=' . $semesterID,
		$ok ? 'success' : 'error',
		$message
	);
}

// Publish a validated schedule
if ($action === 'publish') {

	[$ok, $message] = master_schedule_publish(
		$pdo,
		$semesterID,
		$adminID,
		$expectedVersion
	);

	redirect_with_flash(
		url('admin/master-schedule.php') . '?semester=' . $semesterID,
		$ok ? 'success' : 'error',
		$message
	);
}

// Reopen a published schedule for editing
if ($action === 'reopen') {

	try {

		$pdo->beginTransaction();

		[$ok, $message] = master_schedule_touch(
			$pdo,
			$semesterID,
			$adminID,
			$expectedVersion
		);

		if ($ok) {

			$pdo->prepare(
				"UPDATE Master_Schedule
				SET status = 'Draft'
				WHERE semester_ID = ?"
			)->execute([$semesterID]);

			audit_log($pdo, 'Update', 'Master_Schedule', (string) $semesterID, 'Schedule reopened for editing');

			$pdo->commit();

			$message = 'Schedule reopened for editing.';

		} else {

			$pdo->rollBack();
		}

	} catch (Throwable $e) {

		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}

		error_log('Schedule reopen failed: ' . $e->getMessage());

		[$ok, $message] = [false, 'The schedule could not be reopened.'];
	}

	redirect_with_flash(
		url('admin/master-schedule.php') . '?semester=' . $semesterID,
		$ok ? 'success' : 'error',
		$message
	);
}

<?php

declare(strict_types=1);

// Master schedule action: update one section inside the schedule.
// Uses $pdo, $adminID, $isFullAccess, $error from the entry point.
//
// Optimistic concurrency: the posted version must match the stored one.
// Schedule fields (faculty, time slot, room) lock once the semester starts;
// only seat counts may change after that.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

if (!$isFullAccess) {
	redirect_with_flash(
		url('admin/master-schedule.php'),
		'error',
		'You have read-only access and cannot change the master schedule.'
	);
}

$crn = post_int('crn') ?? 0;
$expectedVersion = post_int('version');

$section = one(
	$pdo,
	'SELECT * FROM Course_Section WHERE CRN = ?',
	[$crn]
);

if (!$section) {

	$error = 'Section not found.';

} else {

	$schedule = master_schedule_get($pdo, (int) $section['semester_ID']);

	if ($schedule === null) {

		$error = 'No master schedule exists for this semester.';

	} elseif (
		$expectedVersion !== null &&
		(int) $schedule['version'] !== $expectedVersion
	) {

		$error = 'Another administrator changed this schedule. Reload before saving.';

	} else {

		$locked = semester_schedule_locked($pdo, (int) $section['semester_ID']);

		$seats = post_int('seats') ?? (int) $section['available_Seats'];

		if ($locked) {

			// After the semester starts, schedule fields are locked;
			// only the seat count may change. The seat write, version
			// bump, and audit entry commit as one transaction.
			try {

				$pdo->beginTransaction();

				$pdo->prepare('UPDATE Course_Section SET available_Seats = ? WHERE CRN = ?')
					->execute([max(0, $seats), $crn]);

				$pdo->prepare(
					'UPDATE Master_Schedule
					SET version = version + 1, updated_By_User_ID = ?, updated_At = NOW()
					WHERE semester_ID = ?'
				)->execute([$adminID, (int) $section['semester_ID']]);

				audit_log(
					$pdo,
					'Update',
					'Master_Schedule',
					(string) $section['semester_ID'],
					"Seats updated for CRN {$crn} (schedule locked)"
				);

				$pdo->commit();

			} catch (Throwable $e) {

				if ($pdo->inTransaction()) {
					$pdo->rollBack();
				}

				error_log('Seat update failed: ' . $e->getMessage());

				redirect_with_flash(
					url('admin/master-schedule.php') . '?semester=' . $section['semester_ID'],
					'error',
					'The seat count could not be updated.'
				);
			}

			redirect_with_flash(
				url('admin/master-schedule.php') . '?semester=' . $section['semester_ID'],
				'success',
				'Seat count updated. Schedule fields are locked after the semester starts.'
			);
		}

		$facultyID = post_int('faculty_id') ?? (int) $section['faculty_ID'];
		$timeSlotID = post_int('time_slot_id') ?? (int) $section['time_Slot_ID'];
		$courseID = post_int('course_id') ?? (int) $section['course_ID'];
		$roomType = post_str('room_type', 10);
		$roomID = post_int('room_id');

		$lectureID = $roomType === 'lecture' ? $roomID : null;
		$labID = $roomType === 'lab' ? $roomID : null;

		[$ok, $errors] = validate_section_assignment(
			$pdo,
			[
				'course_ID' => $courseID,
				'faculty_ID' => $facultyID,
				'time_Slot_ID' => $timeSlotID,
				'lecture_ID' => $lectureID,
				'lab_ID' => $labID,
				'seats' => $seats,
				'semester_ID' => (int) $section['semester_ID'],
			],
			$crn
		);

		if (!$ok) {

			$error = implode(' ', $errors);

		} else {

			try {

				$pdo->beginTransaction();

				$pdo->prepare(
					'UPDATE Course_Section
					SET faculty_ID = ?, time_Slot_ID = ?, lecture_ID = ?, lab_ID = ?,
						available_Seats = ?
					WHERE CRN = ?'
				)->execute([
					$facultyID,
					$timeSlotID,
					$lectureID,
					$labID,
					max(0, $seats),
					$crn
				]);

				$pdo->prepare(
					'UPDATE Master_Schedule
					SET version = version + 1, updated_By_User_ID = ?, updated_At = NOW()
					WHERE semester_ID = ?'
				)->execute([$adminID, (int) $section['semester_ID']]);

				audit_log(
					$pdo,
					'Update',
					'Master_Schedule',
					(string) $section['semester_ID'],
					"Section {$crn} reassigned"
				);

				$pdo->commit();

			} catch (Throwable $e) {

				if ($pdo->inTransaction()) {
					$pdo->rollBack();
				}

				error_log('Section update failed: ' . $e->getMessage());

				redirect_with_flash(
					url('admin/master-schedule.php') . '?semester=' . $section['semester_ID'],
					'error',
					'The section could not be updated.'
				);
			}

			redirect_with_flash(
				url('admin/master-schedule.php') . '?semester=' . $section['semester_ID'],
				'success',
				"Section {$crn} updated in the master schedule."
			);
		}
	}
}

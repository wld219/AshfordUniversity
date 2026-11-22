<?php

declare(strict_types=1);

/** The existing appointment workflow uses 30-minute booking slots. */
function advising_appointment_duration_minutes(): int
{
	return 30;
}

function advising_parse_slot(string $date, string $time): ?DateTimeImmutable
{
	if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
		|| !preg_match('/^\d{2}:\d{2}$/', $time)) {
		return null;
	}

	$slot = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date . ' ' . $time);
	$errors = DateTimeImmutable::getLastErrors();
	if (!$slot || ($errors !== false && ($errors['warning_count'] || $errors['error_count']))
		|| $slot->format('Y-m-d') !== $date || $slot->format('H:i') !== $time) {
		return null;
	}

	return $slot;
}

function advising_slot_is_in_active_window(PDO $pdo, int $facultyId, DateTimeImmutable $slot): bool
{
	$end = $slot->modify('+' . advising_appointment_duration_minutes() . ' minutes');
	$row = one(
		$pdo,
		"SELECT 1
		 FROM Faculty_Availability fa
		 JOIN Day d ON d.day_ID = fa.day_ID
		 WHERE fa.faculty_ID = ?
		   AND fa.is_active = 1
		   AND d.day_Of_Week = ?
		   AND fa.start_Time <= ?
		   AND fa.end_Time >= ?
		 LIMIT 1",
		[$facultyId, $slot->format('l'), $slot->format('H:i:s'), $end->format('H:i:s')]
	);
	return $row !== null;
}

/** Pending and Scheduled bookings reserve their full 30-minute interval. */
function advising_slot_has_conflict(
	PDO $pdo,
	int $facultyId,
	int $studentId,
	DateTimeImmutable $slot,
	int $exceptAppointmentId = 0
): bool {
	$end = $slot->modify('+' . advising_appointment_duration_minutes() . ' minutes');
	return one(
		$pdo,
		"SELECT appointment_ID
		 FROM Advising_Appointment
		 WHERE appointment_ID <> ?
		   AND (faculty_ID = ? OR student_ID = ?)
		   AND status IN ('Pending', 'Scheduled')
		   AND requested_DateTime < ?
		   AND DATE_ADD(requested_DateTime, INTERVAL 30 MINUTE) > ?
		 LIMIT 1",
		[$exceptAppointmentId, $facultyId, $studentId, $end->format('Y-m-d H:i:s'), $slot->format('Y-m-d H:i:s')]
	) !== null;
}

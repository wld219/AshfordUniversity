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

/**
 * Resolve a date only when it falls in a bounded semester interval. Semester
 * start dates are the source of truth; the next known start is the exclusive
 * end. The latest semester has no known end and is intentionally unmappable.
 */
function advising_resolve_semester_for_date(PDO $pdo, DateTimeImmutable $date): ?int
{
	$semesters = all_rows(
		$pdo,
		"SELECT semester_ID, semester_Start FROM Semester
		 WHERE semester_Start IS NOT NULL ORDER BY semester_Start, semester_ID"
	);
	$dateOnly = $date->format('Y-m-d');
	for ($index = 0, $count = count($semesters); $index < $count; $index++) {
		$start = (string) $semesters[$index]['semester_Start'];
		$nextStart = isset($semesters[$index + 1]) ? (string) $semesters[$index + 1]['semester_Start'] : null;
		if ($dateOnly >= $start && $nextStart !== null && $dateOnly < $nextStart) {
			return (int) $semesters[$index]['semester_ID'];
		}
	}
	return null;
}

function advising_semester_date_ranges(PDO $pdo): array
{
	$semesters = all_rows(
		$pdo,
		"SELECT semester_ID, semester_Name, semester_Start FROM Semester
		 WHERE semester_Start IS NOT NULL ORDER BY semester_Start, semester_ID"
	);
	$ranges = [];
	foreach ($semesters as $index => $semester) {
		$nextStart = $semesters[$index + 1]['semester_Start'] ?? null;
		if ($nextStart !== null) {
			$ranges[] = [
				'semester_ID' => (int) $semester['semester_ID'],
				'name' => (string) $semester['semester_Name'],
				'start' => (string) $semester['semester_Start'],
				'end_exclusive' => (string) $nextStart,
			];
		}
	}
	return $ranges;
}

function advising_slot_is_in_active_window(PDO $pdo, int $facultyId, int $semesterId, DateTimeImmutable $slot): bool
{
	$end = $slot->modify('+' . advising_appointment_duration_minutes() . ' minutes');
	$row = one(
		$pdo,
		"SELECT 1
		 FROM Faculty_Availability fa
		 JOIN Day d ON d.day_ID = fa.day_ID
		 WHERE fa.faculty_ID = ?
		   AND fa.semester_ID = ?
		   AND fa.is_active = 1
		   AND d.day_Of_Week = ?
		   AND fa.start_Time <= ?
		   AND fa.end_Time >= ?
		 LIMIT 1",
		[$facultyId, $semesterId, $slot->format('l'), $slot->format('H:i:s'), $end->format('H:i:s')]
	);
	return $row !== null;
}

/** Return the first non-cancelled teaching assignment overlapping a window. */
function advising_teaching_conflict(PDO $pdo, int $facultyId, int $semesterId, int $dayId, string $start, string $end): ?array
{
	return one(
		$pdo,
		"SELECT cs.CRN, c.course_ID, c.course_Name, d.day_Of_Week, p.start_Time, p.end_Time
		 FROM Course_Section cs
		 JOIN Course c ON c.course_ID = cs.course_ID
		 JOIN Time_Slot_Day tsd ON tsd.time_Slot_ID = cs.time_Slot_ID
		 JOIN Time_Slot_Period tsp ON tsp.time_Slot_ID = cs.time_Slot_ID
		 JOIN Period p ON p.period_ID = tsp.period_ID
		 JOIN Day d ON d.day_ID = tsd.day_ID
		 WHERE cs.faculty_ID = ? AND cs.semester_ID = ? AND cs.is_Cancelled = 0
		   AND d.day_ID = ? AND p.start_Time < ? AND p.end_Time > ?
		 ORDER BY p.start_Time, cs.CRN LIMIT 1",
		[$facultyId, $semesterId, $dayId, $end, $start]
	);
}

function advising_teaching_conflict_message(array $conflict): string
{
	return sprintf(
		'%s availability conflicts with %s (CRN %s), %s %s–%s.',
		(string) $conflict['day_Of_Week'],
		(string) $conflict['course_Name'],
		(string) $conflict['CRN'],
		(string) $conflict['day_Of_Week'],
		date('g:i A', strtotime((string) $conflict['start_Time'])),
		date('g:i A', strtotime((string) $conflict['end_Time']))
	);
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

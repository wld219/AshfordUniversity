<?php

declare(strict_types=1);

// Time-slot label helpers and the overlap primitives.

// Convert "HH:MM" or "HH:MM:SS" to minutes after midnight (null if invalid).
function time_to_minutes(string $time): ?int
{
	if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', trim($time), $m) !== 1) {
		return null;
	}

	$hours = (int) $m[1];
	$minutes = (int) $m[2];

	if ($hours > 23 || $minutes > 59) {
		return null;
	}

	return $hours * 60 + $minutes;
}

/**
 * Standard interval overlap rule:
 *   newStart < existingEnd && existingStart < newEnd
 *
 * Back-to-back classes (one ends exactly when the next begins) do NOT
 * overlap. Invalid intervals never overlap.
 */
function times_overlap(
	string $newStart,
	string $newEnd,
	string $existingStart,
	string $existingEnd
): bool {

	$ns = time_to_minutes($newStart);
	$ne = time_to_minutes($newEnd);
	$es = time_to_minutes($existingStart);
	$ee = time_to_minutes($existingEnd);

	if ($ns === null || $ne === null || $es === null || $ee === null) {
		return false;
	}

	// An interval must have positive length
	if ($ns >= $ne || $es >= $ee) {
		return false;
	}

	return $ns < $ee && $es < $ne;
}

// Do two lists of meeting days intersect?
function days_overlap(array $newDays, array $existingDays): bool
{
	return count(array_intersect($newDays, $existingDays)) > 0;
}

// Human-readable label for a time slot, e.g. "Mon, Wed 09:00-09:50"
function time_slot_label(
	PDO $pdo,
	int $timeSlotID
): string {

	if ($timeSlotID <= 0) {
		return 'To be announced';
	}

	$days = all_rows(
		$pdo,
		"SELECT d.day_Of_Week
		FROM Time_Slot_Day tsd
		JOIN Day d ON d.day_ID = tsd.day_ID
		WHERE tsd.time_Slot_ID = ?
		ORDER BY d.day_ID",
		[$timeSlotID]
	);

	$periods = all_rows(
		$pdo,
		"SELECT p.start_Time, p.end_Time
		FROM Time_Slot_Period tsp
		JOIN Period p ON p.period_ID = tsp.period_ID
		WHERE tsp.time_Slot_ID = ?
		ORDER BY p.period_ID",
		[$timeSlotID]
	);

	$dayNames = array_map(
		static fn(array $day): string => substr((string) $day['day_Of_Week'], 0, 3),
		$days
	);

	if (count($periods) === 0) {
		return count($dayNames) > 0
			? implode(', ', $dayNames) . ' (time TBA)'
			: 'To be announced';
	}

	$first = $periods[0];
	$last = $periods[count($periods) - 1];

	$start = substr((string) $first['start_Time'], 0, 5);
	$end = substr((string) $last['end_Time'], 0, 5);

	$label = count($dayNames) > 0
		? implode(', ', $dayNames) . ' '
		: '';

	return $label . $start . '-' . $end;
}

// Location label for a section, e.g. "Recital Hall 1101 (Lecture)"
function section_room_label(PDO $pdo, ?int $lectureID, ?int $labID): string
{
	if ($lectureID !== null) {

		$room = one(
			$pdo,
			"SELECT r.room_Number, b.Bldg_Name
			FROM Room r
			JOIN Building b ON b.Bldg_ID = r.Bldg_ID
			WHERE r.room_ID = ?",
			[$lectureID]
		);

		if ($room) {
			return $room['Bldg_Name'] . ' ' . $room['room_Number'] . ' (Lecture)';
		}
	}

	if ($labID !== null) {

		$room = one(
			$pdo,
			"SELECT r.room_Number, b.Bldg_Name
			FROM Room r
			JOIN Building b ON b.Bldg_ID = r.Bldg_ID
			WHERE r.room_ID = ?",
			[$labID]
		);

		if ($room) {
			return $room['Bldg_Name'] . ' ' . $room['room_Number'] . ' (Lab)';
		}
	}

	return 'To be announced';
}

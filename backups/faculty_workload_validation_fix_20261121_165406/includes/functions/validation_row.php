<?php

declare(strict_types=1);

// Per-section validation of one enriched schedule row.

/**
 * @param array<string, mixed> $row
 * @return array<int, string> validation messages (empty when valid)
 */
function validate_schedule_row(array $row): array
{
	$errors = [];

	// Missing room assignment
	if ($row['room_Key'] === null) {
		$errors[] = 'No room assigned (lecture room or lab required).';
	}

	// Missing faculty assignment
	if ((int) $row['faculty_ID'] <= 0) {
		$errors[] = 'No faculty assigned.';
	}

	// Faculty must belong to the course's department
	if (
		(int) $row['faculty_ID'] > 0 &&
		!in_array((int) $row['dept_ID'], $row['faculty_Departments'] ?? [], true)
	) {
		$errors[] = 'Faculty member is not in the course department.';
	}

	// Faculty workload rules
	if (($row['faculty_Type'] ?? '') === 'Full-Time' && (int) ($row['faculty_Section_Count'] ?? 0) > 2) {
		$errors[] = 'Full-time faculty exceeds the two-section teaching limit.';
	}

	if (($row['faculty_Type'] ?? '') === 'Part-Time' && (int) ($row['faculty_Section_Count'] ?? 0) > 1) {
		$errors[] = 'Part-time faculty exceeds the single-section teaching limit.';
	}

	// Room capacity: available seats must fit the room
	if (
		$row['room_Key'] !== null &&
		(int) ($row['enrolled'] ?? 0) > (int) ($row['room_Capacity'] ?? 0)
	) {
		$errors[] = 'Enrollment exceeds physical room capacity ('
			. (int) ($row['room_Capacity'] ?? 0) . ').';
	}

	if ((int)($row['enrolled'] ?? 0) > 10) $errors[] = 'Enrollment exceeds 10-student section limit.';

	// Meeting times must exist and be a positive interval
	if (empty($row['start_Time']) || empty($row['end_Time'])) {
		$errors[] = 'No meeting time defined for the assigned time slot.';
	} elseif (time_to_minutes((string) $row['start_Time']) >= time_to_minutes((string) $row['end_Time'])) {
		$errors[] = 'Meeting time ends before it starts.';
	}

	return $errors;
}

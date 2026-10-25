<?php

declare(strict_types=1);

// Semester-level validation: statistics plus per-section issues.

/**
 * @return array{rows: array, stats: array, room_Conflicts: array,
 *               faculty_Conflicts: array, issues: array, valid: bool}
 */
function validate_semester_schedule(PDO $pdo, int $semesterID): array
{
	$rows = schedule_section_rows($pdo, $semesterID);

	$stats = [
		'total_Sections' => count($rows),
		'scheduled_Sections' => 0,
		'unscheduled_Sections' => 0,
		'missing_Room' => 0,
		'missing_Faculty' => 0,
		'room_Conflicts' => 0,
		'faculty_Conflicts' => 0,
		'validation_Errors' => 0,
	];

	$roomConflicts = [];
	$facultyConflicts = [];
	$issues = [];

	// Per-section checks
	foreach ($rows as $row) {

		$errors = validate_schedule_row($row);

		if ($row['room_Key'] === null) {
			$stats['missing_Room']++;
		}

		if ((int) $row['faculty_ID'] <= 0) {
			$stats['missing_Faculty']++;
		}

		if (count($errors) === 0) {
			$stats['scheduled_Sections']++;
		} else {
			$stats['unscheduled_Sections']++;
			$stats['validation_Errors'] += count($errors);

			foreach ($errors as $error) {
				$issues[] = [
					'CRN' => (int) $row['CRN'],
					'course_ID' => (string) $row['course_ID'],
					'course_Name' => (string) $row['course_Name'],
					'type' => 'section',
					'message' => $error,
				];
			}
		}
	}

	// Room conflicts: same room, overlapping days and times
	for ($i = 0; $i < count($rows); $i++) {

		for ($j = $i + 1; $j < count($rows); $j++) {

			$a = $rows[$i];
			$b = $rows[$j];

			if ($a['room_Key'] === null || $a['room_Key'] !== $b['room_Key']) {
				continue;
			}

			if (
				days_overlap($a['days'], $b['days']) &&
				times_overlap(
					(string) $a['start_Time'],
					(string) $a['end_Time'],
					(string) $b['start_Time'],
					(string) $b['end_Time']
				)
			) {
				$roomConflicts[] = ['a' => (int) $a['CRN'], 'b' => (int) $b['CRN']];
			}
		}
	}

	// Faculty conflicts: same faculty member, overlapping days and times
	for ($i = 0; $i < count($rows); $i++) {

		for ($j = $i + 1; $j < count($rows); $j++) {

			$a = $rows[$i];
			$b = $rows[$j];

			if ((int) $a['faculty_ID'] !== (int) $b['faculty_ID']) {
				continue;
			}

			if (
				days_overlap($a['days'], $b['days']) &&
				times_overlap(
					(string) $a['start_Time'],
					(string) $a['end_Time'],
					(string) $b['start_Time'],
					(string) $b['end_Time']
				)
			) {
				$facultyConflicts[] = ['a' => (int) $a['CRN'], 'b' => (int) $b['CRN']];
			}
		}
	}

	$stats['room_Conflicts'] = count($roomConflicts);
	$stats['faculty_Conflicts'] = count($facultyConflicts);

	foreach ($roomConflicts as $conflict) {
		$issues[] = [
			'CRN' => $conflict['a'],
			'type' => 'room',
			'message' => 'Room conflict with CRN ' . $conflict['b'] . '.',
		];
	}

	foreach ($facultyConflicts as $conflict) {
		$issues[] = [
			'CRN' => $conflict['a'],
			'type' => 'faculty',
			'message' => 'Faculty conflict with CRN ' . $conflict['b'] . '.',
		];
	}

	return [
		'rows' => $rows,
		'stats' => $stats,
		'room_Conflicts' => $roomConflicts,
		'faculty_Conflicts' => $facultyConflicts,
		'issues' => $issues,
		'valid' => count($issues) === 0,
	];
}

<?php

declare(strict_types=1);

// Master schedule data loading.

/**
 * Load every non-cancelled section of a semester for validation, with
 * department, faculty, room, meeting days, times, and capacity attached.
 *
 * @return array<int, array<string, mixed>>
 */
function schedule_section_rows(PDO $pdo, int $semesterID): array
{
	$sections = all_rows(
		$pdo,
		"SELECT
			cs.CRN,
			cs.section_No,
			cs.course_ID,
			c.course_Name,
			c.course_Credits,
			c.course_Type,
			c.dept_ID,
			d.dept_Name,
			cs.faculty_ID,
			CONCAT(u.first_Name, ' ', u.last_Name) AS faculty_Name,
			f.faculty_Type,
			cs.time_Slot_ID,
			cs.lecture_ID,
			cs.lab_ID,
			cs.available_Seats,
			cs.is_Cancelled,
			COALESCE(l.avail_Seats, b.no_Of_Workstations, 0) AS capacity,
			(SELECT COUNT(*) FROM Enrollment e WHERE e.CRN = cs.CRN) AS enrolled
		FROM Course_Section cs
		JOIN Course c ON c.course_ID = cs.course_ID
		JOIN Department d ON d.dept_ID = c.dept_ID
		JOIN User u ON u.user_ID = cs.faculty_ID
		JOIN Faculty f ON f.faculty_ID = cs.faculty_ID
		LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
		LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID
		WHERE cs.semester_ID = ?
			AND cs.is_Cancelled = 0
		ORDER BY d.dept_Name, c.course_ID, cs.section_No",
		[$semesterID]
	);

	if (count($sections) === 0) {
		return [];
	}

	// Meeting days and times come from the shared time-slot tables
	$dayMap = [];

	foreach (all_rows(
		$pdo,
		"SELECT tsd.time_Slot_ID, dy.day_Of_Week
		FROM Time_Slot_Day tsd
		JOIN Day dy ON dy.day_ID = tsd.day_ID"
	) as $row) {
		$dayMap[(int) $row['time_Slot_ID']][] = (string) $row['day_Of_Week'];
	}

	$periodMap = [];

	foreach (all_rows(
		$pdo,
		"SELECT tsp.time_Slot_ID,
				MIN(p.start_Time) AS start_Time,
				MAX(p.end_Time) AS end_Time
		FROM Time_Slot_Period tsp
		JOIN Period p ON p.period_ID = tsp.period_ID
		GROUP BY tsp.time_Slot_ID"
	) as $row) {
		$periodMap[(int) $row['time_Slot_ID']] = [
			'start_Time' => (string) $row['start_Time'],
			'end_Time' => (string) $row['end_Time'],
		];
	}

	// Faculty departments (faculty member -> course department authorization)
	$membershipMap = [];

	foreach (all_rows(
		$pdo,
		'SELECT faculty_ID, dept_ID FROM Faculty_Department'
	) as $row) {
		$membershipMap[(int) $row['faculty_ID']][] = (int) $row['dept_ID'];
	}

	// Section counts per faculty member (workload validation)
	$countMap = [];

	foreach (all_rows(
		$pdo,
		"SELECT faculty_ID, COUNT(*) AS total
		FROM Course_Section
		WHERE semester_ID = ?
			AND is_Cancelled = 0
		GROUP BY faculty_ID",
		[$semesterID]
	) as $row) {
		$countMap[(int) $row['faculty_ID']] = (int) $row['total'];
	}

	foreach ($sections as &$section) {

		$slotID = (int) $section['time_Slot_ID'];

		$section['days'] = $dayMap[$slotID] ?? [];
		$section['start_Time'] = $periodMap[$slotID]['start_Time'] ?? null;
		$section['end_Time'] = $periodMap[$slotID]['end_Time'] ?? null;
		$section['faculty_Departments'] = $membershipMap[(int) $section['faculty_ID']] ?? [];
		$section['faculty_Section_Count'] = $countMap[(int) $section['faculty_ID']] ?? 0;

		// Room key identifies the physical room used by the section
		if ($section['lecture_ID'] !== null) {
			$section['room_Key'] = 'lecture-' . (int) $section['lecture_ID'];
			$section['room_Label'] = 'Lecture room ' . (int) $section['lecture_ID'];
		} elseif ($section['lab_ID'] !== null) {
			$section['room_Key'] = 'lab-' . (int) $section['lab_ID'];
			$section['room_Label'] = 'Lab ' . (int) $section['lab_ID'];
		} else {
			$section['room_Key'] = null;
			$section['room_Label'] = null;
		}
	}

	unset($section);

	return $sections;
}

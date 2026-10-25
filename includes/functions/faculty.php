<?php

declare(strict_types=1);

// Faculty workload rules (specification)
//   - Full-time faculty teach at most two sections.
//   - Part-time faculty teach exactly one section.
//   - Full-time faculty may not teach two overlapping sections.
//   - Full-time faculty belong to at most three departments.
//   - Part-time faculty belong to exactly one department.
//   - Full-time faculty may have at most 15 advisees.
//   - Part-time faculty may not have advisees.
//   - Faculty may only be assigned to sections in their departments.

// Does this time slot overlap another slot for the given faculty member?
function faculty_slot_overlaps(
	PDO $pdo,
	int $facultyID,
	int $timeSlotID,
	?int $ignoreCRN = null
): bool {

	$sql = "SELECT 1
		FROM Course_Section cs
		JOIN Time_Slot_Day my_day
			ON my_day.time_Slot_ID = cs.time_Slot_ID
		JOIN Time_Slot_Day new_day
			ON new_day.day_ID = my_day.day_ID
			AND new_day.time_Slot_ID = :slot
		WHERE cs.faculty_ID = :faculty
			AND cs.is_Cancelled = 0
			AND (:ignore IS NULL OR cs.CRN <> :ignore2)
			AND EXISTS (
				SELECT 1
				FROM Time_Slot_Period my_period
				JOIN Period my_time
					ON my_time.period_ID = my_period.period_ID
				JOIN Time_Slot_Period new_period
					ON new_period.time_Slot_ID = :slot2
				JOIN Period new_time
					ON new_time.period_ID = new_period.period_ID
				WHERE my_period.time_Slot_ID = cs.time_Slot_ID
					AND my_time.start_Time < new_time.end_Time
					AND new_time.start_Time < my_time.end_Time
			)
		LIMIT 1";

	$stmt = $pdo->prepare($sql);

	$stmt->execute([
		'slot' => $timeSlotID,
		'ignore' => $ignoreCRN,
		'faculty' => $facultyID,
		'slot2' => $timeSlotID,
		'ignore2' => $ignoreCRN,
	]);

	return $stmt->fetch() !== false;
}

// Can this faculty member be assigned to teach a section at this time slot?
function faculty_can_teach_section(
	PDO $pdo,
	int $facultyID,
	int $timeSlotID,
	?int $ignoreCRN = null
): array {

	$faculty = one(
		$pdo,
		'SELECT faculty_Type FROM Faculty WHERE faculty_ID = ?',
		[$facultyID]
	);

	if (!$faculty) {
		return [false, 'Faculty record not found.'];
	}

	$isFullTime = $faculty['faculty_Type'] === 'Full-Time';

	$sql = "SELECT COUNT(*) AS total
		FROM Course_Section
		WHERE faculty_ID = ?
			AND is_Cancelled = 0";

	$params = [$facultyID];

	if ($ignoreCRN !== null) {
		$sql .= ' AND CRN <> ?';
		$params[] = $ignoreCRN;
	}

	$current = (int) (one($pdo, $sql, $params)['total'] ?? 0);

	if ($isFullTime && $current >= 2) {
		return [
			false,
			'Full-time faculty may teach at most two sections.'
		];
	}

	// Part-time faculty teach exactly one section, so a second assignment
	// is refused. The "teaches none at all" side is a data report, not a
	// create-time gate, because historical assignments cannot be invented.
	if (!$isFullTime && $current >= 1) {
		return [
			false,
			'Part-time faculty may teach exactly one section.'
		];
	}

	// Full-time faculty may not teach two overlapping sections
	if ($isFullTime && faculty_slot_overlaps($pdo, $facultyID, $timeSlotID, $ignoreCRN)) {
		return [
			false,
			'Full-time faculty may not be assigned to overlapping sections.'
		];
	}

	return [true, 'OK'];
}

// Is this faculty member assigned to a department that owns the course?
function faculty_teaches_department(PDO $pdo, int $facultyID, int $courseID): bool
{
	$deptOfCourse = one(
		$pdo,
		'SELECT dept_ID FROM Course WHERE course_ID = ?',
		[$courseID]
	);

	if (!$deptOfCourse) {
		return false;
	}

	$membership = one(
		$pdo,
		'SELECT 1 FROM Faculty_Department
		WHERE faculty_ID = ? AND dept_ID = ?',
		[$facultyID, (int) $deptOfCourse['dept_ID']]
	);

	return $membership !== null;
}

// Can this faculty member take another advisee?
function faculty_can_have_advisee(PDO $pdo, int $facultyID): array
{
	$faculty = one(
		$pdo,
		'SELECT faculty_Type FROM Faculty WHERE faculty_ID = ?',
		[$facultyID]
	);

	if (!$faculty) {
		return [false, 'Faculty record not found.'];
	}

	if ($faculty['faculty_Type'] !== 'Full-Time') {
		return [false, 'Part-time faculty may not have advisees.'];
	}

	$current = (int) (one(
		$pdo,
		'SELECT COUNT(*) AS total FROM Advisor WHERE faculty_ID = ?',
		[$facultyID]
	)['total'] ?? 0);

	if ($current >= 15) {
		return [false, 'Full-time faculty may have at most 15 advisees.'];
	}

	return [true, 'OK'];
}

// Department rules (enforced on assignment):
//   - Full-time faculty may belong to at most three departments.
//   - Part-time faculty may belong to exactly one department.
function faculty_can_join_department(
	PDO $pdo,
	int $facultyID,
	?int $departmentID = null
): array {

	$faculty = one(
		$pdo,
		'SELECT faculty_Type FROM Faculty WHERE faculty_ID = ?',
		[$facultyID]
	);

	if (!$faculty) {
		return [false, 'Faculty record not found.'];
	}

	$isFullTime = $faculty['faculty_Type'] === 'Full-Time';

	$sql = 'SELECT COUNT(*) AS total FROM Faculty_Department WHERE faculty_ID = ?';

	$params = [$facultyID];

	if ($departmentID !== null) {
		$sql .= ' AND dept_ID <> ?';
		$params[] = $departmentID;
	}

	$current = (int) (one($pdo, $sql, $params)['total'] ?? 0);

	// Joining a department already held is a no-op.
	if ($departmentID !== null && $current === 0) {

		$already = one(
			$pdo,
			'SELECT 1 FROM Faculty_Department WHERE faculty_ID = ? AND dept_ID = ?',
			[$facultyID, $departmentID]
		);

		if ($already !== null) {
			return [true, 'OK'];
		}
	}

	if ($isFullTime && $current >= 3) {
		return [false, 'Full-time faculty may belong to at most three departments.'];
	}

	if (!$isFullTime && $current >= 1) {
		return [false, 'Part-time faculty may belong to only one department.'];
	}

	return [true, 'OK'];
}

/*
 * Report assignments that already break a faculty rule.
 *
 * The specification requires inconsistencies to be reported rather than
 * silently corrected, because historical records must not be rewritten.
 *
 * Each returned message names the faculty member, the rule, and what was
 * found. Use faculty_rule_summary() to group them for display.
 *
 * @return array<int, string>
 */
function faculty_rule_violations(PDO $pdo): array
{
	$violations = [];

	$faculty = all_rows(
		$pdo,
		"SELECT f.faculty_ID, f.faculty_Type,
			CONCAT(u.first_Name, ' ', u.last_Name) AS faculty_Name
		FROM Faculty f
		JOIN User u ON u.user_ID = f.faculty_ID
		ORDER BY f.faculty_ID"
	);

	foreach ($faculty as $member) {

		$facultyID = (int) $member['faculty_ID'];
		$name = (string) $member['faculty_Name'];
		$isFullTime = $member['faculty_Type'] === 'Full-Time';

		// Teaching load.
		$sectionCount = (int) (one(
			$pdo,
			'SELECT COUNT(*) AS total FROM Course_Section
			WHERE faculty_ID = ? AND is_Cancelled = 0',
			[$facultyID]
		)['total'] ?? 0);

		if ($isFullTime && $sectionCount > 2) {
			$violations[] = "{$name} (ID {$facultyID}) teaches {$sectionCount} sections; full-time faculty may teach at most two.";
		}

		if (!$isFullTime && $sectionCount !== 1) {
			$violations[] = "{$name} (ID {$facultyID}) teaches {$sectionCount} sections; part-time faculty must teach exactly one.";
		}

		// Overlapping meetings for full-time faculty.
		if ($isFullTime) {

			$overlaps = all_rows(
				$pdo,
				"SELECT a.CRN AS crn_a, b.CRN AS crn_b
				FROM Course_Section a
				JOIN Course_Section b
					ON b.faculty_ID = a.faculty_ID
					AND b.CRN > a.CRN
					AND b.is_Cancelled = 0
				JOIN Time_Slot_Day day_a
					ON day_a.time_Slot_ID = a.time_Slot_ID
				JOIN Time_Slot_Day day_b
					ON day_b.time_Slot_ID = b.time_Slot_ID
					AND day_b.day_ID = day_a.day_ID
				JOIN Time_Slot_Period period_a
					ON period_a.time_Slot_ID = a.time_Slot_ID
				JOIN Period time_a
					ON time_a.period_ID = period_a.period_ID
				JOIN Time_Slot_Period period_b
					ON period_b.time_Slot_ID = b.time_Slot_ID
				JOIN Period time_b
					ON time_b.period_ID = period_b.period_ID
				WHERE a.faculty_ID = ?
					AND a.is_Cancelled = 0
					AND time_a.start_Time < time_b.end_Time
					AND time_b.start_Time < time_a.end_Time",
				[$facultyID]
			);

			foreach ($overlaps as $overlap) {
				$violations[] = "{$name} (ID {$facultyID}) teaches overlapping sections {$overlap['crn_a']} and {$overlap['crn_b']}.";
			}
		}

		// Department membership.
		$deptCount = (int) (one(
			$pdo,
			'SELECT COUNT(*) AS total FROM Faculty_Department WHERE faculty_ID = ?',
			[$facultyID]
		)['total'] ?? 0);

		if ($isFullTime && $deptCount > 3) {
			$violations[] = "{$name} (ID {$facultyID}) belongs to {$deptCount} departments; full-time faculty may belong to at most three.";
		}

		if (!$isFullTime && $deptCount !== 1) {
			$violations[] = "{$name} (ID {$facultyID}) belongs to {$deptCount} departments; part-time faculty may belong to only one.";
		}

		// Advisee load.
		$adviseeCount = (int) (one(
			$pdo,
			'SELECT COUNT(*) AS total FROM Advisor WHERE faculty_ID = ?',
			[$facultyID]
		)['total'] ?? 0);

		if (!$isFullTime && $adviseeCount > 0) {
			$violations[] = "{$name} (ID {$facultyID}) has {$adviseeCount} advisee(s); part-time faculty may not have advisees.";
		}

		if ($isFullTime && $adviseeCount > 15) {
			$violations[] = "{$name} (ID {$facultyID}) has {$adviseeCount} advisees; full-time faculty may have at most 15.";
		}

		// Teaching outside the faculty member's own departments.
		$outside = all_rows(
			$pdo,
			"SELECT cs.CRN, c.course_Name
			FROM Course_Section cs
			JOIN Course c ON c.course_ID = cs.course_ID
			WHERE cs.faculty_ID = ?
				AND cs.is_Cancelled = 0
				AND NOT EXISTS (
					SELECT 1 FROM Faculty_Department fd
					WHERE fd.faculty_ID = cs.faculty_ID
					AND fd.dept_ID = c.dept_ID
				)",
			[$facultyID]
		);

		foreach ($outside as $row) {
			$violations[] = "{$name} (ID {$facultyID}) teaches {$row['course_Name']} (CRN {$row['CRN']}) outside their own departments.";
		}
	}

	return $violations;
}

/*
 * Group rule violations by the rule they break, so a large report stays
 * readable: [readable rule label => count].
 *
 * @param array<int, string> $violations Output of faculty_rule_violations()
 * @return array<string, int>
 */
function faculty_rule_summary(array $violations): array
{
	$labels = [
		'at most two' => 'Full-time faculty teaching more than two sections',
		'exactly one' => 'Part-time faculty not teaching exactly one section',
		'overlapping' => 'Full-time faculty teaching overlapping sections',
		'at most three' => 'Full-time faculty in more than three departments',
		'only one department' => 'Part-time faculty in other than one department',
		'may not have advisees' => 'Part-time faculty holding advisees',
		'at most 15' => 'Full-time faculty holding more than 15 advisees',
		'outside their own departments' => 'Teaching outside their own departments',
	];

	$summary = [];

	foreach ($violations as $violation) {

		$matched = false;

		foreach ($labels as $needle => $label) {

			if (str_contains($violation, $needle)) {
				$summary[$label] = ($summary[$label] ?? 0) + 1;
				$matched = true;
				break;
			}
		}

		if (!$matched) {
			$summary['Other'] = ($summary['Other'] ?? 0) + 1;
		}
	}

	arsort($summary);

	return $summary;
}

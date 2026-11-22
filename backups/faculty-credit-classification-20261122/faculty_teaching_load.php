<?php

declare(strict_types=1);

/** Return every department declared for a faculty member. */
function faculty_departments(PDO $pdo, int $facultyId): array
{
	return all_rows(
		$pdo,
		' SELECT d.dept_ID, d.dept_Name, fd.percent_Time, fd.date_Of_Appointment
		  FROM Faculty_Department fd
		  JOIN Department d ON d.dept_ID = fd.dept_ID
		  WHERE fd.faculty_ID = ?
		  ORDER BY d.dept_Name, d.dept_ID',
		[$facultyId]
	);
}

/**
 * Return the catalog courses in each department assigned to this faculty
 * member. Department membership is many-to-many; no primary department is
 * inferred when the schema does not identify one.
 */
function faculty_department_course_groups(PDO $pdo, int $facultyId): array
{
	$rows = all_rows(
		$pdo,
		' SELECT DISTINCT d.dept_ID, d.dept_Name, c.course_ID, c.course_Name, c.course_Credits
		  FROM Faculty_Department fd
		  JOIN Department d ON d.dept_ID = fd.dept_ID
		  LEFT JOIN Course c ON c.dept_ID = d.dept_ID
		  WHERE fd.faculty_ID = ?
		  ORDER BY d.dept_Name, c.course_ID',
		[$facultyId]
	);

	$groups = [];
	foreach ($rows as $row) {
		$departmentId = (int) $row['dept_ID'];
		if (!isset($groups[$departmentId])) {
			$groups[$departmentId] = [
				'dept_ID' => $departmentId,
				'dept_Name' => (string) $row['dept_Name'],
				'courses' => [],
			];
		}
		if ($row['course_ID'] !== null) {
			$groups[$departmentId]['courses'][] = [
				'course_ID' => (string) $row['course_ID'],
				'course_Name' => (string) $row['course_Name'],
				'course_Credits' => (int) $row['course_Credits'],
			];
		}
	}

	return array_values($groups);
}

/** Return one active assigned section per CRN for a faculty member and term. */
function faculty_teaching_sections(PDO $pdo, int $facultyId, int $semesterId): array
{
	$sections = all_rows(
		$pdo,
		' SELECT DISTINCT cs.CRN, cs.course_ID, c.course_Name, c.course_Credits,
				cs.section_No, cs.available_Seats, cs.time_Slot_ID, cs.semester_ID,
				s.semester_Name, d.dept_Name AS course_Department
		  FROM Course_Section cs
		  JOIN Course c ON c.course_ID = cs.course_ID
		  JOIN Department d ON d.dept_ID = c.dept_ID
		  JOIN Semester s ON s.semester_ID = cs.semester_ID
		  WHERE cs.faculty_ID = ? AND cs.semester_ID = ? AND cs.is_Cancelled = 0
		  ORDER BY course_ID, section_No, CRN',
		[$facultyId, $semesterId]
	);

	$labelsByTimeSlot = [];
	$uniqueSections = [];
	foreach ($sections as $section) {
		$crn = (int) $section['CRN'];
		if (isset($uniqueSections[$crn])) {
			continue;
		}
		$uniqueSections[$crn] = $section;
		$timeSlotId = (int) $section['time_Slot_ID'];
		if (!array_key_exists($timeSlotId, $labelsByTimeSlot)) {
			$labelsByTimeSlot[$timeSlotId] = time_slot_label($pdo, $timeSlotId);
		}
		$uniqueSections[$crn]['meeting_Schedule'] = $labelsByTimeSlot[$timeSlotId];
	}

	return array_values($uniqueSections);
}

/**
 * Sum unique assigned section credits. Workload classification is a display
 * calculation and deliberately does not read or change Faculty.faculty_Type.
 */
function faculty_teaching_workload_summary(array $sections): array
{
	$uniqueSections = [];
	$uniqueCourses = [];
	$totalCredits = 0;
	foreach ($sections as $section) {
		$crn = (int) ($section['CRN'] ?? 0);
		if ($crn <= 0 || isset($uniqueSections[$crn])) {
			continue;
		}
		$uniqueSections[$crn] = true;
		$courseId = (string) ($section['course_ID'] ?? '');
		if ($courseId !== '') {
			$uniqueCourses[$courseId] = true;
		}
		$credits = (int) ($section['course_Credits'] ?? 0);
		if ($credits > 0) {
			$totalCredits += $credits;
		}
	}

	return [
		'assigned_sections' => count($uniqueSections),
		'course_count' => count($uniqueCourses),
		'teaching_credits' => $totalCredits,
		'status' => faculty_teaching_workload_status($totalCredits),
	];
}

/**
 * Return the calculated teaching workload for every Faculty record in one
 * semester. Sections are grouped by CRN before summing so an accidental join
 * duplicate cannot inflate credits. This never reads or changes faculty_Type.
 */
function faculty_workload_directory(PDO $pdo, int $semesterId): array
{
	$rows = all_rows(
		$pdo,
		' SELECT f.faculty_ID,
				COALESCE(SUM(assigned.course_Credits), 0) AS teaching_credits,
				COUNT(assigned.CRN) AS assigned_sections
		  FROM Faculty f
		  LEFT JOIN (
			  SELECT cs.faculty_ID, cs.CRN, MAX(c.course_Credits) AS course_Credits
			  FROM Course_Section cs
			  JOIN Course c ON c.course_ID = cs.course_ID
			  WHERE cs.semester_ID = ? AND cs.is_Cancelled = 0
			  GROUP BY cs.faculty_ID, cs.CRN
		  ) assigned ON assigned.faculty_ID = f.faculty_ID
		  GROUP BY f.faculty_ID
		  ORDER BY f.faculty_ID',
		[$semesterId]
	);

	$byFaculty = [];
	$counts = ['Full-Time (FT)' => 0, 'Part-Time (PT)' => 0];
	foreach ($rows as $row) {
		$credits = (int) $row['teaching_credits'];
		$status = faculty_teaching_workload_status($credits);
		$facultyId = (int) $row['faculty_ID'];
		$byFaculty[$facultyId] = [
			'faculty_ID' => $facultyId,
			'teaching_credits' => $credits,
			'assigned_sections' => (int) $row['assigned_sections'],
			'status' => $status,
		];
		$counts[$status]++;
	}

	return [
		'by_faculty' => $byFaculty,
		'faculty_count' => count($byFaculty),
		'full_time_count' => $counts['Full-Time (FT)'],
		'part_time_count' => $counts['Part-Time (PT)'],
	];
}

function faculty_teaching_workload_status(int $credits): string
{
	return $credits >= 12 ? 'Full-Time (FT)' : 'Part-Time (PT)';
}

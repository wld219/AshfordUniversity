<?php

declare(strict_types=1);

/**
 * Read-only Fall 2026 undergraduate enrollment audit and Chunk 2 planning aid.
 * This script executes SELECT statements only. It never changes live records.
 * Run from any directory: php scripts/plan_fall2026_enrollment_chunk1.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$databaseName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
if ($databaseName !== 'ashford_waleed') {
	fwrite(STDERR, "Expected ashford_waleed; refusing to create an audit plan for {$databaseName}.\n");
	exit(2);
}

$semesterID = 1;
$semester = one($pdo, 'SELECT semester_Name FROM Semester WHERE semester_ID = ?', [$semesterID]);
if (($semester['semester_Name'] ?? null) !== 'Fall 2026') {
	fwrite(STDERR, "Semester ID 1 is not Fall 2026; no report generated.\n");
	exit(2);
}

$reportDirectory = __DIR__ . '/../reports';
if (!is_dir($reportDirectory) && !mkdir($reportDirectory, 0750, true) && !is_dir($reportDirectory)) {
	fwrite(STDERR, "Unable to create reports directory.\n");
	exit(2);
}

$students = all_rows(
	$pdo,
	"SELECT student_ID, major_ID, student_Year, student_Type
	 FROM Student
	 WHERE student_Type = 'Undergraduate'
	 ORDER BY student_ID"
);

$profiles = [];
foreach ($students as $student) {
	$id = (int) $student['student_ID'];
	$profiles[$id] = [
		'id' => $id,
		'primary_major_id' => $student['major_ID'] === null ? '' : (int) $student['major_ID'],
		'year' => (int) $student['student_Year'],
		'majors' => [],
		'minors' => [],
		'holds' => 0,
		'courses' => [],
		'course_rows' => [],
		'credits' => 0,
		'cross_level' => 0,
		'invalid_enrollments' => 0,
		'duplicate_groups' => 0,
		'conflicts' => 0,
		'active_sections' => [],
		'history' => [],
		'catalog_candidates' => [],
		'options' => [],
		'proposed' => null,
	];
	if ($student['major_ID'] !== null) {
		$profiles[$id]['majors'][(int) $student['major_ID']] = true;
	}
}

foreach (all_rows($pdo, 'SELECT student_ID, major_ID FROM Student_Major') as $row) {
	$id = (int) $row['student_ID'];
	if (isset($profiles[$id])) {
		$profiles[$id]['majors'][(int) $row['major_ID']] = true;
	}
}
foreach (all_rows($pdo, 'SELECT student_ID, minor_ID FROM Student_Minor') as $row) {
	$id = (int) $row['student_ID'];
	if (isset($profiles[$id])) {
		$profiles[$id]['minors'][(int) $row['minor_ID']] = true;
	}
}
foreach (all_rows($pdo, 'SELECT student_ID, COUNT(*) AS hold_count FROM Student_Hold GROUP BY student_ID') as $row) {
	$id = (int) $row['student_ID'];
	if (isset($profiles[$id])) {
		$profiles[$id]['holds'] = (int) $row['hold_count'];
	}
}

$majorRequirements = [];
foreach (all_rows($pdo, 'SELECT major_ID, course_ID, min_Grade_Req FROM Major_Requirement') as $row) {
	$majorRequirements[(int) $row['major_ID']][(string) $row['course_ID']] = (string) $row['min_Grade_Req'];
}
$minorRequirements = [];
foreach (all_rows($pdo, 'SELECT minor_ID, course_ID, min_Grade_Req FROM Minor_Requirement') as $row) {
	$minorRequirements[(int) $row['minor_ID']][(string) $row['course_ID']] = (string) $row['min_Grade_Req'];
}
$prerequisites = [];
foreach (all_rows($pdo, 'SELECT course_ID, prerequisite_course_ID, min_Grade_Req FROM Course_Prerequisite') as $row) {
	$prerequisites[(string) $row['course_ID']][] = [
		'course_ID' => (string) $row['prerequisite_course_ID'],
		'minimum' => (string) $row['min_Grade_Req'],
	];
}
$bestHistory = [];
foreach (all_rows($pdo, 'SELECT student_ID, course_ID, Grade FROM Student_History WHERE Grade IS NOT NULL') as $row) {
	$id = (int) $row['student_ID'];
	$courseID = (string) $row['course_ID'];
	$grade = (string) $row['Grade'];
	$points = grade_points($grade);
	if ($points === null) {
		continue;
	}
	if (!isset($bestHistory[$id][$courseID]) || $points > $bestHistory[$id][$courseID]['points']) {
		$bestHistory[$id][$courseID] = ['grade' => $grade, 'points' => $points];
	}
}

$sectionRows = schedule_section_rows($pdo, $semesterID);
$sectionByCRN = [];
$slotMap = [];
foreach ($sectionRows as $row) {
	$crn = (int) $row['CRN'];
	$sectionByCRN[$crn] = $row;
	$slotMap[(int) $row['time_Slot_ID']] = [
		'days' => $row['days'],
		'start' => $row['start_Time'] === null ? null : time_to_minutes((string) $row['start_Time']),
		'end' => $row['end_Time'] === null ? null : time_to_minutes((string) $row['end_Time']),
	];
}

// Include every term-1 Enrollment row so canceled, missing, or mismatched
// section links are visible and can conservatively block new recommendations.
foreach (all_rows(
	$pdo,
	"SELECT e.student_ID, e.CRN, e.semester_ID AS enrollment_semester,
	        cs.course_ID, cs.semester_ID AS section_semester, cs.time_Slot_ID,
	        cs.is_Cancelled, c.course_Credits, c.course_Type
	 FROM Enrollment e
	 LEFT JOIN Course_Section cs ON cs.CRN = e.CRN
	 LEFT JOIN Course c ON c.course_ID = cs.course_ID
	 JOIN Student st ON st.student_ID = e.student_ID AND st.student_Type = 'Undergraduate'
	 WHERE e.semester_ID = ?
	 ORDER BY e.student_ID, e.CRN",
	[$semesterID]
) as $row) {
	$id = (int) $row['student_ID'];
	if (!isset($profiles[$id])) {
		continue;
	}
	$profile =& $profiles[$id];
	if ($row['course_ID'] === null || (int) $row['section_semester'] !== $semesterID || (int) $row['is_Cancelled'] === 1) {
		$profile['invalid_enrollments']++;
		unset($profile);
		continue;
	}
	$courseID = (string) $row['course_ID'];
	$crn = (int) $row['CRN'];
	$profile['course_rows'][] = $courseID;
	$profile['courses'][$courseID] = true;
	$profile['credits'] += (int) $row['course_Credits'];
	if ((string) $row['course_Type'] !== 'Undergraduate') {
		$profile['cross_level']++;
	}
	$profile['active_sections'][] = [
		'CRN' => $crn,
		'course_ID' => $courseID,
		'credits' => (int) $row['course_Credits'],
		'course_Type' => (string) $row['course_Type'],
		'slot' => (int) $row['time_Slot_ID'],
	];
	unset($profile);
}

foreach ($profiles as &$profile) {
	$courseCounts = array_count_values($profile['course_rows']);
	$profile['duplicate_groups'] = count(array_filter($courseCounts, static fn(int $n): bool => $n > 1));
	$sections = $profile['active_sections'];
	for ($i = 0; $i < count($sections); $i++) {
		$a = $slotMap[$sections[$i]['slot']] ?? null;
		if (!$a || $a['start'] === null || $a['end'] === null) {
			continue;
		}
		for ($j = $i + 1; $j < count($sections); $j++) {
			$b = $slotMap[$sections[$j]['slot']] ?? null;
			if (!$b || $b['start'] === null || $b['end'] === null) {
				continue;
			}
			if (array_intersect($a['days'], $b['days']) && $a['start'] < $b['end'] && $b['start'] < $a['end']) {
				$profile['conflicts']++;
			}
		}
	}
}
unset($profile);

$activeEnrollmentRows = count(all_rows(
	$pdo,
	"SELECT e.student_ID
	 FROM Enrollment e
	 JOIN Student st ON st.student_ID = e.student_ID AND st.student_Type = 'Undergraduate'
	 JOIN Course_Section cs ON cs.CRN = e.CRN AND cs.semester_ID = e.semester_ID AND cs.is_Cancelled = 0
	 WHERE e.semester_ID = ?",
	[$semesterID]
));
$duplicateTotals = one(
	$pdo,
	"SELECT COUNT(*) AS duplicate_groups, COALESCE(SUM(group_size - 1), 0) AS extra_rows
	 FROM (
	   SELECT e.student_ID, cs.course_ID, COUNT(*) AS group_size
	   FROM Enrollment e
	   JOIN Student st ON st.student_ID = e.student_ID AND st.student_Type = 'Undergraduate'
	   JOIN Course_Section cs ON cs.CRN = e.CRN AND cs.semester_ID = e.semester_ID AND cs.is_Cancelled = 0
	   WHERE e.semester_ID = ?
	   GROUP BY e.student_ID, cs.course_ID
	   HAVING COUNT(*) > 1
	 ) d",
	[$semesterID]
);
$crossLevelCount = (int) (one(
	$pdo,
	"SELECT COUNT(*) AS n
	 FROM Enrollment e
	 JOIN Student st ON st.student_ID = e.student_ID AND st.student_Type = 'Undergraduate'
	 JOIN Course_Section cs ON cs.CRN = e.CRN AND cs.semester_ID = e.semester_ID AND cs.is_Cancelled = 0
	 JOIN Course c ON c.course_ID = cs.course_ID
	 WHERE e.semester_ID = ? AND c.course_Type <> 'Undergraduate'",
	[$semesterID]
)['n'] ?? 0);

$validStudentCredits = [];
foreach (all_rows(
	$pdo,
	"SELECT e.student_ID, SUM(c.course_Credits) AS credits
	 FROM Enrollment e
	 JOIN Student st ON st.student_ID = e.student_ID AND st.student_Type = 'Undergraduate'
	 JOIN Course_Section cs ON cs.CRN = e.CRN AND cs.semester_ID = e.semester_ID AND cs.is_Cancelled = 0
	 JOIN Course c ON c.course_ID = cs.course_ID AND c.course_Type = 'Undergraduate'
	 WHERE e.semester_ID = ? GROUP BY e.student_ID",
	[$semesterID]
) as $row) {
	$validStudentCredits[(int) $row['student_ID']] = (int) $row['credits'];
}

$availableSections = [];
foreach ($sectionRows as $section) {
	if ((string) $section['course_Type'] !== 'Undergraduate' || (int) $section['available_Seats'] <= 0) {
		continue;
	}
	$errors = validate_schedule_row($section);
	$errors = array_merge(
		$errors,
		validate_friday_saturday_schedule_rule(
			$pdo,
			(string) $section['course_ID'],
			(int) $section['time_Slot_ID'],
			$semesterID
		)
	);
	if ($errors) {
		continue;
	}
	$availableSections[] = $section;
}

$requirementCourseSet = [];
foreach ($profiles as $id => $profile) {
	foreach (array_keys($profile['majors']) as $majorID) {
		foreach (array_keys($majorRequirements[$majorID] ?? []) as $courseID) {
			$requirementCourseSet[$id][$courseID] = true;
		}
	}
	foreach (array_keys($profile['minors']) as $minorID) {
		foreach (array_keys($minorRequirements[$minorID] ?? []) as $courseID) {
			$requirementCourseSet[$id][$courseID] = true;
		}
	}
}
$primaryMajorIDs = [];
$studentsPrimaryMajorWithoutPublishedRequirements = 0;
$studentsWithAnyDeclaredMajorRequirement = 0;
$studentsWithAnyDeclaredMinorRequirement = 0;
foreach ($profiles as $id => $profile) {
	if ($profile['primary_major_id'] !== '') {
		$primaryID = (int) $profile['primary_major_id'];
		$primaryMajorIDs[$primaryID] = true;
		if (empty($majorRequirements[$primaryID])) $studentsPrimaryMajorWithoutPublishedRequirements++;
	}
	$hasMajorRequirement = false;
	foreach (array_keys($profile['majors']) as $majorID) {
		if (!empty($majorRequirements[$majorID])) { $hasMajorRequirement = true; break; }
	}
	if ($hasMajorRequirement) $studentsWithAnyDeclaredMajorRequirement++;
	$hasMinorRequirement = false;
	foreach (array_keys($profile['minors']) as $minorID) {
		if (!empty($minorRequirements[$minorID])) { $hasMinorRequirement = true; break; }
	}
	if ($hasMinorRequirement) $studentsWithAnyDeclaredMinorRequirement++;
}

$undergraduateCatalog = [];
foreach (all_rows(
	$pdo,
	"SELECT course_ID, course_Name, course_Credits, dept_ID
	 FROM Course WHERE course_Type = 'Undergraduate' ORDER BY course_ID"
) as $row) {
	$undergraduateCatalog[(string) $row['course_ID']] = $row;
}
$potentialRequirementCourseRelationships = 0;
$potentialRequirementStudentsByCourse = [];
foreach ($profiles as $id => &$profile) {
	foreach (array_keys($requirementCourseSet[$id] ?? []) as $courseID) {
		if (!isset($undergraduateCatalog[$courseID]) || isset($profile['courses'][$courseID])) continue;
		if (isset($bestHistory[$id][$courseID]) && $bestHistory[$id][$courseID]['points'] >= 1.0) continue;
		$prerequisitesMet = true;
		foreach ($prerequisites[$courseID] ?? [] as $prerequisite) {
			$completed = $bestHistory[$id][$prerequisite['course_ID']] ?? null;
			if (!$completed || !grade_meets($completed['grade'], $prerequisite['minimum'])) {
				$prerequisitesMet = false;
				break;
			}
		}
		if (!$prerequisitesMet) continue;
		$profile['catalog_candidates'][] = $courseID;
		$potentialRequirementCourseRelationships++;
		$potentialRequirementStudentsByCourse[$courseID][$id] = true;
	}
}
unset($profile);

$courseSectionResources = [];
foreach (all_rows(
	$pdo,
	"SELECT cs.course_ID, COUNT(*) AS active_sections,
	        SUM(COALESCE(l.avail_Seats, b.no_Of_Workstations, 0)) AS physical_seats,
	        SUM(cs.available_Seats) AS stored_open_seats,
	        SUM((SELECT COUNT(*) FROM Enrollment e WHERE e.CRN = cs.CRN AND e.semester_ID = cs.semester_ID)) AS enrolled_rows
	 FROM Course_Section cs
	 JOIN Course c ON c.course_ID = cs.course_ID AND c.course_Type = 'Undergraduate'
	 LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
	 LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID
	 WHERE cs.semester_ID = ? AND cs.is_Cancelled = 0
	 GROUP BY cs.course_ID",
	[$semesterID]
) as $row) {
	$courseSectionResources[(string) $row['course_ID']] = $row;
}
$facultyByDepartment = [];
foreach (all_rows(
	$pdo,
	"SELECT f.faculty_ID, f.faculty_Type, fd.dept_ID,
	        COUNT(DISTINCT cs.CRN) AS assigned_sections
	 FROM Faculty f
	 JOIN Faculty_Department fd ON fd.faculty_ID = f.faculty_ID
	 LEFT JOIN Course_Section cs ON cs.faculty_ID = f.faculty_ID AND cs.semester_ID = ? AND cs.is_Cancelled = 0
	 GROUP BY f.faculty_ID, f.faculty_Type, fd.dept_ID",
	[$semesterID]
) as $row) {
	$limit = (string) $row['faculty_Type'] === 'Full-Time' ? 2 : 1;
	$spare = max(0, $limit - (int) $row['assigned_sections']);
	$facultyByDepartment[(int) $row['dept_ID']][] = ['faculty_ID' => (int) $row['faculty_ID'], 'spare' => $spare];
}
$demandPath = $reportDirectory . '/fall2026_chunk1_course_demand.csv';
$demandCSV = fopen($demandPath, 'wb');
if ($demandCSV === false) {
	fwrite(STDERR, "Unable to write course demand CSV.\n");
	exit(2);
}
fputcsv($demandCSV, [
	'course_ID', 'course_Name', 'department_ID', 'credits', 'potential_students_with_unpassed_prerequisite_met_requirement',
	'active_Fall2026_sections', 'physical_seats', 'currently_open_seats',
	'qualified_faculty_with_remaining_section_capacity', 'aggregate_remaining_qualified_faculty_assignments',
	'additional_10_seat_sections_if_every_candidate_requested_this_course_non_additive'
], ',', '"', '');
ksort($potentialRequirementStudentsByCourse);
foreach ($potentialRequirementStudentsByCourse as $courseID => $candidateStudentSet) {
	$course = $undergraduateCatalog[$courseID];
	$resource = $courseSectionResources[$courseID] ?? [
		'active_sections' => 0, 'physical_seats' => 0, 'stored_open_seats' => 0,
	];
	$deptFaculty = $facultyByDepartment[(int) $course['dept_ID']] ?? [];
	$facultyWithCapacity = count(array_filter($deptFaculty, static fn(array $faculty): bool => $faculty['spare'] > 0));
	$spareFacultyAssignments = array_sum(array_column($deptFaculty, 'spare'));
	$seatGap = max(0, count($candidateStudentSet) - (int) $resource['stored_open_seats']);
	fputcsv($demandCSV, [
		$courseID, $course['course_Name'], $course['dept_ID'], $course['course_Credits'],
		count($candidateStudentSet), (int) $resource['active_sections'], (int) $resource['physical_seats'],
		(int) $resource['stored_open_seats'], $facultyWithCapacity, $spareFacultyAssignments,
		(int) ceil($seatGap / 10),
	], ',', '"', '');
}
fclose($demandCSV);

$optionsBySection = [];
$sectionEligibilityReasons = [];
foreach ($availableSections as $section) {
	$crn = (int) $section['CRN'];
	$courseID = (string) $section['course_ID'];
	$optionsBySection[$crn] = [];
	foreach ($profiles as $id => $profile) {
		$reasons = [];
		if ($profile['holds'] > 0) $reasons[] = 'registration_hold';
		if ($profile['duplicate_groups'] > 0) $reasons[] = 'existing_duplicate_course_registration';
		if ($profile['conflicts'] > 0) $reasons[] = 'existing_timetable_conflict';
		if ($profile['cross_level'] > 0) $reasons[] = 'existing_cross_level_registration';
		if ($profile['invalid_enrollments'] > 0) $reasons[] = 'invalid_or_cancelled_enrollment_link';
		if (!isset($requirementCourseSet[$id][$courseID])) $reasons[] = 'no_published_major_or_minor_requirement_match';
		if (isset($profile['courses'][$courseID])) $reasons[] = 'already_registered_in_course';
		$maxCredits = student_credit_limit($pdo, $id);
		if ($profile['credits'] + (int) $section['course_Credits'] > $maxCredits) $reasons[] = 'credit_limit';
		foreach ($profile['active_sections'] as $registered) {
			if ($registered['course_ID'] === $courseID) {
				$reasons[] = 'already_registered_in_course';
				break;
			}
			$a = $slotMap[$registered['slot']] ?? null;
			$b = $slotMap[(int) $section['time_Slot_ID']] ?? null;
			if ($a && $b && array_intersect($a['days'], $b['days']) && $a['start'] !== null && $b['start'] !== null && $a['end'] !== null && $b['end'] !== null && $a['start'] < $b['end'] && $b['start'] < $a['end']) {
				$reasons[] = 'proposed_timetable_conflict';
				break;
			}
		}
		if (isset($bestHistory[$id][$courseID]) && $bestHistory[$id][$courseID]['points'] >= 1.0) {
			$reasons[] = 'course_already_passed';
		}
		foreach ($prerequisites[$courseID] ?? [] as $prerequisite) {
			$completed = $bestHistory[$id][$prerequisite['course_ID']] ?? null;
			if (!$completed || !grade_meets($completed['grade'], $prerequisite['minimum'])) {
				$reasons[] = 'prerequisite_not_met';
				break;
			}
		}
		$reasons = array_values(array_unique($reasons));
		if (!$reasons) {
			$optionsBySection[$crn][] = $id;
		} else {
			foreach ($reasons as $reason) $sectionEligibilityReasons[$reason] = ($sectionEligibilityReasons[$reason] ?? 0) + 1;
		}
	}
	usort($optionsBySection[$crn], static function (int $a, int $b) use ($profiles): int {
		return [$profiles[$a]['credits'], count($profiles[$a]['courses']), $a]
			<=> [$profiles[$b]['credits'], count($profiles[$b]['courses']), $b];
	});
}

$eligibleStudents = [];
foreach ($optionsBySection as $candidateIDs) {
	foreach ($candidateIDs as $id) $eligibleStudents[$id] = true;
}

// Allocate only extant open seats and at most one additional course per
// student. This is a small, reviewable recommendation, not a DB migration.
usort($availableSections, static function (array $a, array $b) use ($optionsBySection): int {
	return [count($optionsBySection[(int) $a['CRN']] ?? []), (int) $a['CRN']]
		<=> [count($optionsBySection[(int) $b['CRN']] ?? []), (int) $b['CRN']];
});
$assignedStudents = [];
foreach ($availableSections as $section) {
	$crn = (int) $section['CRN'];
	$remaining = (int) $section['available_Seats'];
	foreach ($optionsBySection[$crn] ?? [] as $id) {
		if (isset($assignedStudents[$id]) || $remaining <= 0) continue;
		$profiles[$id]['proposed'] = $section;
		$assignedStudents[$id] = true;
		$remaining--;
	}
}

$courseBuckets = ['0' => 0, '1' => 0, '2' => 0, '3' => 0, '4+' => 0];
$rowBuckets = ['0' => 0, '1' => 0, '2' => 0, '3' => 0, '4+' => 0];
$statusCounts = ['FT' => 0, 'PT' => 0, 'Not Enrolled' => 0];
$validUGStatusCounts = ['FT' => 0, 'PT' => 0, 'Not Enrolled' => 0];
$totalUGCourseCredits = 0;
$maximumAllCourseLoad = 0;
$studentsOver20Credits = 0;
$studentsWithHold = 0;
$studentsWithAnyConflict = 0;
$studentsWithDuplicates = 0;
$studentsWithCrossLevel = 0;
$studentsWithInvalidEnrollments = 0;
$currentPrerequisiteFailures = 0;
$studentsWithCurrentPrerequisiteFailures = [];
$currentPassedCourseRetakes = 0;
foreach ($profiles as $id => $profile) {
	$courseN = count($profile['courses']);
	$rowN = count($profile['course_rows']);
	$courseBuckets[$courseN >= 4 ? '4+' : (string) $courseN]++;
	$rowBuckets[$rowN >= 4 ? '4+' : (string) $rowN]++;
	$status = $profile['credits'] === 0 ? 'Not Enrolled' : ($profile['credits'] >= 12 ? 'FT' : 'PT');
	$statusCounts[$status]++;
	$maximumAllCourseLoad = max($maximumAllCourseLoad, $profile['credits']);
	if ($profile['credits'] > 20) $studentsOver20Credits++;
	$cleanUGCredits = $validStudentCredits[$id] ?? 0;
	$totalUGCourseCredits += $cleanUGCredits;
	$validUGStatus = $cleanUGCredits === 0 ? 'Not Enrolled' : ($cleanUGCredits >= 12 ? 'FT' : 'PT');
	$validUGStatusCounts[$validUGStatus]++;
	if ($profile['holds'] > 0) $studentsWithHold++;
	if ($profile['conflicts'] > 0) $studentsWithAnyConflict++;
	if ($profile['duplicate_groups'] > 0) $studentsWithDuplicates++;
	if ($profile['cross_level'] > 0) $studentsWithCrossLevel++;
	if ($profile['invalid_enrollments'] > 0) $studentsWithInvalidEnrollments++;
	foreach ($profile['active_sections'] as $active) {
		if (isset($bestHistory[$id][$active['course_ID']]) && $bestHistory[$id][$active['course_ID']]['points'] >= 1.0) {
			$currentPassedCourseRetakes++;
		}
		foreach ($prerequisites[$active['course_ID']] ?? [] as $prerequisite) {
			$completed = $bestHistory[$id][$prerequisite['course_ID']] ?? null;
			if (!$completed || !grade_meets($completed['grade'], $prerequisite['minimum'])) {
				$currentPrerequisiteFailures++;
				$studentsWithCurrentPrerequisiteFailures[$id] = true;
				break;
			}
		}
	}
}

$csvPath = $reportDirectory . '/fall2026_chunk1_student_enrollment_plan.csv';
$csv = fopen($csvPath, 'wb');
if ($csv === false) {
	fwrite(STDERR, "Unable to write student plan CSV.\n");
	exit(2);
}
fputcsv($csv, [
	'student_ID', 'primary_major_ID', 'declared_major_IDs', 'declared_minor_IDs',
	'active_distinct_courses', 'active_enrollment_rows', 'registered_credits_all_active',
	'current_status', 'registration_hold', 'duplicate_course_groups', 'schedule_conflict_pairs',
	'cross_level_enrollment_rows', 'invalid_or_cancelled_enrollment_rows', 'active_courses_CRN_course_credits',
	'catalog_requirement_courses_unpassed_prereqs_met', 'eligible_open_requirement_CRNs', 'proposed_add_CRN', 'proposed_add_course_ID',
	'proposed_add_credits', 'projected_credits', 'projected_status', 'plan_note'
], ',', '"', '');
foreach ($profiles as $id => $profile) {
	$currentStatus = $profile['credits'] === 0 ? 'Not Enrolled' : ($profile['credits'] >= 12 ? 'FT' : 'PT');
	$proposed = $profile['proposed'];
	$projectedCredits = $profile['credits'] + ($proposed ? (int) $proposed['course_Credits'] : 0);
	$projectedStatus = $projectedCredits === 0 ? 'Not Enrolled' : ($projectedCredits >= 12 ? 'FT' : 'PT');
	$courseTokens = [];
	foreach ($profile['active_sections'] as $active) {
		$courseTokens[] = $active['CRN'] . ':' . $active['course_ID'] . ':' . $active['credits'];
	}
	$openRequirementCRNs = [];
	foreach ($optionsBySection as $crn => $ids) if (in_array($id, $ids, true)) $openRequirementCRNs[] = $crn;
	$note = $proposed
		? 'Proposed only from an existing open seat; re-run eligibility and capacity checks immediately before Chunk 2 writes.'
		: (isset($eligibleStudents[$id])
			? 'Eligible for at least one published requirement section, but existing open seats were allocated to other eligible students.'
			: 'No safe existing open seat matched a published major/minor requirement and all checks; plan new sections/advisor review.');
	fputcsv($csv, [
		$id,
		$profile['primary_major_id'],
		implode('|', array_keys($profile['majors'])),
		implode('|', array_keys($profile['minors'])),
		count($profile['courses']), count($profile['course_rows']), $profile['credits'],
		$currentStatus, $profile['holds'] > 0 ? 'yes' : 'no', $profile['duplicate_groups'],
		$profile['conflicts'], $profile['cross_level'], $profile['invalid_enrollments'],
		implode('|', $courseTokens), implode('|', $profile['catalog_candidates']), implode('|', $openRequirementCRNs),
		$proposed['CRN'] ?? '', $proposed['course_ID'] ?? '',
		$proposed['course_Credits'] ?? '', $projectedCredits, $projectedStatus, $note,
	], ',', '"', '');
}
fclose($csv);

$resourceTotals = one(
	$pdo,
	"SELECT COUNT(*) AS active_sections,
	        SUM(CASE WHEN c.course_Type = 'Undergraduate' THEN 1 ELSE 0 END) AS undergraduate_sections,
	        SUM(CASE WHEN c.course_Type = 'Graduate' THEN 1 ELSE 0 END) AS graduate_sections,
	        SUM(CASE WHEN c.course_Type = 'Undergraduate' THEN COALESCE(l.avail_Seats, b.no_Of_Workstations, 0) ELSE 0 END) AS undergraduate_physical_seats,
	        SUM(CASE WHEN c.course_Type = 'Undergraduate' THEN cs.available_Seats ELSE 0 END) AS undergraduate_open_seats,
	        SUM(CASE WHEN c.course_Type = 'Undergraduate' THEN cs.available_Seats * c.course_Credits ELSE 0 END) AS undergraduate_open_credit_seats,
	        SUM(CASE WHEN c.course_Type = 'Undergraduate' THEN COALESCE(l.avail_Seats, b.no_Of_Workstations, 0) * c.course_Credits ELSE 0 END) AS undergraduate_physical_credit_seats
	 FROM Course_Section cs
	 JOIN Course c ON c.course_ID = cs.course_ID
	 LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
	 LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID
	 WHERE cs.semester_ID = ? AND cs.is_Cancelled = 0",
	[$semesterID]
);
$allEnrollmentCredits = one(
	$pdo,
	"SELECT SUM(c.course_Credits) AS credits
	 FROM Enrollment e
	 JOIN Student st ON st.student_ID=e.student_ID AND st.student_Type='Undergraduate'
	 JOIN Course_Section cs ON cs.CRN=e.CRN AND cs.semester_ID=e.semester_ID AND cs.is_Cancelled=0
	 JOIN Course c ON c.course_ID=cs.course_ID
	 WHERE e.semester_ID=?",
	[$semesterID]
);
$ftCapacity = (int) $pdo->query(
	"SELECT COALESCE(SUM(CASE WHEN f.faculty_Type='Full-Time' THEN GREATEST(0,2-COALESCE(x.section_count,0)) ELSE GREATEST(0,1-COALESCE(x.section_count,0)) END),0)
	 FROM Faculty f
	 LEFT JOIN (SELECT faculty_ID, COUNT(*) section_count FROM Course_Section WHERE semester_ID=1 AND is_Cancelled=0 GROUP BY faculty_ID) x ON x.faculty_ID=f.faculty_ID"
)->fetchColumn();
$qualifiedFaculty = (int) $pdo->query(
	"SELECT COUNT(DISTINCT fd.faculty_ID)
	 FROM Faculty_Department fd JOIN Faculty f ON f.faculty_ID=fd.faculty_ID
	 WHERE (f.faculty_Type='Full-Time' AND COALESCE((SELECT COUNT(*) FROM Course_Section cs WHERE cs.faculty_ID=f.faculty_ID AND cs.semester_ID=1 AND cs.is_Cancelled=0),0)<2)
	    OR (f.faculty_Type='Part-Time' AND COALESCE((SELECT COUNT(*) FROM Course_Section cs WHERE cs.faculty_ID=f.faculty_ID AND cs.semester_ID=1 AND cs.is_Cancelled=0),0)<1)"
)->fetchColumn();

$timeSlotInventory = all_rows(
	$pdo,
	"SELECT ts.time_Slot_ID,
	        GROUP_CONCAT(DISTINCT d.day_Of_Week ORDER BY d.day_ID SEPARATOR ', ') AS meeting_days,
	        MIN(p.start_Time) AS start_time, MAX(p.end_Time) AS end_time,
	        CASE WHEN MIN(p.start_Time) IS NULL OR MAX(p.end_Time) IS NULL THEN NULL
	             ELSE TIMESTAMPDIFF(MINUTE, MIN(p.start_Time), MAX(p.end_Time)) END AS duration_minutes,
	        COUNT(DISTINCT cs.CRN) AS active_Fall2026_sections
	 FROM Time_Slot ts
	 LEFT JOIN Time_Slot_Day tsd ON tsd.time_Slot_ID=ts.time_Slot_ID
	 LEFT JOIN Day d ON d.day_ID=tsd.day_ID
	 LEFT JOIN Time_Slot_Period tsp ON tsp.time_Slot_ID=ts.time_Slot_ID
	 LEFT JOIN Period p ON p.period_ID=tsp.period_ID
	 LEFT JOIN Course_Section cs ON cs.time_Slot_ID=ts.time_Slot_ID AND cs.semester_ID=? AND cs.is_Cancelled=0
	 GROUP BY ts.time_Slot_ID
	 ORDER BY MIN(p.start_Time), ts.time_Slot_ID",
	[$semesterID]
);
$timeSlotPath = $reportDirectory . '/fall2026_chunk1_time_slot_inventory.csv';
$timeSlotCSV = fopen($timeSlotPath, 'wb');
if ($timeSlotCSV === false) {
	fwrite(STDERR, "Unable to write time slot inventory CSV.\n");
	exit(2);
}
fputcsv($timeSlotCSV, [
	'time_Slot_ID', 'meeting_days', 'start_time', 'end_time', 'duration_minutes',
	'active_Fall2026_sections', 'Saturday_prohibited', 'four_credit_Friday_200_minute_eligible'
], ',', '"', '');
$friday200SlotCount = 0;
$saturdaySlotCount = 0;
foreach ($timeSlotInventory as $slot) {
	$days = array_map('trim', explode(',', (string) ($slot['meeting_days'] ?? '')));
	$isSaturday = in_array('Saturday', $days, true);
	$isFriday = in_array('Friday', $days, true);
	$isFridayOnly = $days === ['Friday'];
	$isFriday200 = $isFridayOnly && (int) $slot['duration_minutes'] === 200;
	if ($isSaturday) $saturdaySlotCount++;
	if ($isFriday200) $friday200SlotCount++;
	fputcsv($timeSlotCSV, [
		(int) $slot['time_Slot_ID'], $slot['meeting_days'], $slot['start_time'], $slot['end_time'],
		$slot['duration_minutes'], (int) $slot['active_Fall2026_sections'],
		$isSaturday ? 'yes' : 'no', $isFriday200 ? 'yes' : 'no',
	], ',', '"', '');
}
fclose($timeSlotCSV);

$overlapCount = 0;
foreach ($profiles as $profile) $overlapCount += $profile['conflicts'];
$currentValidUGCredits = (int) ($allEnrollmentCredits['credits'] ?? 0) - $crossLevelCount * 0;
$targetCredits = (610 * 16) + (610 * 8);
$existingSeatsForZeroStudents = min((int) $resourceTotals['undergraduate_open_seats'], (int) $courseBuckets['0']);
$minNewSeatsToGiveEveryUGAtLeastOneCourse = max(0, (int) $courseBuckets['0'] - $existingSeatsForZeroStudents);
$idealCreditGap = max(0, $targetCredits - $totalUGCourseCredits);
$minimumNewSectionsForEveryZeroStudent = (int) ceil($minNewSeatsToGiveEveryUGAtLeastOneCourse / 10);
$newFourCreditTenSeatSectionsForIdealCreditGap = (int) ceil(max(
	0,
	$idealCreditGap - (int) $resourceTotals['undergraduate_open_credit_seats']
) / 40);

$summary = [
	'database' => $databaseName,
	'semester_ID' => $semesterID,
	'semester' => $semester['semester_Name'],
	'generated_at' => date(DATE_ATOM),
	'read_only' => true,
	'undergraduate_students' => count($profiles),
	'primary_major_count' => count($primaryMajorIDs),
	'students_primary_major_without_published_requirements' => $studentsPrimaryMajorWithoutPublishedRequirements,
	'students_with_any_declared_major_with_published_requirements' => $studentsWithAnyDeclaredMajorRequirement,
	'students_with_any_declared_minor_with_published_requirements' => $studentsWithAnyDeclaredMinorRequirement,
	'students_by_distinct_active_course_count' => $courseBuckets,
	'students_by_raw_active_enrollment_rows' => $rowBuckets,
	'current_status_including_cross_level_rows_and_duplicates' => $statusCounts,
	'status_using_undergraduate_courses_only' => $validUGStatusCounts,
	'active_enrollment_rows' => $activeEnrollmentRows,
	'duplicate_student_course_groups' => (int) ($duplicateTotals['duplicate_groups'] ?? 0),
	'extra_duplicate_enrollment_rows' => (int) ($duplicateTotals['extra_rows'] ?? 0),
	'undergraduate_enrollment_rows_in_graduate_courses' => $crossLevelCount,
	'students_with_registration_holds' => $studentsWithHold,
	'students_with_existing_schedule_conflicts' => $studentsWithAnyConflict,
	'existing_schedule_conflict_pairs' => $overlapCount,
	'students_with_duplicate_course_groups' => $studentsWithDuplicates,
	'students_with_cross_level_enrollments' => $studentsWithCrossLevel,
	'students_with_invalid_or_cancelled_enrollment_links' => $studentsWithInvalidEnrollments,
	'active_enrollment_rows_with_unmet_prerequisite' => $currentPrerequisiteFailures,
	'students_with_active_enrollment_prerequisite_failures' => count($studentsWithCurrentPrerequisiteFailures),
	'active_enrollment_rows_for_previously_passed_courses' => $currentPassedCourseRetakes,
	'current_undergraduate_course_credits' => $totalUGCourseCredits,
	'maximum_registered_credits_all_active_records' => $maximumAllCourseLoad,
	'students_over_20_active_credits_flagged_for_review' => $studentsOver20Credits,
	'current_all_active_credits_counting_cross_level' => (int) ($allEnrollmentCredits['credits'] ?? 0),
	'ideal_total_credits_for_610x16_and_610x8' => $targetCredits,
	'ideal_credit_seat_gap_before_new_sections' => $idealCreditGap,
	'additional_seats_minimum_for_every_currently_zero_course_student_to_receive_one_course' => $minNewSeatsToGiveEveryUGAtLeastOneCourse,
	'lower_bound_new_10_seat_sections_for_every_currently_zero_student_to_receive_one_course' => $minimumNewSectionsForEveryZeroStudent,
	'open_undergraduate_credit_seats' => (int) $resourceTotals['undergraduate_open_credit_seats'],
	'optimistic_new_four_credit_full_10_seat_sections_for_ideal_credit_gap' => $newFourCreditTenSeatSectionsForIdealCreditGap,
	'currently_open_undergraduate_seats' => (int) $resourceTotals['undergraduate_open_seats'],
	'currently_open_undergraduate_sections' => count($availableSections),
	'proposed_students_assigned_existing_open_seat' => count($assignedStudents),
	'students_eligible_for_at_least_one_existing_open_section_with_published_requirement_match' => count($eligibleStudents),
	'section_eligibility_blocker_counts_not_mutually_exclusive' => $sectionEligibilityReasons,
	'active_undergraduate_sections' => (int) $resourceTotals['undergraduate_sections'],
	'undergraduate_physical_seats' => (int) $resourceTotals['undergraduate_physical_seats'],
	'undergraduate_physical_credit_seats' => (int) $resourceTotals['undergraduate_physical_credit_seats'],
	'faculty_with_any_remaining_section_capacity_and_department_membership' => $qualifiedFaculty,
	'aggregate_remaining_faculty_section_capacity_under_current_limits' => $ftCapacity,
	'existing_time_slot_count' => count($timeSlotInventory),
	'existing_Friday_only_200_minute_time_slots' => $friday200SlotCount,
	'existing_time_slots_including_Saturday' => $saturdaySlotCount,
	'potential_requirement_course_student_relationships_before_seat_time_hold_and_load_checks' => $potentialRequirementCourseRelationships,
	'potential_requirement_course_candidate_students_by_course' => array_map('count', $potentialRequirementStudentsByCourse),
	'plan_CSV' => 'reports/fall2026_chunk1_student_enrollment_plan.csv',
	'course_demand_CSV' => 'reports/fall2026_chunk1_course_demand.csv',
	'time_slot_inventory_CSV' => 'reports/fall2026_chunk1_time_slot_inventory.csv',
	'notes' => [
		'No enrollment or academic record was changed.',
		'Existing course counts are distinct course_IDs; duplicate Enrollment rows are separately counted.',
		'Course recommendations require an explicit published major/minor course requirement; no elective rule was inferred.',
		'Existing open-seat recommendations are provisional and require a fresh transactional validation in Chunk 2.',
		'Exact additional-section count cannot be inferred without selecting the individual FT/PT cohort and each student course plan; the reported seat and credit gaps are lower-bound planning measures.',
		'The 10-seat section and 4-credit section estimates are optimistic lower-bound scenarios; course choice, capacity, schedule, faculty workload, and academic eligibility may require more sections.',
	],
];
file_put_contents($reportDirectory . '/fall2026_chunk1_summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "Fall 2026 Chunk 1 read-only plan created.\n";
echo 'Undergraduates: ' . count($profiles) . "\n";
echo 'Course-count distribution: ' . json_encode($courseBuckets) . "\n";
echo 'Current FT/PT/Not Enrolled: ' . json_encode($statusCounts) . "\n";
echo 'Existing safe seat proposals: ' . count($assignedStudents) . ' of ' . (int) $resourceTotals['undergraduate_open_seats'] . ' open UG seats; eligible students: ' . count($eligibleStudents) . "\n";
echo 'Minimum new seats to give all currently-zero students one course: ' . $minNewSeatsToGiveEveryUGAtLeastOneCourse . "\n";
echo 'Lower-bound 10-seat sections for those first-course seats: ' . $minimumNewSectionsForEveryZeroStudent . "\n";
echo 'Ideal credit-seat gap (14,640 target less current UG-course credits): ' . $idealCreditGap . "\n";
echo 'Optimistic four-credit/10-seat sections for that aggregate credit gap: ' . $newFourCreditTenSeatSectionsForIdealCreditGap . "\n";
echo "Reports: student plan, course demand, time-slot inventory, and summary JSON under reports/fall2026_chunk1_*\n";


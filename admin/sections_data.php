<?php

declare(strict_types=1);

// Reference data for the section forms.

$semesters = all_rows(
	$pdo,
	'SELECT semester_ID, semester_Name FROM Semester WHERE semester_Name IN (\'Fall 2026\', \'Spring 2027\') ORDER BY semester_ID ASC'
);

$departments = all_rows(
	$pdo,
	'SELECT dept_ID, dept_Name FROM Department ORDER BY dept_Name'
);

$courses = all_rows(
	$pdo,
	"SELECT c.course_ID, c.course_Name, c.course_Type, c.course_Credits, c.dept_ID, d.dept_Name
	FROM Course c
	JOIN Department d ON d.dept_ID = c.dept_ID
	ORDER BY d.dept_Name, c.course_ID"
);

$faculty = all_rows(
	$pdo,
	"SELECT f.faculty_ID, f.faculty_Type,
			CONCAT(u.first_Name, ' ', u.last_Name) AS name
	FROM Faculty f
	JOIN User u ON u.user_ID = f.faculty_ID
	ORDER BY name"
);

$workloadSemesters = all_rows($pdo, 'SELECT semester_ID FROM Semester ORDER BY semester_ID');
$facultyWorkloadsBySemester = [];
foreach ($workloadSemesters as $semesterOption) {
	$semesterId = (int) $semesterOption['semester_ID'];
	$facultyWorkloadsBySemester[$semesterId] = faculty_workload_directory($pdo, $semesterId)['by_faculty'];
}

foreach ($faculty as &$facultyMember) {
	$facultyId = (int) $facultyMember['faculty_ID'];
	$facultyMember['teaching_loads'] = [];
	foreach ($workloadSemesters as $semesterOption) {
		$semesterId = (int) $semesterOption['semester_ID'];
		$load = $facultyWorkloadsBySemester[$semesterId][$facultyId] ?? null;
		$credits = $load['teaching_credits'] ?? null;
		$facultyMember['teaching_loads'][$semesterId] = [
			'employment_type' => $load['employment_type'] ?? 'Unknown',
			'teaching_credits' => $credits,
			'assigned_sections' => $load['assigned_sections'] ?? 0,
			'missing_credit_sections' => $load['missing_credit_sections'] ?? 0,
		];
	}
}
unset($facultyMember);

$facultyWorkloadLabels = [];
foreach ($faculty as $facultyMember) {
	$facultyId = (int) $facultyMember['faculty_ID'];
	foreach ($facultyMember['teaching_loads'] as $semesterId => $load) {
		$facultyWorkloadLabels[$facultyId][(int) $semesterId] = [
			'employment_type' => $load['employment_type'],
			'teaching_credits' => $load['teaching_credits'],
			'assigned_sections' => $load['assigned_sections'] ?? 0,
			'missing_credit_sections' => $load['missing_credit_sections'] ?? 0,
		];
	}
}

$timeSlots = all_rows(
	$pdo,
	'SELECT time_Slot_ID FROM Time_Slot ORDER BY time_Slot_ID'
);

$lectureRooms = all_rows(
	$pdo,
	"SELECT l.lecture_ID, l.avail_Seats, r.room_Number, b.Bldg_Name
	FROM Lecture l
	JOIN Room r ON r.room_ID = l.lecture_ID
	JOIN Building b ON b.Bldg_ID = r.Bldg_ID
	ORDER BY b.Bldg_Name, r.room_Number"
);

$labRooms = all_rows(
	$pdo,
	"SELECT b.lab_ID, b.no_Of_Workstations, r.room_Number, bl.Bldg_Name
	FROM Lab b
	JOIN Room r ON r.room_ID = b.lab_ID
	JOIN Building bl ON bl.Bldg_ID = r.Bldg_ID
	ORDER BY bl.Bldg_Name, r.room_Number"
);

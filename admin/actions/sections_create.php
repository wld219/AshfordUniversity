<?php

declare(strict_types=1);

// Create a section.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

// Enforces:
//   - the course's department must be one of the faculty member's
//     departments (faculty may only teach in their departments),
//   - full-time faculty teach at most two sections and never two
//     overlapping sections; part-time faculty teach exactly one section.
//
// Uses $pdo, $adminID, $error from the entry point.

$courseID = post_int('course_id') ?? 0;
$facultyID = post_int('faculty_id') ?? 0;
$timeSlotID = post_int('time_slot_id') ?? 0;
$semesterID = post_int('semester_id') ?? 0;
$sectionNo = post_int('section_no') ?? 0;
$roomType = post_str('room_type', 10);
$roomID = post_int('room_id');
$seats = post_int('seats') ?? 0;

if (
	$courseID <= 0 || $facultyID <= 0 || $timeSlotID <= 0 ||
	$semesterID <= 0 || $sectionNo <= 0
) {
	$error = 'Please complete every section field.';
	return;
}

if ($roomType === 'lecture' && $roomID !== null) {

	$lecture = one(
		$pdo,
		'SELECT avail_Seats FROM Lecture WHERE lecture_ID = ?',
		[$roomID]
	);

	if (!$lecture) {
		$error = 'The selected lecture room was not found.';
		return;
	}
}

// Faculty workload and department rules
[$canTeach, $teachMessage] = faculty_can_teach_section(
	$pdo,
	$facultyID,
	$timeSlotID
);

if (!$canTeach) {
	$error = $teachMessage;
	return;
}

if (!faculty_teaches_department($pdo, $facultyID, $courseID)) {
	$error = 'Faculty may only be assigned to sections in their departments.';
	return;
}

$lectureID = ($roomType === 'lecture') ? $roomID : null;
$labID = ($roomType === 'lab') ? $roomID : null;

// Capacity defaults from the room when seats are not given
if ($seats <= 0) {
	if ($lectureID !== null) {
		$seats = (int) (one(
			$pdo,
			'SELECT avail_Seats FROM Lecture WHERE lecture_ID = ?',
			[$lectureID]
		)['avail_Seats'] ?? 0);
	} elseif ($labID !== null) {
		$seats = (int) (one(
			$pdo,
			'SELECT no_Of_Workstations FROM Lab WHERE lab_ID = ?',
			[$labID]
		)['no_Of_Workstations'] ?? 0);
	}
}

// Next CRN (sections use the 10000+ range)
$nextCRN = ((int) (one(
	$pdo,
	'SELECT COALESCE(MAX(CRN), 10000) AS max_crn FROM Course_Section'
)['max_crn'] ?? 10000)) + 1;

try {

	$pdo->prepare(
		'INSERT INTO Course_Section
			(CRN, course_ID, section_No, faculty_ID, time_Slot_ID,
			lecture_ID, lab_ID, semester_ID, available_Seats, is_Cancelled)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)'
	)->execute([
		$nextCRN,
		$courseID,
		$sectionNo,
		$facultyID,
		$timeSlotID,
		$lectureID,
		$labID,
		$semesterID,
		max(0, $seats)
	]);

	audit_log($pdo, 'Create', 'Course_Section', (string) $nextCRN, 'Section created');

	if (master_schedule_get($pdo, $semesterID) !== null) {
		master_schedule_touch($pdo, $semesterID, $adminID);
	}

	redirect_with_flash(
		url('admin/sections.php'),
		'success',
		"Section {$nextCRN} created."
	);

} catch (Throwable $e) {

	error_log('Section creation failed: ' . $e->getMessage());

	$error = 'The section could not be created.';
}

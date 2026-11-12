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

$courseID = trim(post_str('course_id', 10));
$facultyID = post_int('faculty_id') ?? 0;
$timeSlotID = post_int('time_slot_id') ?? 0;
$semesterID = post_int('semester_id') ?? 0;
$sectionNo = post_int('section_no') ?? 0;
$roomType = post_str('room_type', 10);
$roomID = post_int('room_id');
$seatsRaw = $_POST['seats'] ?? null;
$enrolledRaw = $_POST['demo_enrolled'] ?? null;
if (!is_scalar($seatsRaw) || !preg_match('/^(?:[0-9]|10)$/', (string) $seatsRaw)
    || !is_scalar($enrolledRaw) || !preg_match('/^(?:[0-9]|10)$/', (string) $enrolledRaw)) {
    $error = 'Currently Enrolled and Seats Remaining must be whole numbers from 0 to 10.';
    return;
}
$seats = (int) $seatsRaw;
$demoEnrolled = (int) $enrolledRaw;

if (
	$courseID === '' || $facultyID <= 0 || $timeSlotID <= 0 ||
	$semesterID <= 0 || $sectionNo <= 0
) {
	$error = 'Please complete every section field.';
	return;
}

if ($seats + $demoEnrolled > 10) {
    $error = 'Currently Enrolled plus Seats Remaining cannot exceed 10.';
    return;
}

if (!one($pdo, 'SELECT course_ID FROM Course WHERE course_ID = ?', [$courseID])) {
    $error = 'Please select a valid course.';
    return;
}
if (!one($pdo, "SELECT semester_ID FROM Semester WHERE semester_ID = ? AND semester_Name IN ('Fall 2026', 'Spring 2027')", [$semesterID])) {
    $error = 'Please select Fall 2026 or Spring 2027.';
    return;
}
if (one($pdo, 'SELECT CRN FROM Course_Section WHERE course_ID = ? AND semester_ID = ? AND section_No = ? LIMIT 1', [$courseID, $semesterID, $sectionNo])) {
    $error = 'This course already has that section number in the selected semester.';
    return;
}
if (!in_array($roomType, ['lecture', 'lab'], true) || $roomID === null || $roomID <= 0) {
    $error = 'Please select a valid classroom or laboratory.';
    return;
}
$roomTable = $roomType === 'lecture' ? 'Lecture' : 'Lab';
$roomColumn = $roomType === 'lecture' ? 'lecture_ID' : 'lab_ID';
$roomCapacityColumn = $roomType === 'lecture' ? 'avail_Seats' : 'no_Of_Workstations';
$room = one($pdo, "SELECT {$roomCapacityColumn} AS capacity FROM {$roomTable} WHERE {$roomColumn} = ?", [$roomID]);
if (!$room) {
    $error = 'The selected room was not found.';
    return;
}
if ($seats + $demoEnrolled > (int) $room['capacity']) {
    $error = 'The selected room does not have enough capacity for this section.';
    return;
}
if (one($pdo, "SELECT cs.CRN FROM Course_Section cs
    JOIN Time_Slot_Day ed ON ed.time_Slot_ID = cs.time_Slot_ID
    JOIN Time_Slot_Day nd ON nd.time_Slot_ID = ? AND nd.day_ID = ed.day_ID
    WHERE cs.semester_ID = ? AND cs.{$roomColumn} = ? AND cs.is_Cancelled = 0
    AND EXISTS (
      SELECT 1 FROM Time_Slot_Period ep
      JOIN Period et ON et.period_ID = ep.period_ID
      JOIN Time_Slot_Period np ON np.time_Slot_ID = ?
      JOIN Period nt ON nt.period_ID = np.period_ID
      WHERE ep.time_Slot_ID = cs.time_Slot_ID
        AND et.start_Time < nt.end_Time AND nt.start_Time < et.end_Time
    ) LIMIT 1", [$timeSlotID, $semesterID, $roomID, $timeSlotID])) {
    $error = 'This room is already assigned to another section at that meeting time.';
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

// Check that the selected time slot actually exists.
if (!one($pdo, 'SELECT time_Slot_ID FROM Time_Slot WHERE time_Slot_ID = ?', [$timeSlotID])) {
    $error = 'Please select a valid meeting day and time.';
    return;
}

// Faculty cannot be double-booked in the same semester and time slot.
if (one($pdo, 'SELECT CRN FROM Course_Section WHERE semester_ID = ? AND time_Slot_ID = ? AND faculty_ID = ? AND is_Cancelled = 0 LIMIT 1', [$semesterID, $timeSlotID, $facultyID])) {
    $error = 'This instructor is already assigned to another section at that meeting time.';
    return;
}

// Faculty workload and department rules
[$canTeach, $teachMessage] = faculty_can_teach_section(
    $pdo,
    $facultyID,
    $timeSlotID,
    null,
    $semesterID
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

    // The display enrollment is independent of actual Enrollment records.
    $pdo->prepare('INSERT INTO Course_Section_Demo_Enrollment (CRN, demo_enrolled) VALUES (?, ?)')
        ->execute([$nextCRN, $demoEnrolled]);

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

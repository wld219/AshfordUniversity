<?php

declare(strict_types=1);

// Update a section.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

// Schedule fields (course, faculty, room, time slot) lock once the
// semester has started; only the seat count may change after that.
//
// Uses $pdo, $adminID, $error from the entry point.

$crn = post_int('crn') ?? 0;

$section = one(
	$pdo,
	'SELECT * FROM Course_Section WHERE CRN = ?',
	[$crn]
);

if (!$section) {
	$error = 'Section not found.';
	return;
}

$locked = semester_schedule_locked($pdo, (int) $section['semester_ID']);
// Query enrollment count separately: PDO::execute returns a boolean, not the count.
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM Enrollment WHERE CRN = ?');
$countStmt->execute([$crn]);
$enrolled = (int) $countStmt->fetchColumn();


$courseID = trim(post_str('course_id', 10)) ?: (string) $section['course_ID'];
$facultyID = post_int('faculty_id') ?? (int) $section['faculty_ID'];
$timeSlotID = post_int('time_slot_id') ?? (int) $section['time_Slot_ID'];
$sectionNo = post_int('section_no') ?? (int) $section['section_No'];
$roomType = post_str('room_type', 10) ?: ($section['lecture_ID'] !== null ? 'lecture' : 'lab');
$roomID = post_int('room_id') ?? ($section['lecture_ID'] ?? $section['lab_ID']);
$seats = post_int('seats') ?? (int) $section['available_Seats'];
$seatsChanged = $seats !== (int) $section['available_Seats'];

$semesterID = post_int('semester_id') ?? (int) $section['semester_ID'];
if ($semesterID !== (int) $section['semester_ID']) {
    $error = 'Changing a section semester is not supported; create a new section instead.';
    return;
}
if ($enrolled > 0 && ($courseID !== (string) $section['course_ID'] || $sectionNo !== (int) $section['section_No'])) {
    $error = 'Course and section number cannot be changed while students are enrolled.';
    return;
}
if ($seatsChanged && $seats + $enrolled > 10) {
    $error = 'Total enrolled and available seats cannot exceed 10.';
    return;
}
if ($seats < 0 || $seats > 10) {
    $error = 'Available seats must be between 0 and 10.';
    return;
}

if ($locked) {

    if (!$seatsChanged) {
        $error = 'This semester schedule is locked; only available seats can be changed.';
        return;
    }

	$pdo->prepare('UPDATE Course_Section SET available_Seats = ? WHERE CRN = ?')
		->execute([max(0, $seats), $crn]);

	audit_log($pdo, 'Update', 'Course_Section', (string) $crn, 'Seats updated only (schedule locked)');

	if (master_schedule_get($pdo, (int) $section['semester_ID']) !== null) {
		master_schedule_touch($pdo, (int) $section['semester_ID'], $adminID);
	}

	redirect_with_flash(
		url('admin/sections.php'),
		'success',
		'Seat count updated. Schedule fields are locked because the semester has started.'
	);
}

if ($courseID === '' || !one($pdo, 'SELECT course_ID FROM Course WHERE course_ID = ?', [$courseID])) {
    $error = 'Please select a valid course.';
    return;
}
if ($sectionNo <= 0 || one($pdo, 'SELECT CRN FROM Course_Section WHERE course_ID = ? AND semester_ID = ? AND section_No = ? AND CRN <> ? LIMIT 1', [$courseID, (int) $section['semester_ID'], $sectionNo, $crn])) {
    $error = 'This section number is already used for the selected course and semester.';
    return;
}
if (!in_array($roomType, ['lecture', 'lab'], true) || !$roomID) {
    $error = 'Please select a valid room.';
    return;
}
$roomTable = $roomType === 'lecture' ? 'Lecture' : 'Lab';
$roomColumn = $roomType === 'lecture' ? 'lecture_ID' : 'lab_ID';
$capacityColumn = $roomType === 'lecture' ? 'avail_Seats' : 'no_Of_Workstations';
$room = one($pdo, "SELECT {$capacityColumn} AS capacity FROM {$roomTable} WHERE {$roomColumn} = ?", [$roomID]);
if (!$room || ($seatsChanged && $seats > min(10, (int) $room['capacity']))) {
    $error = 'The selected room does not have enough capacity.';
    return;
}
if (one($pdo, "SELECT CRN FROM Course_Section WHERE semester_ID = ? AND time_Slot_ID = ? AND {$roomColumn} = ? AND is_Cancelled = 0 AND CRN <> ? LIMIT 1", [(int) $section['semester_ID'], $timeSlotID, $roomID, $crn])) {
    $error = 'This classroom is already assigned at that time.';
    return;
}

[$canTeach, $teachMessage] = faculty_can_teach_section(
	$pdo,
	$facultyID,
	$timeSlotID,
	$crn
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

$pdo->prepare(
	'UPDATE Course_Section
	SET course_ID = ?, section_No = ?, faculty_ID = ?, time_Slot_ID = ?,
		lecture_ID = ?, lab_ID = ?, semester_ID = ?, available_Seats = ?
	WHERE CRN = ?'
)->execute([
	$courseID,
	$sectionNo,
	$facultyID,
	$timeSlotID,
	$lectureID,
	$labID,
	(int) $section['semester_ID'],
	max(0, $seats),
	$crn
]);

audit_log($pdo, 'Update', 'Course_Section', (string) $crn, 'Section updated');

if (master_schedule_get($pdo, (int) $section['semester_ID']) !== null) {
	master_schedule_touch($pdo, (int) $section['semester_ID'], $adminID);
}

redirect_with_flash(
	url('admin/sections.php'),
	'success',
	'Section updated.'
);

<?php

declare(strict_types=1);

// Update a section.
//
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

$courseID = post_int('course_id') ?? (int) $section['course_ID'];
$facultyID = post_int('faculty_id') ?? (int) $section['faculty_ID'];
$timeSlotID = post_int('time_slot_id') ?? (int) $section['time_Slot_ID'];
$sectionNo = post_int('section_no') ?? (int) $section['section_No'];
$roomType = post_str('room_type', 10) ?: ($section['lecture_ID'] !== null ? 'lecture' : 'lab');
$roomID = post_int('room_id') ?? ($section['lecture_ID'] ?? $section['lab_ID']);
$seats = post_int('seats') ?? (int) $section['available_Seats'];

if ($locked) {

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

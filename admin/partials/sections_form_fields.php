<?php

declare(strict_types=1);

// Section form fields. When the schedule is locked, fields are disabled
// and hidden inputs keep the submitted values.

?>

<div>
	<label for="semester_id">Semester</label>
	<select name="semester_id" id="semester_id" required <?= $locked ? 'disabled' : '' ?>>
		<option value="">Select semester</option>
		<?php foreach ($semesters as $semester): ?>
			<option
				value="<?= e($semester['semester_ID']) ?>"
				<?= $editSection !== null && (int) $editSection['semester_ID'] === (int) $semester['semester_ID'] ? 'selected' : '' ?>
			>
				<?= e($semester['semester_Name']) ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>

<div>
	<label for="course_id">Course</label>
	<select name="course_id" id="course_id" required <?= $locked ? 'disabled' : '' ?>>
		<option value="">Select course</option>
		<?php foreach ($courses as $course): ?>
			<option
				value="<?= e($course['course_ID']) ?>"
            data-dept="<?= e((string) ($course['dept_ID'] ?? '')) ?>"
				<?= $editSection !== null && (string) $editSection['course_ID'] === (string) $course['course_ID'] ? 'selected' : '' ?>
			>
				<?= e($course['course_ID']) ?> -
				<?= e($course['course_Name']) ?>
				(<?= e($course['dept_Name']) ?>)
			</option>
		<?php endforeach; ?>
	</select>
</div>

<div>
    <label for="section_no">Section number</label>
    <select name="section_no" id="section_no" required <?= $locked ? 'disabled' : '' ?>>
        <option value="">Select section number</option>
        <?php for ($n = 1; $n <= 999; $n++): ?>
            <option value="<?= $n ?>" <?= $editSection !== null && (int)$editSection['section_No'] === $n ? 'selected' : '' ?>>
                <?= str_pad((string)$n, 3, '0', STR_PAD_LEFT) ?>
            </option>
        <?php endfor; ?>
    </select>
    <small id="create-section-availability" role="status" aria-live="polite">Choose semester and course to check availability.</small>
</div>

<div>
	<label for="faculty_id">Instructor</label>
	<select name="faculty_id" id="faculty_id" required <?= $locked ? 'disabled' : '' ?>>
		<option value="">Select faculty</option>
		<?php foreach ($faculty as $member): ?>
			<option
				value="<?= e($member['faculty_ID']) ?>"
				<?= $editSection !== null && (int) $editSection['faculty_ID'] === (int) $member['faculty_ID'] ? 'selected' : '' ?>
			>
				<?= e($member['name']) ?> (<?= e($member['faculty_Type']) ?>)
			</option>
		<?php endforeach; ?>
	</select>
</div>



<div>
	<label for="time_slot_id">Meeting day and time</label>
	<select name="time_slot_id" id="time_slot_id" required <?= $locked ? 'disabled' : '' ?>>
		<option value="">Select time slot</option>
		<?php foreach ($timeSlots as $slot): ?>
			<option
				value="<?= e($slot['time_Slot_ID']) ?>"
				<?= $editSection !== null && (int) $editSection['time_Slot_ID'] === (int) $slot['time_Slot_ID'] ? 'selected' : '' ?>
			>
				<?= e(time_slot_label($pdo, (int) $slot['time_Slot_ID'])) ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>

<div>
	<label for="room_type">Room type</label>
	<select name="room_type" id="room_type" <?= $locked ? 'disabled' : '' ?>>
		<option value="lecture" <?= $editSection !== null && $editSection['lecture_ID'] !== null ? 'selected' : '' ?>>Lecture room</option>
		<option value="lab" <?= $editSection !== null && $editSection['lab_ID'] !== null ? 'selected' : '' ?>>Laboratory</option>
	</select>
</div>

<div>
	<label for="room_id">Classroom / laboratory</label>
	<select name="room_id" id="room_id" <?= $locked ? 'disabled' : '' ?>>
		<option value="">Select room</option>
		<?php foreach ($lectureRooms as $room): ?>
			<option
				value="<?= e($room['lecture_ID']) ?>"
				data-room-type="lecture"
				<?= $editSection !== null && $editSection['lecture_ID'] !== null && (int) $editSection['lecture_ID'] === (int) $room['lecture_ID'] ? 'selected' : '' ?>
			>
				<?= e($room['Bldg_Name']) ?> <?= e($room['room_Number']) ?>
				(seats <?= e($room['avail_Seats']) ?>)
			</option>
		<?php endforeach; ?>
		<?php foreach ($labRooms as $room): ?>
			<option
				value="<?= e($room['lab_ID']) ?>"
				data-room-type="lab"
				<?= $editSection !== null && $editSection['lab_ID'] !== null && (int) $editSection['lab_ID'] === (int) $room['lab_ID'] ? 'selected' : '' ?>
			>
				<?= e($room['Bldg_Name']) ?> <?= e($room['room_Number']) ?>
				(workstations <?= e($room['no_Of_Workstations']) ?>)
			</option>
		<?php endforeach; ?>
	</select>
</div>

<div style="grid-column:1/-1;border:1px solid #e0e7f0;border-radius:14px;padding:16px;background:#f8faff">
  <h3 style="margin:0 0 12px">Enrollment</h3>
  <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px">
    <div><label for="create_enrolled">Currently Enrolled</label>
      <input type="number" id="create_enrolled" name="demo_enrolled" min="0" max="10" step="1" value="0" required></div>
    <div><label for="seats">Seats Remaining</label>
      <input type="number" id="seats" name="seats" min="0" max="10" step="1" value="10" required></div>
  </div>
  <small id="create-enrollment-validation" role="status" aria-live="polite">Both values must be 0–10, and their sum cannot exceed 10.</small>
</div>

<?php if ($locked): ?>
	<?php /* Disabled fields are not submitted; keep the values server-side. */ ?>
	<input type="hidden" name="course_id" value="<?= e((string) $editSection['course_ID']) ?>">
	<input type="hidden" name="section_no" value="<?= e((string) $editSection['section_No']) ?>">
	<input type="hidden" name="faculty_id" value="<?= e((string) $editSection['faculty_ID']) ?>">
	<input type="hidden" name="semester_id" value="<?= e((string) $editSection['semester_ID']) ?>">
	<input type="hidden" name="time_slot_id" value="<?= e((string) $editSection['time_Slot_ID']) ?>">
	<input type="hidden" name="room_type" value="<?= e($editSection['lecture_ID'] !== null ? 'lecture' : 'lab') ?>">
	<input type="hidden" name="room_id" value="<?= e((string) ($editSection['lecture_ID'] ?? $editSection['lab_ID'] ?? '')) ?>">
<?php endif; ?>

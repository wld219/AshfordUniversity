<?php

declare(strict_types=1);

// Edit popup fields. When the schedule is locked, fields are disabled
// and hidden inputs keep the submitted values.

?>

<div>
	<label for="<?= e($fieldIdPrefix) ?>course_id">Course</label>
	<select name="course_id" id="<?= e($fieldIdPrefix) ?>course_id" required >
		<option value="">Select course</option>
		<?php foreach ($courses as $course): ?>
			<option
				value="<?= e($course['course_ID']) ?>" data-dept="<?= e($course['dept_Name']) ?>"
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
	<label for="<?= e($fieldIdPrefix) ?>section_no">Section number</label>
	<input
		type="number"
		id="<?= e($fieldIdPrefix) ?>section_no"
		name="section_no"
		min="1"
		value="<?= e((string) ($editSection['section_No'] ?? '')) ?>"
		required
		
	>
</div>

<div>
	<label for="<?= e($fieldIdPrefix) ?>faculty_id">Instructor</label>
	<select name="faculty_id" id="<?= e($fieldIdPrefix) ?>faculty_id" required >
		<option value="">Select faculty</option>
		<?php foreach ($faculty as $member): ?>
			<option
				value="<?= e($member['faculty_ID']) ?>" data-faculty-type="<?= e($member['faculty_Type']) ?>"
				<?= $editSection !== null && (int) $editSection['faculty_ID'] === (int) $member['faculty_ID'] ? 'selected' : '' ?>
			>
				<?= e($member['name']) ?> (<?= e($member['faculty_Type']) ?>)
			</option>
		<?php endforeach; ?>
	</select>
</div>

<div>
	<label for="<?= e($fieldIdPrefix) ?>semester_id">Semester</label>
	<select name="semester_id" id="<?= e($fieldIdPrefix) ?>semester_id" required >
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

<div class="meeting-schedule-card">
  <div class="meeting-slot-control">
	<label for="<?= e($fieldIdPrefix) ?>time_slot_id">Meeting Schedule</label>
	<select name="time_slot_id" id="<?= e($fieldIdPrefix) ?>time_slot_id" required >
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
  <div class="meeting-preview-row">
    <div><span class="meeting-preview-label">Meeting Days</span><strong data-meeting-days>—</strong></div>
    <div><span class="meeting-preview-label">Meeting Time</span><strong data-meeting-time>—</strong></div>
  </div>
</div>

<div class="room-assignment-card">
  <h3>Room Assignment</h3>
  <div class="room-assignment-row">
    <div>
	<label for="<?= e($fieldIdPrefix) ?>room_type">Room Type</label>
	<select name="room_type" id="<?= e($fieldIdPrefix) ?>room_type" >
		<option value="lecture" <?= $editSection !== null && $editSection['lecture_ID'] !== null ? 'selected' : '' ?>>Lecture room</option>
		<option value="lab" <?= $editSection !== null && $editSection['lab_ID'] !== null ? 'selected' : '' ?>>Laboratory</option>
</select>
    </div>
    <div>
	<label for="<?= e($fieldIdPrefix) ?>room_id">Available Room / Laboratory</label>
	<select name="room_id" id="<?= e($fieldIdPrefix) ?>room_id" >
		<option value="">Select room</option>
		<?php foreach ($lectureRooms as $room): ?>
			<option
				value="<?= e($room['lecture_ID']) ?>" data-capacity="<?= (int) $room['avail_Seats'] ?>"
				data-room-type="lecture"
				<?= $editSection !== null && $editSection['lecture_ID'] !== null && (int) $editSection['lecture_ID'] === (int) $room['lecture_ID'] ? 'selected' : '' ?>
			>
				<?= e($room['Bldg_Name']) ?> <?= e($room['room_Number']) ?>
				(seats <?= e($room['avail_Seats']) ?>)
			</option>
		<?php endforeach; ?>
		<?php foreach ($labRooms as $room): ?>
			<option
				value="<?= e($room['lab_ID']) ?>" data-capacity="<?= (int) $room['no_Of_Workstations'] ?>"
				data-room-type="lab"
				<?= $editSection !== null && $editSection['lab_ID'] !== null && (int) $editSection['lab_ID'] === (int) $room['lab_ID'] ? 'selected' : '' ?>
			>
				<?= e($room['Bldg_Name']) ?> <?= e($room['room_Number']) ?>
				(workstations <?= e($room['no_Of_Workstations']) ?>)
			</option>
		<?php endforeach; ?>
</select>
    </div>
  </div>
</div>

<div class="enrollment-panel">
  <div class="enrollment-title"><h3>Enrollment</h3><span>Maximum 10 students</span></div>
  <div class="enrollment-row">
    <div>
      <label for="<?= e($fieldIdPrefix) ?>demo_enrolled">Currently Enrolled</label>
      <input type="number" id="<?= e($fieldIdPrefix) ?>demo_enrolled" name="demo_enrolled" min="0" max="10" step="1" value="<?= e((string) ($ashfordDemoOverrides[$editCRN] ?? (int) ($editSection['enrolled'] ?? 0))) ?>" required>
    </div>
    <div>
      <label for="<?= e($fieldIdPrefix) ?>seats">Seats Remaining</label>
      <input type="number" id="<?= e($fieldIdPrefix) ?>seats" name="seats" min="0" max="10" step="1" value="<?= e((string) ($editSection['available_Seats'] ?? '10')) ?>" required>
    </div>
  </div>
  <small>Both values are manually entered (0–10 each). Their combined total cannot exceed 10. Actual registrations are unchanged.</small>
  <small class="actual-enrollment-note">Actual registrations: <?= (int) ($editSection['enrolled'] ?? 0) ?></small>
</div>

<?php if (false): ?>
	<?php /* Disabled fields are not submitted; keep the values server-side. */ ?>
	<input type="hidden" name="course_id" value="<?= e((string) $editSection['course_ID']) ?>">
	<input type="hidden" name="section_no" value="<?= e((string) $editSection['section_No']) ?>">
	<?php if ($locked): ?>
	<input type="hidden" name="faculty_id" value="<?= e((string) $editSection['faculty_ID']) ?>">
	<?php endif; ?>
	<input type="hidden" name="semester_id" value="<?= e((string) $editSection['semester_ID']) ?>">
	<?php if ($locked): ?>
	<input type="hidden" name="time_slot_id" value="<?= e((string) $editSection['time_Slot_ID']) ?>">
	<input type="hidden" name="room_type" value="<?= e($editSection['lecture_ID'] !== null ? 'lecture' : 'lab') ?>">
	<input type="hidden" name="room_id" value="<?= e((string) ($editSection['lecture_ID'] ?? $editSection['lab_ID'] ?? '')) ?>">
<?php endif; ?>
<?php endif; ?>

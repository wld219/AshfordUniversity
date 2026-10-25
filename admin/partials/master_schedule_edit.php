<?php

declare(strict_types=1);

// Section edit form. Schedule fields lock after the semester starts.

?>

<section class="card">

	<h2>Edit section <?= e((string) $editCRN) ?></h2>

	<form method="post" class="form-grid" style="margin-top: 16px;">

		<?= csrf_field() ?>
		<input type="hidden" name="action" value="update_section">
		<input type="hidden" name="crn" value="<?= e((string) $editCRN) ?>">
		<input type="hidden" name="semester_id" value="<?= e((string) $semesterID) ?>">
		<?php if ($schedule !== null): ?>
			<input type="hidden" name="version" value="<?= e((string) $schedule['version']) ?>">
		<?php endif; ?>

		<div>
			<label for="faculty_id">Faculty</label>
			<select name="faculty_id" id="faculty_id" required <?= $scheduleLocked ? 'disabled' : '' ?>>
				<?php foreach ($facultyList as $member): ?>
					<option
						value="<?= e($member['faculty_ID']) ?>"
						<?= (int) $editSection['faculty_ID'] === (int) $member['faculty_ID'] ? 'selected' : '' ?>
					>
						<?= e($member['name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="time_slot_id">Meeting time</label>
			<select name="time_slot_id" id="time_slot_id" required <?= $scheduleLocked ? 'disabled' : '' ?>>
				<?php foreach ($timeSlots as $slot): ?>
					<option
						value="<?= e($slot['time_Slot_ID']) ?>"
						<?= (int) $editSection['time_Slot_ID'] === (int) $slot['time_Slot_ID'] ? 'selected' : '' ?>
					>
						Slot <?= e($slot['time_Slot_ID']) ?> -
						<?= e(time_slot_label($pdo, (int) $slot['time_Slot_ID'])) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="room_type">Room type</label>
			<select name="room_type" id="room_type" <?= $scheduleLocked ? 'disabled' : '' ?>>
				<option value="lecture" <?= $editSection['lecture_ID'] !== null ? 'selected' : '' ?>>Lecture room</option>
				<option value="lab" <?= $editSection['lab_ID'] !== null ? 'selected' : '' ?>>Laboratory</option>
			</select>
		</div>

		<div>
			<label for="room_id">Room</label>
			<select name="room_id" id="room_id" <?= $scheduleLocked ? 'disabled' : '' ?>>
				<option value="">Unassigned</option>
				<?php foreach ($lectureRooms as $room): ?>
					<option
						value="<?= e($room['lecture_ID']) ?>"
						data-room-type="lecture"
						<?= $editSection['lecture_ID'] !== null && (int) $editSection['lecture_ID'] === (int) $room['lecture_ID'] ? 'selected' : '' ?>
					>
						<?= e($room['Bldg_Name']) ?> <?= e($room['room_Number']) ?> (lecture)
					</option>
				<?php endforeach; ?>
				<?php foreach ($labRooms as $room): ?>
					<option
						value="<?= e($room['lab_ID']) ?>"
						data-room-type="lab"
						<?= $editSection['lab_ID'] !== null && (int) $editSection['lab_ID'] === (int) $room['lab_ID'] ? 'selected' : '' ?>
					>
						<?= e($room['Bldg_Name']) ?> <?= e($room['room_Number']) ?> (lab)
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="seats">Available seats</label>
			<input
				type="number"
				id="seats"
				name="seats"
				min="0"
				value="<?= e((string) $editSection['available_Seats']) ?>"
			>
		</div>

		<div class="form-actions" style="grid-column: 1 / -1;">
			<button type="submit" class="btn">Save section</button>
			<a class="btn light" href="master-schedule.php?semester=<?= e((string) $semesterID) ?>">Cancel</a>
		</div>
	</form>

</section>

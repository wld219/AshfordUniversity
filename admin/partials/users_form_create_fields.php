<?php

declare(strict_types=1);

// Role-specific fields for the create-account form.

?>

<div class="student-fields">
	<label for="major_id">Major (students)</label>
	<select name="major_id" id="major_id">
		<option value="">Not applicable</option>
		<?php foreach ($majors as $major): ?>
			<option value="<?= e($major['major_ID']) ?>">
				<?= e($major['major_Name']) ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>

<div class="student-fields">
	<label for="student_type">Level (students)</label>
	<select name="student_type" id="student_type">
		<option value="Undergraduate">Undergraduate</option>
		<option value="Graduate">Graduate</option>
	</select>
</div>

<div class="student-fields">
	<label for="undergraduate_student_type">Enrollment (undergraduates)</label>
	<select name="undergraduate_student_type" id="undergraduate_student_type">
		<option value="Full-Time">Full-Time</option>
		<option value="Part-Time">Part-Time</option>
	</select>
</div>

<div class="faculty-fields">
	<label for="office_id">Office (faculty)</label>
	<select name="office_id" id="office_id">
		<option value="">Not applicable</option>
		<?php foreach ($offices as $office): ?>
			<option value="<?= e($office['office_ID']) ?>">
				<?= e($office['Bldg_Name']) ?> - <?= e($office['room_Number']) ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>

<div class="faculty-fields">
	<label for="specialty">Specialty (faculty)</label>
	<input
		type="text"
		name="specialty"
		maxlength="100"
	>
</div>

<div class="faculty-fields">
	<label for="faculty_rank">Rank (faculty)</label>
	<input
		type="text"
		name="faculty_rank"
		maxlength="50"
		placeholder="Assistant Professor"
	>
</div>

<div class="faculty-fields">
	<label for="faculty_type">Type (faculty)</label>
	<select name="faculty_type" id="faculty_type">
		<option value="Full-Time">Full-Time</option>
		<option value="Part-Time">Part-Time</option>
	</select>
</div>

<div class="admin-fields">
	<label for="security_level">Access level (admins)</label>
	<select name="security_level" id="security_level">
		<option value="Full-Access">Full-Access</option>
		<option value="Read-Only">Read-Only</option>
	</select>
</div>

<div class="statstaff-fields">
	<label for="employment_type">Employment (statistics staff)</label>
	<select name="employment_type" id="employment_type">
		<option value="Full-Time">Full-Time</option>
		<option value="Part-Time">Part-Time</option>
	</select>
</div>

<?php

declare(strict_types=1);

// Programs and requirements forms (Full-Access administrators only).

?>

<section class="card">

	<h2>Programs and requirements</h2>

	<form method="post" class="filters" style="margin-top: 16px;">
		<?= csrf_field() ?>
		<input type="hidden" name="action" value="create_major">

		<div>
			<label for="major_id">Major ID</label>
			<input type="number" id="major_id" name="major_id" min="1" required>
		</div>

		<div>
			<label for="major_name">Major name</label>
			<input type="text" id="major_name" name="major_name" maxlength="100" required>
		</div>

		<div>
			<label for="major_dept">Department</label>
			<select name="dept_id" id="major_dept" required>
				<option value="">Select</option>
				<?php foreach ($departments as $department): ?>
					<option value="<?= e($department['dept_ID']) ?>"><?= e($department['dept_Name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">Create major</button>
		</div>
	</form>

	<form method="post" class="filters" style="margin-top: 12px;">
		<?= csrf_field() ?>
		<input type="hidden" name="action" value="create_minor">

		<div>
			<label for="minor_id">Minor ID</label>
			<input type="number" id="minor_id" name="minor_id" min="1" required>
		</div>

		<div>
			<label for="minor_name">Minor name</label>
			<input type="text" id="minor_name" name="minor_name" maxlength="100" required>
		</div>

		<div>
			<label for="minor_dept">Department</label>
			<select name="dept_id" id="minor_dept" required>
				<option value="">Select</option>
				<?php foreach ($departments as $department): ?>
					<option value="<?= e($department['dept_ID']) ?>"><?= e($department['dept_Name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">Create minor</button>
		</div>
	</form>

	<hr>

	<form method="post" class="filters" style="margin-top: 12px;">
		<?= csrf_field() ?>
		<input type="hidden" name="action" value="add_requirement">

		<div>
			<label for="program_type">Program type</label>
			<select name="program_type" id="program_type">
				<option value="major">Major requirement</option>
				<option value="minor">Minor requirement</option>
			</select>
		</div>

		<div>
			<label for="program_id">Program ID</label>
			<input type="number" id="program_id" name="program_id" min="1" required>
		</div>

		<div>
			<label for="req_course">Course</label>
			<select name="course_id" id="req_course" required>
				<option value="">Select course</option>
				<?php foreach ($courseOptions as $course): ?>
					<option value="<?= e($course['course_ID']) ?>">
						<?= e($course['course_ID']) ?> - <?= e($course['course_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="req_min_grade">Minimum grade</label>
			<select name="min_grade" id="req_min_grade">
				<?php foreach (['A', 'B+', 'B', 'C+', 'C', 'D'] as $grade): ?>
					<option value="<?= e($grade) ?>"><?= e($grade) ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">Add requirement</button>
		</div>
	</form>

</section>

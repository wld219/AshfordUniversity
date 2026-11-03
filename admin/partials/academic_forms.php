<?php

declare(strict_types=1);

// Course form + prerequisite form (Full-Access administrators only).

?>

<section class="card">

	<h2><?= $editCourse !== null ? 'Edit course ' . e((string) $editCourseID) : 'Create a course' ?></h2>

	<form method="post" class="form-grid" style="margin-top: 16px;">

		<?= csrf_field() ?>
		<input type="hidden" name="action" value="save_course">

		<?php if ($editCourse !== null): ?>
			<input type="hidden" name="existing_course_id" value="<?= e((string) $editCourseID) ?>">
		<?php endif; ?>

		<div>
			<label for="course_id">Course ID</label>
			<input
				type="number"
				id="course_id"
				name="course_id"
				min="1"
				value="<?= e((string) ($editCourse['course_ID'] ?? '')) ?>"
				required
			>
		</div>

		<div>
			<label for="dept_id">Department</label>
			<select name="dept_id" id="dept_id" required>
				<option value="">Select department</option>
				<?php foreach ($departments as $department): ?>
					<option
						value="<?= e($department['dept_ID']) ?>"
						<?= $editCourse !== null && (int) $editCourse['dept_ID'] === (int) $department['dept_ID'] ? 'selected' : '' ?>
					>
						<?= e($department['dept_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="course_name">Course name</label>
			<input
				type="text"
				id="course_name"
				name="course_name"
				maxlength="100"
				value="<?= e($editCourse['course_Name'] ?? '') ?>"
				required
			>
		</div>

		<div>
			<label for="credits">Credits</label>
			<input
				type="number"
				id="credits"
				name="credits"
				min="1"
				max="6"
				value="<?= e((string) ($editCourse['course_Credits'] ?? '')) ?>"
				required
			>
		</div>

		<div>
			<label for="course_type">Level</label>
			<select name="course_type" id="course_type" required>
				<option value="Undergraduate" <?= ($editCourse['course_Type'] ?? '') === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate</option>
				<option value="Graduate" <?= ($editCourse['course_Type'] ?? '') === 'Graduate' ? 'selected' : '' ?>>Graduate</option>
			</select>
		</div>

		<div style="grid-column: 1 / -1;">
			<label for="course_dsc">Description</label>
			<textarea
				id="course_dsc"
				name="course_dsc"
				rows="3"
				maxlength="2000"
			><?= e($editCourse['course_DSC'] ?? '') ?></textarea>
		</div>

		<div class="form-actions" style="grid-column: 1 / -1;">
			<button type="submit" class="btn">
				<?= $editCourse !== null ? 'Save course' : 'Create course' ?>
			</button>

			<?php if ($editCourse !== null): ?>
				<a href="academic.php" class="btn light">Cancel</a>
			<?php endif; ?>
		</div>

	</form>

</section>


<section class="card">

	<h2>Add a prerequisite</h2>

	<form method="post" class="filters" style="margin-top: 16px;">
		<?= csrf_field() ?>
		<input type="hidden" name="action" value="add_prerequisite">

		<div>
			<label for="prereq_for">Course</label>
			<select name="course_id" id="prereq_for" required>
				<option value="">Select course</option>
				<?php foreach ($courseOptions as $course): ?>
					<option value="<?= e($course['course_ID']) ?>">
						<?= e($course['course_ID']) ?> - <?= e($course['course_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="prereq_of">Requires</label>
			<select name="prereq_id" id="prereq_of" required>
				<option value="">Select prerequisite</option>
				<?php foreach ($courseOptions as $course): ?>
					<option value="<?= e($course['course_ID']) ?>">
						<?= e($course['course_ID']) ?> - <?= e($course['course_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="min_grade">Minimum grade</label>
			<select name="min_grade" id="min_grade">
				<?php foreach (['A', 'B+', 'B', 'C+', 'C', 'D'] as $grade): ?>
					<option value="<?= e($grade) ?>"><?= e($grade) ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">Add</button>
		</div>
	</form>

</section>

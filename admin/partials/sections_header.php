<?php

declare(strict_types=1);

// Sections header: access badge, error message, filters.

?>

<div class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Scheduling</p>
			<h2>Course sections</h2>
		</div>
		<span class="badge"><?= e($securityLevel) ?></span>
	</div>

	<?php if ($error !== ''): ?>
		<div class="alert error"><?= e($error) ?></div>
	<?php endif; ?>

	<form method="get" class="filters" style="margin-top: 16px;">
		<div>
			<label for="semester">Semester</label>
			<select name="semester" id="semester">
				<option value="">All semesters</option>
				<?php foreach ($semesters as $semester): ?>
					<option
						value="<?= e($semester['semester_ID']) ?>"
						<?= $filterSemester === (int) $semester['semester_ID'] ? 'selected' : '' ?>
					>
						<?= e($semester['semester_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="dept">Department</label>
			<select name="dept" id="dept">
				<option value="">All departments</option>
				<?php foreach ($departments as $department): ?>
					<option
						value="<?= e($department['dept_ID']) ?>"
						<?= $filterDept === (int) $department['dept_ID'] ? 'selected' : '' ?>
					>
						<?= e($department['dept_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="search">Search</label>
			<input
				type="search"
				id="search"
				name="search"
				value="<?= e($search) ?>"
				placeholder="Course name or CRN"
			>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">Filter</button>
			<a href="sections.php" class="btn light">Clear</a>
		</div>
	</form>
</div>

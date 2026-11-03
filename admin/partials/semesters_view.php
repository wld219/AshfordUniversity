<?php

declare(strict_types=1);

// Semesters view: form and table.

?>

<div class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Academic calendar</p>
			<h2>Semesters</h2>
		</div>
		<span class="badge"><?= e($securityLevel) ?></span>
	</div>

	<p class="muted">
		Set each semester's start date and its registration windows. The start
		date locks schedule edits once the semester begins; the windows are
		enforced against the current date when they are set.
	</p>

	<?php if ($error !== ''): ?>
		<div class="alert error"><?= e($error) ?></div>
	<?php endif; ?>

</div>


<?php if ($isFullAccess): ?>

	<section class="card">

		<h2><?= $editSemester !== null ? 'Edit semester' : 'Create a semester' ?></h2>

		<form method="post" class="form-grid" style="margin-top: 16px;">

			<?= csrf_field() ?>

			<?php if ($editSemester !== null): ?>

				<input type="hidden" name="action" value="update">
				<input type="hidden" name="semester_id" value="<?= e($editSemester['semester_ID']) ?>">

			<?php else: ?>

				<input type="hidden" name="action" value="create">

				<div>
					<label for="semester_id">Semester ID</label>
					<input type="number" id="semester_id" name="semester_id" min="1" required>
				</div>

				<div>
					<label for="semester_name">Semester name</label>
					<input
						type="text"
						id="semester_name"
						name="semester_name"
						maxlength="50"
						placeholder="Fall 2027"
						required
					>
				</div>

			<?php endif; ?>


			<div>
				<label for="semester_start">Semester start date</label>
				<input
					type="date"
					id="semester_start"
					name="semester_start"
					value="<?= e($editSemester['semester_Start'] ?? '') ?>"
				>
			</div>

			<div>
				<label for="registration_start">Registration opens</label>
				<input
					type="date"
					id="registration_start"
					name="registration_start"
					value="<?= e($editSemester['registration_Start'] ?? '') ?>"
				>
			</div>

			<div>
				<label for="registration_end">Registration closes</label>
				<input
					type="date"
					id="registration_end"
					name="registration_end"
					value="<?= e($editSemester['registration_End'] ?? '') ?>"
				>
			</div>

			<div>
				<label for="drop_deadline">Drop deadline</label>
				<input
					type="date"
					id="drop_deadline"
					name="drop_deadline"
					value="<?= e($editSemester['drop_Deadline'] ?? '') ?>"
				>
			</div>

			<div>
				<label for="grade_deadline">Grade submission deadline</label>
				<input
					type="datetime-local"
					id="grade_deadline"
					name="grade_deadline"
					value="<?= e(
						$editSemester !== null && $editSemester['grade_Deadline'] !== null
							? date('Y-m-d\TH:i', strtotime((string) $editSemester['grade_Deadline']))
							: ''
					) ?>"
				>
			</div>

			<div class="form-actions" style="grid-column: 1 / -1;">
				<button type="submit" class="btn">
					<?= $editSemester !== null ? 'Save settings' : 'Create semester' ?>
				</button>

				<?php if ($editSemester !== null): ?>
					<a href="semesters.php" class="btn light">Cancel</a>
				<?php endif; ?>
			</div>

		</form>

	</section>

<?php endif; ?>


<div class="table-wrap">

	<table>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Starts</th>
			<th>Registration</th>
			<th>Drop deadline</th>
			<th>Grade deadline</th>
			<?php if ($isFullAccess): ?>
				<th>Action</th>
			<?php endif; ?>
		</tr>

		<?php foreach ($semesters as $semester): ?>

			<tr>
				<td><?= e($semester['semester_ID']) ?></td>
				<td><?= e($semester['semester_Name']) ?></td>
				<td><?= e($semester['semester_Start'] ?? '—') ?></td>
				<td>
					<?= e($semester['registration_Start'] ?? '—') ?>
					&rarr;
					<?= e($semester['registration_End'] ?? '—') ?>
				</td>
				<td><?= e($semester['drop_Deadline'] ?? '—') ?></td>
				<td><?= e($semester['grade_Deadline'] ?? '—') ?></td>
				<?php if ($isFullAccess): ?>
					<td>
						<a class="btn small" href="semesters.php?edit=<?= e($semester['semester_ID']) ?>">
							Edit
						</a>
					</td>
				<?php endif; ?>
			</tr>

		<?php endforeach; ?>

	</table>

</div>

<?php

declare(strict_types=1);

// Header: title, schedule status, semester selector, action buttons.

?>

<div class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Scheduling</p>
			<h2>Ashford University &mdash; Master Schedule</h2>
		</div>

		<?php if ($schedule !== null): ?>
			<span class="badge"><?= e((string) $schedule['status']) ?></span>
		<?php else: ?>
			<span class="badge">No schedule</span>
		<?php endif; ?>
	</div>


	<form method="get" class="filters" style="margin-top: 16px;">
		<div>
			<label for="semester">Semester</label>
			<select name="semester" id="semester" onchange="ashfordChangeSemester(this)">
				<?php foreach ($semesters as $row): ?>
					<option
						value="<?= e($row['semester_ID']) ?>"
						<?= $semesterID === (int) $row['semester_ID'] ? 'selected' : '' ?>
					>
						<?= e($row['semester_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	</form>


    <script>
    function ashfordChangeSemester(select) {
        const url = new URL(window.location.href);
        url.searchParams.set('semester', select.value);
        url.searchParams.delete('page');
        url.searchParams.delete('edit');
        window.location.assign(url.toString());
    }
    </script>


	<?php if ($semester !== null): ?>

		<p>
			<strong>Schedule status:</strong>

			<?php if ($schedule === null): ?>
				No master schedule has been created for
				<?= e($semester['semester_Name']) ?> yet.
			<?php else: ?>
				<?= e((string) $schedule['status']) ?> (version
				<?= e((string) $schedule['version']) ?>).

				<?php if ($schedule['created_At'] !== null): ?>
					Created <?= e((string) $schedule['created_At']) ?>.
				<?php endif; ?>

				<?php if ($schedule['updated_At'] !== null): ?>
					Last updated <?= e((string) $schedule['updated_At']) ?>.
				<?php endif; ?>
			<?php endif; ?>
		</p>


		<?php if ($scheduleLocked): ?>
			<div class="alert error">
				This semester has started. Room, faculty, and meeting-time
				changes are locked; only seat counts may be updated.
			</div>
		<?php endif; ?>


		<?php if ($error !== ''): ?>
			<div class="alert error"><?= e($error) ?></div>
		<?php endif; ?>


		<?php if ($isFullAccess): ?>

			<div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 12px;">

				<?php if ($schedule === null): ?>

					<form method="post">
						<?= csrf_field() ?>
						<input type="hidden" name="action" value="create">
						<input type="hidden" name="semester_id" value="<?= e((string) $semesterID) ?>">
						<button type="submit" class="btn">
							Create New Master Schedule
						</button>
					</form>

				<?php else: ?>

					<form
						method="post"
						onsubmit="return confirm('Publish this schedule? Only a validated, conflict-free schedule can be published.');"
					>
						<?= csrf_field() ?>
						<input type="hidden" name="action" value="publish">
						<input type="hidden" name="semester_id" value="<?= e((string) $semesterID) ?>">
						<input type="hidden" name="version" value="<?= e((string) $schedule['version']) ?>">
						<button
							type="submit"
							class="btn"
							<?= $validation['valid'] ? '' : 'disabled' ?>
						>
							Publish Schedule
						</button>
					</form>

					<form method="post">
						<?= csrf_field() ?>
						<input type="hidden" name="action" value="reopen">
						<input type="hidden" name="semester_id" value="<?= e((string) $semesterID) ?>">
						<input type="hidden" name="version" value="<?= e((string) $schedule['version']) ?>">
						<button type="submit" class="btn light">
							Reopen for Editing
						</button>
					</form>

				<?php endif; ?>

			</div>

		<?php endif; ?>

	<?php endif; ?>

</div>

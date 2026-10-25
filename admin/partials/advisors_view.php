<?php

declare(strict_types=1);

// Advisors view.

?>

<div class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Advising</p>
			<h2>Advisor assignments</h2>
		</div>
		<span class="badge"><?= e($securityLevel) ?></span>
	</div>

	<p class="muted">
		Every student must have at least one advisor. Full-time faculty may
		advise at most 15 students; part-time faculty may not advise.
	</p>

	<?php if ($error !== ''): ?>
		<div class="alert error"><?= e($error) ?></div>
	<?php endif; ?>


	<?php if ($isFullAccess): ?>

		<form method="post" class="filters" style="margin-top: 16px;">
			<?= csrf_field() ?>
			<input type="hidden" name="action" value="assign">

			<div>
				<label for="faculty_id">Faculty advisor</label>
				<select name="faculty_id" id="faculty_id" required>
					<option value="">Select faculty</option>
					<?php foreach ($facultyList as $member): ?>
						<option value="<?= e($member['faculty_ID']) ?>">
							<?= e($member['name']) ?> (<?= e($member['faculty_Type']) ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div>
				<label for="student_id">Student ID</label>
				<input
					type="number"
					id="student_id"
					name="student_id"
					min="1"
					placeholder="e.g. 100001"
					required
				>
			</div>

			<div>
				<label for="date_Of_Appnt">Appointment date</label>
				<input
					type="date"
					id="date_Of_Appnt"
					name="date_Of_Appnt"
					value="<?= e(date('Y-m-d')) ?>"
				>
			</div>

			<div style="align-self: end;">
				<button type="submit" class="btn">Assign</button>
			</div>
		</form>

	<?php endif; ?>

</div>


<div class="table-wrap">

	<table>
		<tr>
			<th>Advisor</th>
			<th>Type</th>
			<th>Student ID</th>
			<th>Student</th>
			<th>Appointed</th>
			<?php if ($isFullAccess): ?>
				<th>Actions</th>
			<?php endif; ?>
		</tr>

		<?php foreach ($assignments as $assignment): ?>
			<tr>
				<td><?= e($assignment['faculty_Name']) ?></td>
				<td><?= e($assignment['faculty_Type']) ?></td>
				<td><?= e($assignment['student_ID']) ?></td>
				<td><?= e($assignment['student_Name']) ?></td>
				<td><?= e($assignment['date_Of_Appnt']) ?></td>
				<?php if ($isFullAccess): ?>
					<td>
						<form method="post" style="display: inline-block;">
							<?= csrf_field() ?>
							<input type="hidden" name="action" value="remove">
							<input type="hidden" name="faculty_id" value="<?= e($assignment['faculty_ID']) ?>">
							<input type="hidden" name="student_id" value="<?= e($assignment['student_ID']) ?>">
							<button
								type="submit"
								class="btn danger small"
								onclick="return confirm('Remove this advisor assignment?');"
							>
								Remove
							</button>
						</form>
					</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>

	</table>

</div>


<p class="muted">Showing the 200 most recent assignments.</p>

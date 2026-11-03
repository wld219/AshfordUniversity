<?php

declare(strict_types=1);

// Holds view.

?>

<div class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Registration</p>
			<h2>Student holds</h2>
		</div>
		<span class="badge"><?= e($securityLevel) ?></span>
	</div>

	<p class="muted">
		A hold blocks course registration until it is removed.
	</p>

	<?php if ($error !== ''): ?>
		<div class="alert error"><?= e($error) ?></div>
	<?php endif; ?>


	<?php if ($isFullAccess): ?>

		<form method="post" class="filters" style="margin-top: 16px;">
			<?= csrf_field() ?>
			<input type="hidden" name="action" value="add_hold">

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
				<label for="hold_id">Hold type</label>
				<select name="hold_id" id="hold_id" required>
					<option value="">Select hold</option>
					<?php foreach ($holdTypes as $hold): ?>
						<option value="<?= e($hold['hold_ID']) ?>">
							<?= e($hold['hold_Type']) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div>
				<label for="hold_date">Hold date</label>
				<input type="date" id="hold_date" name="hold_date" value="<?= e(date('Y-m-d')) ?>">
			</div>

			<div style="align-self: end;">
				<button type="submit" class="btn">Add hold</button>
			</div>
		</form>

	<?php endif; ?>

</div>


<div class="table-wrap">

	<table>
		<tr>
			<th>Student ID</th>
			<th>Student</th>
			<th>Hold</th>
			<th>Date</th>
			<?php if ($isFullAccess): ?>
				<th>Actions</th>
			<?php endif; ?>
		</tr>

		<?php foreach ($holds as $hold): ?>
			<tr>
				<td><?= e($hold['student_ID']) ?></td>
				<td><?= e($hold['student_Name']) ?></td>
				<td><?= e($hold['hold_Type']) ?></td>
				<td><?= e($hold['hold_Date']) ?></td>
				<?php if ($isFullAccess): ?>
					<td>
						<form method="post" style="display: inline-block;">
							<?= csrf_field() ?>
							<input type="hidden" name="action" value="remove_hold">
							<input type="hidden" name="student_id" value="<?= e($hold['student_ID']) ?>">
							<input type="hidden" name="hold_id" value="<?= e($hold['hold_ID']) ?>">
							<button
								type="submit"
								class="btn danger small"
								onclick="return confirm('Remove this hold?');"
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


<p class="muted">Showing the 200 most recent holds.</p>

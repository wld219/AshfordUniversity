<?php

declare(strict_types=1);

// Grade exceptions view.

?>

<div class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Grading</p>
			<h2>Grade exceptions</h2>
		</div>
		<span class="badge"><?= e($securityLevel) ?></span>
	</div>

	<p class="muted">
		Approve a late grade submission for a specific enrollment. Once
		approved, the course instructor may submit grades for that section
		even after the semester grade deadline.
	</p>

	<?php if ($error !== ''): ?>
		<div class="alert error"><?= e($error) ?></div>
	<?php endif; ?>


	<?php if ($isFullAccess): ?>

		<form method="post" class="filters" style="margin-top: 16px;">
			<?= csrf_field() ?>
			<input type="hidden" name="action" value="approve">

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
				<label for="crn">Section CRN</label>
				<input
					type="number"
					id="crn"
					name="crn"
					min="1"
					placeholder="e.g. 10001"
					required
				>
			</div>

			<div>
				<label for="reason">Reason</label>
				<input
					type="text"
					id="reason"
					name="reason"
					maxlength="255"
					placeholder="e.g. Documented medical deferral"
					required
				>
			</div>

			<div style="align-self: end;">
				<button type="submit" class="btn">Approve exception</button>
			</div>
		</form>

	<?php endif; ?>

</div>


<div class="table-wrap">

	<table>
		<tr>
			<th>ID</th>
			<th>Student ID</th>
			<th>CRN</th>
			<th>Reason</th>
			<th>Approved by</th>
			<th>Approved at</th>
			<?php if ($isFullAccess): ?>
				<th>Actions</th>
			<?php endif; ?>
		</tr>

		<?php foreach ($exceptions as $exception): ?>
			<tr>
				<td><?= e($exception['exception_ID']) ?></td>
				<td><?= e($exception['student_ID']) ?></td>
				<td><?= e($exception['CRN']) ?></td>
				<td><?= e($exception['reason']) ?></td>
				<td><?= e($exception['approver_Name'] ?? 'Unknown') ?></td>
				<td><?= e($exception['created_At']) ?></td>
				<?php if ($isFullAccess): ?>
					<td>
						<form method="post" style="display: inline-block;">
							<?= csrf_field() ?>
							<input type="hidden" name="action" value="remove">
							<input type="hidden" name="exception_id" value="<?= e($exception['exception_ID']) ?>">
							<button
								type="submit"
								class="btn danger small"
								onclick="return confirm('Remove this grade exception?');"
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


<p class="muted">Showing the 100 most recent grade exceptions.</p>

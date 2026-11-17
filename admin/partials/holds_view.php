<?php

declare(strict_types=1);

// Holds view — with resolution workflow.

?>

<style>
/* ASHFORD_HOLDS_V2 */
.hold-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}
.hold-status-badge.active {
    background: #fdf3f3;
    color: #a83232;
    border: 1px solid #f3c6c6;
}
.hold-status-badge.resolved {
    background: #f0f7f0;
    color: #245b2b;
    border: 1px solid #c6e0c6;
}
.hold-reason {
    font-size: 12px;
    color: #63718d;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.hold-resolved-by {
    font-size: 11px;
    color: #63718d;
}
.hold-actions-resolve {
    display: flex;
    gap: 6px;
    flex-direction: column;
}
.hold-actions-resolve .btn {
    min-height: auto;
    padding: 6px 12px;
    font-size: 12px;
}
.resolve-dialog {
    border: 0;
    border-radius: 14px;
    padding: 0;
    width: min(460px, calc(100% - 32px));
    max-height: 85vh;
    box-shadow: 0 22px 70px #0f172a55;
}
.resolve-dialog::backdrop {
    background: #0f172a99;
}
.resolve-panel {
    padding: 24px;
}
.resolve-panel h3 {
    margin: 0 0 6px;
    font-size: 19px;
    color: #142345;
}
.resolve-panel p {
    margin: 0 0 16px;
    color: #63718d;
    font-size: 13px;
}
.resolve-panel label {
    display: block;
    font-size: 13px;
    font-weight: 650;
    color: #334155;
    margin: 15px 0 5px;
}
.resolve-panel textarea {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    padding: 10px;
    background: white;
    font-family: inherit;
    font-size: 13px;
    resize: vertical;
}
.resolve-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}
.resolve-actions button {
    padding: 10px 16px;
    border-radius: 9px;
    border: 1px solid #cbd5e1;
    background: white;
    cursor: pointer;
    font-weight: 600;
}
.resolve-actions button.confirm {
    background: #1e3366;
    color: white;
    border-color: #1e3366;
}
.resolve-actions button.confirm:hover {
    background: #142345;
}
</style>

<div class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Registration</p>
			<h2>Student holds</h2>
		</div>
		<span class="badge"><?= e($securityLevel) ?></span>
	</div>

	<p class="muted">
		A hold blocks course registration until it is removed or resolved.
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
			<th>Hold type</th>
			<th>Reason</th>
			<th>Date</th>
			<th>Expires</th>
			<th>Status</th>
			<?php if ($isFullAccess): ?>
				<th>Actions</th>
			<?php endif; ?>
		</tr>

		<?php foreach ($holds as $hold): $isResolved = (int) $hold['hold_Resolved'] === 1; ?>
			<tr>
				<td><?= e($hold['student_ID']) ?></td>
				<td><?= e($hold['student_Name']) ?></td>
				<td><?= e($hold['hold_Type']) ?></td>
				<td class="hold-reason" title="<?= e($hold['hold_Reason'] ?? '') ?>">
					<?= e($hold['hold_Reason'] ?? '—') ?>
				</td>
				<td><?= e($hold['hold_Date']) ?></td>
				<td><?= e($hold['hold_Expires'] ?? '—') ?></td>
				<td>
					<span class="hold-status-badge <?= $isResolved ? 'resolved' : 'active' ?>">
						<?= $isResolved ? 'Resolved' : 'Active' ?>
					</span>
					<?php if ($isResolved): ?>
						<div class="hold-resolved-by">
							Resolved <?= e($hold['hold_Resolved_At']) ?>
							<?php if ($hold['resolver_First_Name']): ?>
								by <?= e($hold['resolver_First_Name'] . ' ' . $hold['resolver_Last_Name']) ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</td>
				<?php if ($isFullAccess): ?>
					<td class="hold-actions">
						<?php if (!$isResolved): ?>
							<dialog class="resolve-dialog" id="resolve-dialog-<?= e($hold['hold_ID']) ?>">
								<div class="resolve-panel">
									<h3>Resolve Hold</h3>
									<p>Mark hold #<?= e($hold['hold_ID']) ?> for <?= e($hold['student_Name']) ?> as resolved.</p>
									<form method="post" action="holds.php?view=<?= e($hold['student_ID']) ?>">
										<?= csrf_field() ?>
										<input type="hidden" name="action" value="resolve_hold">
										<input type="hidden" name="hold_id" value="<?= e($hold['hold_ID']) ?>">
										<label>Resolution notes</label>
										<textarea name="resolution_notes" rows="3" placeholder="e.g. Student paid outstanding balance"></textarea>
										<div class="resolve-actions">
											<button type="button" class="btn" onclick="document.getElementById('resolve-dialog-<?= e($hold['hold_ID']) ?>').close()">Cancel</button>
											<button type="submit" class="btn confirm">Resolve Hold</button>
										</div>
									</form>
								</div>
							</dialog>

							<button type="button" class="btn small"
								onclick="document.getElementById('resolve-dialog-<?= e($hold['hold_ID']) ?>').showModal()">
								Resolve
							</button>

							<form method="post" style="display: inline-block; margin-top: 6px;">
								<?= csrf_field() ?>
								<input type="hidden" name="action" value="remove_hold">
								<input type="hidden" name="student_id" value="<?= e($hold['student_ID']) ?>">
								<input type="hidden" name="hold_id" value="<?= e($hold['hold_ID']) ?>">
								<button
									type="submit"
									class="btn danger small"
									onclick="return confirm('Remove this hold completely?');"
								>
									Remove
								</button>
							</form>
						<?php else: ?>
							<span class="muted small">Resolved</span>
						<?php endif; ?>
					</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>

	</table>

</div>


<p class="muted">Showing the 200 most recent holds.</p>

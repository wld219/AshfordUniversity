<?php

declare(strict_types=1);

// Audit log view: filters, entries, pagination.

?>

<div class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Compliance</p>
			<h2>Audit log</h2>
		</div>
		<span class="badge">Append-only</span>
	</div>

	<form method="get" class="filters" style="margin-top: 16px;">
		<div>
			<label for="action">Action</label>
			<select name="action" id="action">
				<option value="">All actions</option>
				<?php foreach ($actionFilters as $row): ?>
					<option value="<?= e($row['action']) ?>" <?= $action === $row['action'] ? 'selected' : '' ?>>
						<?= e($row['action']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="entity">Record type</label>
			<select name="entity" id="entity">
				<option value="">All record types</option>
				<?php foreach ($entityFilters as $row): ?>
					<option value="<?= e($row['entity_Type']) ?>" <?= $entityType === $row['entity_Type'] ? 'selected' : '' ?>>
						<?= e($row['entity_Type']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">Filter</button>
			<a href="audit.php" class="btn light">Clear</a>
		</div>
	</form>
</div>


<div class="table-wrap">

	<table>
		<tr>
			<th>ID</th>
			<th>When</th>
			<th>Actor</th>
			<th>Action</th>
			<th>Record</th>
			<th>Details</th>
		</tr>

		<?php foreach ($entries as $entry): ?>
			<tr>
				<td><?= e($entry['log_ID']) ?></td>
				<td><?= e($entry['created_At']) ?></td>
				<td>
					<?= e($entry['actor_Name'] ?? 'System') ?>
					<?php if (!empty($entry['actor_User_ID'])): ?>
						<br><span class="muted">ID <?= e($entry['actor_User_ID']) ?></span>
					<?php endif; ?>
				</td>
				<td><?= e($entry['action']) ?></td>
				<td>
					<?= e($entry['entity_Type']) ?>
					<?php if ($entry['entity_ID'] !== null): ?>
						#<?= e($entry['entity_ID']) ?>
					<?php endif; ?>
				</td>
				<td><?= e($entry['details'] ?? '') ?></td>
			</tr>
		<?php endforeach; ?>

	</table>

</div>


<div class="card">
	<p class="muted">
		<?= e((string) $total) ?> matching entries.
		Page <?= e((string) $page) ?> of <?= e((string) $totalPages) ?>.
	</p>

	<?php if ($page > 1): ?>
		<a class="btn light" href="audit.php?page=<?= e((string) ($page - 1)) ?>&action=<?= e($action) ?>&entity=<?= e($entityType) ?>">
			Previous
		</a>
	<?php endif; ?>

	<?php if ($page < $totalPages): ?>
		<a class="btn" href="audit.php?page=<?= e((string) ($page + 1)) ?>&action=<?= e($action) ?>&entity=<?= e($entityType) ?>">
			Next
		</a>
	<?php endif; ?>
</div>

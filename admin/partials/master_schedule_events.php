<?php

declare(strict_types=1);

// Recent schedule audit events.

?>

<section class="card">

	<h2>Recent schedule events</h2>

	<?php if (count($auditEntries) === 0): ?>

		<p class="muted">No schedule events recorded yet.</p>

	<?php else: ?>

		<div class="table-wrap">
			<table>
				<tr>
					<th>When</th>
					<th>Administrator</th>
					<th>Action</th>
					<th>Details</th>
				</tr>
				<?php foreach ($auditEntries as $entry): ?>
					<tr>
						<td><?= e((string) $entry['created_At']) ?></td>
						<td><?= e((string) ($entry['actor_Name'] ?? 'System')) ?></td>
						<td><?= e((string) $entry['action']) ?></td>
						<td><?= e((string) ($entry['details'] ?? '')) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>

	<?php endif; ?>

</section>

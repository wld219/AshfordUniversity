<?php

declare(strict_types=1);

// Live statistics and the validation & conflict report.

?>

<?php if ($semester !== null): ?>

	<details class="card"><summary style="cursor:pointer;font-weight:600">Validation &amp; conflict report (<?= count($validation['issues']) ?> issues)</summary><div style="padding-top:12px">

		<h2>Validation &amp; conflict report</h2>

		<?php if (count($validation['issues']) === 0): ?>

			<div class="alert success">
				No validation issues: every section has a room, an authorized
				faculty member, a valid meeting time, and no conflicts.
			</div>

		<?php else: ?>

			<div class="alert error">
				<?= count($validation['issues']) ?> issue(s) must be resolved
				before this schedule can be published.
			</div>

			<div class="table-wrap">
				<table>
					<tr>
						<th>CRN</th>
						<th>Type</th>
						<th>Issue</th>
					</tr>
					<?php foreach ($validation['issues'] as $issue): ?>
						<tr>
							<td><?= e((string) $issue['CRN']) ?></td>
							<td><?= e((string) $issue['type']) ?></td>
							<td><?= e((string) $issue['message']) ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

		<?php endif; ?>

	</div></details>

<?php endif; ?>

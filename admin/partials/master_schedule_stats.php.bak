<?php

declare(strict_types=1);

// Live statistics and the validation & conflict report.

?>

<?php if ($semester !== null): ?>

	<section class="dashboard-stats" aria-label="Schedule statistics">

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Total sections</span>
			<strong><?= e((string) ($validation['stats']['total_Sections'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">In this semester</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Scheduled</span>
			<strong><?= e((string) ($validation['stats']['scheduled_Sections'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Fully assigned</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Unscheduled</span>
			<strong><?= e((string) ($validation['stats']['unscheduled_Sections'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Need attention</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Missing room</span>
			<strong><?= e((string) ($validation['stats']['missing_Room'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">No room assigned</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Missing faculty</span>
			<strong><?= e((string) ($validation['stats']['missing_Faculty'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">No faculty assigned</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Room conflicts</span>
			<strong><?= e((string) ($validation['stats']['room_Conflicts'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Double-booked rooms</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Faculty conflicts</span>
			<strong><?= e((string) ($validation['stats']['faculty_Conflicts'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Overlapping teaching</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Validation errors</span>
			<strong><?= e((string) ($validation['stats']['validation_Errors'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Other rule failures</span>
		</article>

	</section>


	<section class="card">

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

	</section>

<?php endif; ?>

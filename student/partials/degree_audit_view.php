<?php

declare(strict_types=1);

// Degree audit view: progress per declared major and minor, plus what-if analysis.

?>

<style>
/* ASHFORD_DEGREE_AUDIT_V2 */
.ash-audit .progress-wrap {
    margin: 10px 0 6px;
}
.ash-audit .progress-bar {
    height: 8px;
    background: #eef2f6;
    border-radius: 4px;
    overflow: hidden;
}
.ash-audit .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #1e3366, #d6a800);
    width: 0%;
    transition: width 0.5s ease;
}
.ash-audit .progress-text {
    font-size: 13px;
    color: #63718d;
    margin-top: 4px;
}
.ash-audit .what-if-card {
    background: #f8f9fd;
    border: 1px solid #e0e5ef;
    border-radius: 13px;
    padding: 18px;
    margin-top: 24px;
}
.ash-audit .what-if-card h3 {
    margin: 0 0 16px;
    font-size: 19px;
    color: #142345;
}
.ash-audit .what-if-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #edf0f6;
}
.ash-audit .what-if-row:last-child {
    border-bottom: 0;
}
.ash-audit .what-if-name {
    font-weight: 700;
    color: #142345;
}
.ash-audit .what-if-pct {
    font-weight: 700;
    color: #1e3366;
}
</style>

<p class="muted">
	Requirements come from the published major and minor requirement lists.
	A requirement is met when your best recorded grade is at least the
	required minimum grade.
</p>


<?php foreach ($audits as $audit): $pct = $audit['total'] > 0 ? round(($audit['completed'] / $audit['total']) * 100, 0) : 0; ?>

	<section class="card ash-audit" style="margin-bottom: 18px;">

		<div class="dashboard-section-heading">
			<div>
				<p class="dashboard-eyebrow"><?= e($audit['type']) ?></p>
				<h2><?= e($audit['name']) ?></h2>
			</div>
			<?php if ($audit['primary']): ?>
				<span class="badge">Primary</span>
			<?php endif; ?>
		</div>

		<p>
			<strong>
				<?= e((string) $audit['completed']) ?> of
				<?= e((string) $audit['total']) ?> requirements met
			</strong>
			<span style="float:right"><?= e($pct) ?>% complete</span>
		</p>

		<div class="progress-wrap">
			<div class="progress-bar">
				<div class="progress-fill" style="width: <?= e($pct) ?>%"></div>
			</div>
			<div class="progress-text">
				<?= e($audit['completed']) ?> of <?= e($audit['total']) ?> courses completed
			</div>
		</div>

		<?php if ($audit['total'] === 0): ?>

			<p class="muted">No requirements are published for this program yet.</p>

		<?php else: ?>

			<div class="table-wrap">
				<table>
					<thead>
						<tr>
							<th>Course</th>
							<th>Title</th>
							<th>Minimum grade</th>
							<th>Your grade</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>

						<?php foreach ($audit['rows'] as $row): ?>

							<tr>
								<td><?= e((string) $row['course_ID']) ?></td>
								<td><?= e($row['course_Name']) ?></td>
								<td><?= e($row['min_Grade_Req']) ?></td>
								<td><?= e($row['grade'] ?? '—') ?></td>
								<td>
									<?php if ($row['met']): ?>
										<span class="badge">Complete</span>
									<?php elseif ($row['grade'] !== null): ?>
										<span class="badge">Below required grade</span>
									<?php else: ?>
										<span class="muted">Not taken</span>
									<?php endif; ?>
								</td>
							</tr>

						<?php endforeach; ?>

					</tbody>
				</table>
			</div>

		<?php endif; ?>

	</section>

<?php endforeach; ?>


<?php if (count($audits) === 0): ?>

	<div class="card">
		<p>
			No majors or minors are declared yet. Declare them on the
			<a href="programs.php">Majors &amp; Minors</a> page to see your
			degree audit.
		</p>
	</div>

<?php endif; ?>

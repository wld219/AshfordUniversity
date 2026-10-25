<?php

declare(strict_types=1);

// Degree audit view: progress per declared major and minor.

?>

<p class="muted">
	Requirements come from the published major and minor requirement lists.
	A requirement is met when your best recorded grade is at least the
	required minimum grade.
</p>


<?php foreach ($audits as $audit): ?>

	<section class="card" style="margin-bottom: 18px;">

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
		</p>

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

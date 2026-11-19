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
@media (prefers-reduced-motion: reduce) {
	.ash-audit .progress-fill { transition: none; }
}
</style>

<section class="card" aria-label="Student academic information">
	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Student academic record</p>
			<h2><?= e($studentAuditFullName !== '' ? $studentAuditFullName : 'Student record unavailable') ?></h2>
		</div>
		<span class="badge"><?= e($studentAuditProfile['student_Type'] ?? 'Academic level unavailable') ?></span>
	</div>
	<p><strong>Student ID:</strong> <?= e($studentAuditProfile['user_ID'] ?? $studentID) ?></p>
	<?php if (($studentAuditProfile['student_Type'] ?? null) === 'Undergraduate'): ?>
		<p><strong>Primary major:</strong> <?= e($studentAuditProfile['major_Name'] ?? 'Not recorded') ?></p>
	<?php elseif (($studentAuditProfile['student_Type'] ?? null) === 'Graduate'): ?>
		<p><strong>Graduate program:</strong> <?= e($studentAuditProfile['graduate_Program'] ?? 'Not recorded') ?></p>
	<?php endif; ?>
	<?php if ($studentAuditDeclaredMinors): ?>
		<p><strong>Declared minor<?= count($studentAuditDeclaredMinors) === 1 ? '' : 's' ?>:</strong> <?= e(implode(', ', $studentAuditDeclaredMinors)) ?></p>
	<?php endif; ?>
</section>

<?php if ($studentAuditGraduateMessage !== null): ?>
	<section class="card" aria-label="Graduate degree requirements">
		<h2><?= e($studentAuditProfile['graduate_Program'] ?? 'Graduate program') ?></h2>
		<p class="muted"><?= e($studentAuditGraduateMessage) ?> Requirements Not Published.</p>
	</section>

<?php endif; ?>

<?php if (!$studentAuditHistory): ?>
	<p class="muted">No completed course history is recorded. Requirement rows will show no qualifying grade until completed coursework is available.</p>
<?php endif; ?>

<section class="card" aria-label="<?= e($studentAuditSemesterName) ?> enrollment classification">
	<h2><?= e($studentAuditSemesterName) ?> Enrollment Status</h2>
	<p><?= e($studentAuditSemesterStatus['registered_credits'] ?? 0) ?> registered credits · <?= e($studentAuditSemesterStatus['status'] ?? 'Unavailable') ?></p>
</section>

<section class="card ash-audit" aria-label="<?= e($studentAuditSemesterName) ?> courses currently registered">
	<form method="get" action="degree-audit.php" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;margin-bottom:16px">
		<div>
			<label for="audit-semester"><strong>Academic Semester</strong></label><br>
			<select id="audit-semester" name="semester" style="padding:9px;border:1px solid #cbd5e1;border-radius:8px">
				<?php foreach ($studentAuditSemesters as $term): ?>
					<option value="<?= (int)$term['semester_ID'] ?>" <?= (int)$studentAuditSemesterID === (int)$term['semester_ID'] ? 'selected' : '' ?>><?= e($term['semester_Name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<button type="submit" class="btn">View Semester</button>
	</form>
	<h2><?= e($studentAuditSemesterName) ?> — Courses Currently Registered</h2>
	<?php if (!$studentAuditRegisteredCourses): ?>
		<p class="muted">No courses registered for <?= e($studentAuditSemesterName ?: 'this semester') ?>.</p>
	<?php else: ?>
		<div class="table-wrap">
			<table>
				<thead><tr><th>Course</th><th>Title</th><th>Credits</th><th>CRN</th><th>Days</th><th>Meeting Time</th><th>Status</th></tr></thead>
				<tbody><?php foreach ($studentAuditRegisteredCourses as $course): ?>
					<tr>
						<td><?= e($course['course_ID']) ?></td>
						<td><?= e($course['course_Name']) ?></td>
						<td><?= e($course['course_Credits']) ?></td>
						<td><?= e($course['CRN']) ?></td>
						<td><?= e($course['meeting_days']) ?></td>
						<td><?= e($course['meeting_time']) ?></td>
						<td><?= e($course['registration_status']) ?></td>
					</tr>
				<?php endforeach; ?></tbody>
			</table>
		</div>
	<?php endif; ?>
</section>

<?php if (($studentAuditProfile['student_Type'] ?? null) === 'Undergraduate'): ?>
<p class="muted">
	Published major and minor course requirements are evaluated using your best
	recorded grade. This audit reports published course requirements only; it
	does not represent total degree or graduation progress.
</p>


<?php foreach ($studentAuditPrograms as $audit): ?>
	<?php $pct = $audit['total'] > 0 ? round(($audit['completed'] / $audit['total']) * 100, 0) : 0; ?>

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

		<?php if ($audit['total'] === 0): ?>
			<p class="muted">
				Requirements Not Published.
			</p>
		<?php else: ?>
			<p>
				<strong>Published Course Requirements Completed</strong>
				<span style="float:right"><?= e($pct) ?>%</span>
			</p>
			<p><?= e((string) $audit['completed']) ?> of <?= e((string) $audit['total']) ?> published course requirements met</p>

			<div class="progress-wrap">
				<div class="progress-bar" role="progressbar"
					aria-label="Published Course Requirements Completed"
					aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $pct ?>">
					<div class="progress-fill" style="width: <?= e($pct) ?>%"></div>
				</div>
				<div class="progress-text">
					<?= e($audit['completed']) ?> of <?= e($audit['total']) ?> published courses completed
				</div>
			</div>

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
									<?php elseif ($row['in_progress']): ?>
										<span class="badge">In progress</span>
									<?php elseif ($row['grade'] !== null): ?>
										<span class="badge">Below required grade</span>
									<?php else: ?>
										<span class="muted">Missing requirement</span>
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


<?php if (count($studentAuditPrograms) === 0): ?>

	<div class="card">
		<p>
			No primary or additional major or minor is recorded for this student.
		</p>
	</div>

<?php endif; ?>
<?php endif; ?>

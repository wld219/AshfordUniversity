<?php

declare(strict_types=1);

// Student dashboard view: information, notices, schedule, actions.

?>

<div class="dashboard-page">

	<section class="dashboard-hero">
		<div>
			<p class="dashboard-eyebrow">Student</p>
			<h2>My dashboard</h2>
			<p>Current academic activity and quick links</p>
		</div>
	</section>


	<section class="dashboard-stats" aria-label="Academic summary">

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Current courses</span>
			<strong><?= count($schedule) ?></strong>
			<span class="dashboard-stat-note">Fall 2026</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Current credits</span>
			<strong><?= e((int) ($credits['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">
				Limit: <?= e(student_credit_limit($pdo, $studentID)) ?>
			</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Cumulative GPA</span>
			<strong><?= $gpa !== null ? e($gpa) : '—' ?></strong>
			<span class="dashboard-stat-note">
				<?= $gpa !== null ? 'Completed coursework' : 'Not available yet' ?>
			</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Holds</span>
			<strong><?= count($holds) ?></strong>
			<span class="dashboard-stat-note">Registration holds</span>
		</article>

	</section>


	<section class="dashboard-section">
		<div class="dashboard-section-heading">
			<div>
				<p class="dashboard-eyebrow">Profile</p>
				<h2>Student information</h2>
			</div>
			<a class="btn light" href="information.php">Update information</a>
		</div>

		<div class="card grid2">

			<p>
				<strong>Name</strong>
				<br>
				<?= e(
					($student['first_Name'] ?? '') . ' ' .
					($student['last_Name'] ?? '')
				) ?>
			</p>

			<p>
				<strong>Level</strong>
				<br>
				<?= e($student['student_Type'] ?? '') ?>
			</p>

			<p>
				<strong>Major</strong>
				<br>
				<?= e($student['major_Name'] ?? 'Undeclared') ?>
			</p>

			<p>
				<strong>Academic advisor</strong>
				<br>
				<?= e($student['advisor_Name'] ?? 'Not assigned') ?>
			</p>

		</div>
	</section>


	<?php if (count($holds) > 0): ?>

		<div class="alert error">
			You have <?= count($holds) ?> registration hold(s). Resolve them
			before registering for courses. See the Holds page for details.
		</div>

	<?php endif; ?>


	<section class="dashboard-section">
		<div class="dashboard-section-heading">
			<div>
				<p class="dashboard-eyebrow">Registration</p>
				<h2>Current schedule</h2>
			</div>
			<a class="btn" href="registration.php">Register for courses</a>
		</div>

		<?php if (count($schedule) > 0): ?>

			<div class="table-wrap">
				<table>
					<thead>
						<tr>
							<th>Course</th>
							<th>Title</th>
							<th>Credits</th>
							<th>CRN</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($schedule as $course): ?>
							<tr>
								<td><?= e($course['course_ID']) ?></td>
								<td><?= e($course['course_Name']) ?></td>
								<td><?= e($course['course_Credits']) ?></td>
								<td><?= e($course['CRN']) ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

		<?php else: ?>

			<div class="card">
				<p>No courses registered for Fall 2026.</p>
			</div>

		<?php endif; ?>
	</section>

</div>

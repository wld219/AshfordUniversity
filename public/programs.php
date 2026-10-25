<?php

declare(strict_types=1);

require_once __DIR__ . '/layout_public.php';

// Required courses per program, keyed by program ID.
[$majorCourses, $majorCoursesError] = public_load(static fn(): array => all_rows(
	$pdo,
	"SELECT mr.major_ID, mr.min_Grade_Req, c.course_ID, c.course_Name
	FROM Major_Requirement mr
	JOIN Course c ON c.course_ID = mr.course_ID
	ORDER BY mr.major_ID, c.course_ID"
));

[$minorCourses, $minorCoursesError] = public_load(static fn(): array => all_rows(
	$pdo,
	"SELECT mr.minor_ID, mr.min_Grade_Req, c.course_ID, c.course_Name
	FROM Minor_Requirement mr
	JOIN Course c ON c.course_ID = mr.course_ID
	ORDER BY mr.minor_ID, c.course_ID"
));

$majorMap = [];

foreach ($majorCourses as $row) {
	$majorMap[(int) $row['major_ID']][] = $row;
}

$minorMap = [];

foreach ($minorCourses as $row) {
	$minorMap[(int) $row['minor_ID']][] = $row;
}

[$majors, $majorsError] = public_load(static fn(): array => all_rows(
	$pdo,
	'SELECT m.major_ID, m.major_Name, d.dept_Name
	FROM Major m
	JOIN Department d ON d.dept_ID = m.dept_ID
	ORDER BY m.major_Name'
));

[$minors, $minorsError] = public_load(static fn(): array => all_rows(
	$pdo,
	'SELECT mn.minor_ID, mn.minor_Name, d.dept_Name
	FROM Minor mn
	JOIN Department d ON d.dept_ID = mn.dept_ID
	ORDER BY mn.minor_Name'
));

public_page_start('Majors & Minors');

?>

<h1>Majors &amp; Minors</h1>

<p class="muted">
	Degree programs offered by Ashford University and the departments that
	administer them. Expand a program to see its required courses.
</p>

<?php if ($majorsError !== ''): ?>

	<div class="alert error"><?= e($majorsError) ?></div>

<?php elseif (count($majors) === 0): ?>

	<div class="card">
		<p>No majors are published yet.</p>
	</div>

<?php else: ?>

	<h2 class="section-title">Majors</h2>

	<div class="table-wrap">
		<table>
			<caption class="visually-hidden">
				Ashford University majors with administering department and
				required courses.
			</caption>
			<thead>
				<tr>
					<th scope="col">Major</th>
					<th scope="col">Department</th>
					<th scope="col">Required courses</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($majors as $major): ?>

					<?php
					$requirements = $majorMap[(int) $major['major_ID']] ?? [];
					$majorId = (int) $major['major_ID'];
					?>

					<tr>
						<td><?= e($major['major_Name']) ?></td>
						<td><?= e($major['dept_Name']) ?></td>
						<td>
							<?php if ($majorCoursesError !== ''): ?>
								<span class="muted">Unavailable</span>
							<?php elseif ($requirements === []): ?>
								<span class="muted">No required courses published</span>
							<?php else: ?>
								<details>
									<summary>
										Show <?= count($requirements) ?> required
										course(s)
									</summary>
									<ul class="public-requirement-list">
										<?php foreach ($requirements as $requirement): ?>
											<li>
												<?= e($requirement['course_ID']) ?>
												<?= e($requirement['course_Name']) ?>
												<span class="muted">
													(min grade
													<?= e($requirement['min_Grade_Req']) ?>)
												</span>
											</li>
										<?php endforeach; ?>
									</ul>
								</details>
							<?php endif; ?>
						</td>
					</tr>

				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

<?php endif; ?>

<?php if ($minorsError !== ''): ?>

	<div class="alert error"><?= e($minorsError) ?></div>

<?php elseif (count($minors) === 0): ?>

	<div class="card">
		<p>No minors are published yet.</p>
	</div>

<?php else: ?>

	<h2 class="section-title">Minors</h2>

	<div class="table-wrap">
		<table>
			<caption class="visually-hidden">
				Ashford University minors with administering department and
				required courses.
			</caption>
			<thead>
				<tr>
					<th scope="col">Minor</th>
					<th scope="col">Department</th>
					<th scope="col">Required courses</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($minors as $minor): ?>

					<?php $requirements = $minorMap[(int) $minor['minor_ID']] ?? []; ?>

					<tr>
						<td><?= e($minor['minor_Name']) ?></td>
						<td><?= e($minor['dept_Name']) ?></td>
						<td>
							<?php if ($minorCoursesError !== ''): ?>
								<span class="muted">Unavailable</span>
							<?php elseif ($requirements === []): ?>
								<span class="muted">No required courses published</span>
							<?php else: ?>
								<details>
									<summary>
										Show <?= count($requirements) ?> required
										course(s)
									</summary>
									<ul class="public-requirement-list">
										<?php foreach ($requirements as $requirement): ?>
											<li>
												<?= e($requirement['course_ID']) ?>
												<?= e($requirement['course_Name']) ?>
												<span class="muted">
													(min grade
													<?= e($requirement['min_Grade_Req']) ?>)
												</span>
											</li>
										<?php endforeach; ?>
									</ul>
								</details>
							<?php endif; ?>
						</td>
					</tr>

				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

<?php endif; ?>

<?php

public_page_end();

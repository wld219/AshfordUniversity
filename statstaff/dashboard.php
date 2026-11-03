<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['StatStaff']);

/*
 * Read-only overview. Every figure on this page is an aggregate: no student
 * names, no student IDs, no individual grades are ever displayed.
 */

$students = one(
	$pdo,
	'SELECT COUNT(*) AS total FROM Student'
);

$undergraduates = one(
	$pdo,
	"SELECT COUNT(*) AS total FROM Student WHERE student_Type = 'Undergraduate'"
);

$graduates = one(
	$pdo,
	"SELECT COUNT(*) AS total FROM Student WHERE student_Type = 'Graduate'"
);

$faculty = one(
	$pdo,
	'SELECT COUNT(*) AS total FROM Faculty'
);

$departments = one(
	$pdo,
	'SELECT COUNT(*) AS total FROM Department'
);


// Enrollment totals for the current term
$currentSemester = semester_id($pdo, 'Fall 2026');

$currentEnrollments = one(
	$pdo,
	'SELECT COUNT(*) AS total FROM Enrollment WHERE semester_ID = ?',
	[$currentSemester]
);

$currentStudents = one(
	$pdo,
	'SELECT COUNT(DISTINCT student_ID) AS total FROM Enrollment WHERE semester_ID = ?',
	[$currentSemester]
);


page_start(
	'Overview',
	'StatStaff',
	'dashboard.php'
);

flash();

?>


<div class="dashboard-page">

	<section class="dashboard-hero">
		<div>
			<p class="dashboard-eyebrow">Statistics department</p>
			<h2>Ashford University at a glance</h2>
			<p>Read-only, anonymous reporting for the current academic term.</p>
		</div>
		<span class="dashboard-badge">Read-only</span>
	</section>

	<section class="dashboard-stats" aria-label="University summary">
		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Students</span>
			<strong><?= e((int) ($students['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">All levels</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Undergraduates</span>
			<strong><?= e((int) ($undergraduates['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Undergraduate programs</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Graduates</span>
			<strong><?= e((int) ($graduates['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Graduate programs</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Faculty</span>
			<strong><?= e((int) ($faculty['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Teaching staff</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Departments</span>
			<strong><?= e((int) ($departments['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Academic units</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Fall 2026 enrollments</span>
			<strong><?= e((int) ($currentEnrollments['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">
				<?= e((int) ($currentStudents['total'] ?? 0)) ?> distinct students
			</span>
		</article>
	</section>

	<section class="dashboard-section">
		<div class="dashboard-section-heading">
			<div>
				<p class="dashboard-eyebrow">Anonymous reporting</p>
				<h2>Reports</h2>
			</div>
		</div>

		<div class="card">
			<p>
				Detailed anonymous reports are available on the
				<a href="reports.php">Anonymous Reports</a> page. Reports show
				enrollment totals and grade summaries only — never student
				names or student IDs.
			</p>
		</div>
	</section>
</div>


<?php

page_end();

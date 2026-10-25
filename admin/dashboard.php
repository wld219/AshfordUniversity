<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin']);

$students = one(
	$pdo,
	'SELECT COUNT(*) AS total FROM Student'
);

$faculty = one(
	$pdo,
	'SELECT COUNT(*) AS total FROM Faculty'
);

$sections = one(
	$pdo,
	'SELECT COUNT(*) AS total FROM Course_Section'
);

$advisees = one(
	$pdo,
	'SELECT COUNT(*) AS total FROM Advisor'
);

page_start(
	'Welcome, ' . ($_SESSION['name'] ?? 'Admin'),
	'Admin',
	'dashboard.php'
);

?>

<div class="dashboard-page admin-dashboard">

	<section class="dashboard-hero">
		<div>
			<p class="dashboard-eyebrow">Administration</p>
			<h2>University overview</h2>
			<p>Current academic and staffing activity</p>
		</div>
		<span class="dashboard-badge">Live data</span>
	</section>

	<section class="dashboard-stats" aria-label="Administration summary">
		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Students</span>
			<strong><?= e((int) ($students['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Enrolled students</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Faculty</span>
			<strong><?= e((int) ($faculty['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Teaching staff</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Course sections</span>
			<strong><?= e((int) ($sections['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">All semesters</span>
		</article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Advisees</span>
			<strong><?= e((int) ($advisees['total'] ?? 0)) ?></strong>
			<span class="dashboard-stat-note">Advisor assignments</span>
		</article>
	</section>
</div>

<?php

page_end();

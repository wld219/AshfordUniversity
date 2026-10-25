<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

/*
 * The public landing page for Ashford University.
 *
 * It uses its own layout (a vertical rail navigation) instead of the shared
 * public top bar, so it is a standalone document rather than a
 * layout_public.php page.
 *
 * It makes no claim it cannot back: every figure comes from the live
 * database, and the stat band is hidden when the install has no catalog yet.
 */

$departmentCount = (int) (one($pdo, 'SELECT COUNT(*) AS total FROM Department')['total'] ?? 0);
$courseCount = (int) (one($pdo, 'SELECT COUNT(*) AS total FROM Course')['total'] ?? 0);
$sectionCount = (int) (one(
	$pdo,
	'SELECT COUNT(*) AS total FROM Course_Section WHERE is_Cancelled = 0'
)['total'] ?? 0);

// Only show figures that exist. An empty install shows no band at all
// rather than a row of zeroes.
$stats = [];

if ($departmentCount > 0) {
	$stats[] = ['Academic departments', number_format($departmentCount)];
}

if ($courseCount > 0) {
	$stats[] = ['Courses in the catalog', number_format($courseCount)];
}

if ($sectionCount > 0) {
	$stats[] = ['Sections published this term', number_format($sectionCount)];
}

if (count($stats) >= 2) {
	$stats[] = ['Role portals', (string) ASHFORD_PORTAL_COUNT];
}

$sections = [
	'overview' => 'Overview',
	'academics' => 'Academics',
	'admissions' => 'Admissions',
	'schedule' => 'Schedule',
];

$current = 'overview';

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta
		name="description"
		content="Ashford University: read the catalog, prerequisites, program requirements, and master schedule before you register. Meeting times, rooms, instructors, and open seats are published in the open."
	>
	<title>Ashford University | Plan your degree in the open</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link
		rel="stylesheet"
		href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap"
	>
	<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
	<link rel="stylesheet" href="<?= e(url('assets/css/public.css')) ?>">
</head>
<body>

<a class="skip-link" href="#main-content">Skip to content</a>

<div class="landing-shell">

	<aside class="landing-rail">

		<img
			class="landing-rail-logo"
			src="<?= e(url('assets/img/ashford-university-logo.svg')) ?>"
			alt="Ashford University"
			width="220"
			height="80"
		>

		<nav class="landing-nav" aria-label="Page sections">
			<?php $index = 0; ?>
			<?php foreach ($sections as $id => $label): ?>
				<?php $index++; ?>
				<a
					href="#<?= e($id) ?>"
					class="<?= $current === $id ? 'active' : '' ?>"
					data-section="<?= e($id) ?>"
					<?= $current === $id ? 'aria-current="page"' : '' ?>
				>
					<span class="idx" aria-hidden="true"><?= str_pad((string) $index, 2, '0', STR_PAD_LEFT) ?></span>
					<span><?= e($label) ?></span>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="landing-rail-foot">
			<p class="landing-rail-note">
				Students, faculty, administrators, and statistics staff each have
				their own portal.
			</p>
			<a class="btn gold" href="<?= e(url('index.php')) ?>">Open your portal</a>
		</div>

	</aside>

	<main class="landing-main" id="main-content">

		<section class="landing-hero" id="overview">
			<div class="landing-hero-copy">
				<p class="eyebrow">Ashford University</p>
				<h1><span class="grad">Plan the whole term</span> before registration opens.</h1>
				<p class="lead">
					The catalog, prerequisites, program requirements, and master
					schedule are published in the open. Meeting times, rooms,
					instructors, and open seats are visible before you sign in, so
					you can build a schedule that holds together.
				</p>
				<div class="landing-hero-actions">
					<a class="btn gold" href="<?= e(url('public/programs.php')) ?>">Explore degree programs</a>
					<a class="btn light" href="<?= e(url('public/schedule.php')) ?>">See the published schedule</a>
				</div>
			</div>

			<div class="landing-brand-panel">
				<img
					class="landing-lockup"
					src="<?= e(url('assets/img/ashford-university-logo.svg')) ?>"
					alt="Ashford University"
					width="380"
					height="139"
				>
				<ul class="landing-portals">
					<li>Student portal <span class="who">Enroll</span></li>
					<li>Faculty portal <span class="who">Teach</span></li>
					<li>Administrator portal <span class="who">Manage</span></li>
					<li>Statistics portal <span class="who">Report</span></li>
				</ul>
			</div>
		</section>

		<section class="landing-section" id="academics">
			<div class="landing-section-head">
				<p class="eyebrow">Academics</p>
				<h2>Departments, prerequisites, and requirements in one place.</h2>
				<p>
					Before you commit to a term, you can see what each department
					teaches, what every course requires, and what a major or minor
					actually asks of you.
				</p>
			</div>

			<div class="landing-cards">
				<article class="landing-card">
					<p class="tag">Departments</p>
					<h3>Know who teaches what</h3>
					<p>
						Every academic unit with its courses and contact
						information, published for prospective and current
						students alike.
					</p>
					<a href="<?= e(url('public/departments.php')) ?>">Browse departments</a>
				</article>

				<article class="landing-card">
					<p class="tag">Programs</p>
					<h3>Map credits to a degree</h3>
					<p>
						Majors and minors list the courses they require, so you can
						plan four years instead of guessing one term at a time.
					</p>
					<a href="<?= e(url('public/programs.php')) ?>">Compare programs</a>
				</article>

				<article class="landing-card">
					<p class="tag">Prerequisites</p>
					<h3>See the gate before you reach it</h3>
					<p>
						Each prerequisite carries its minimum grade. You know what
						you must earn before a course unlocks.
					</p>
					<a href="<?= e(url('public/courses.php')) ?>">Check prerequisites</a>
				</article>
			</div>
		</section>

		<section class="landing-section" id="admissions">
			<div class="landing-section-head">
				<p class="eyebrow">Admissions</p>
				<h2>Four steps from interest to enrolled.</h2>
				<p>
					The same tools you use to plan are the ones you use to register.
					Nothing is hidden behind a login until it concerns your own
					record.
				</p>
			</div>

			<ol class="landing-steps">
				<li>
					<span class="n">01</span>
					<h3>Create your account</h3>
					<p>Register as a student from the public site.</p>
				</li>
				<li>
					<span class="n">02</span>
					<h3>Read the catalog</h3>
					<p>Courses, credits, levels, and prerequisites.</p>
				</li>
				<li>
					<span class="n">03</span>
					<h3>Build the schedule</h3>
					<p>Meeting times, rooms, and open seats, checked for conflicts.</p>
				</li>
				<li>
					<span class="n">04</span>
					<h3>Register in your window</h3>
					<p>Enrollment opens and closes on published dates.</p>
				</li>
			</ol>

			<p style="margin: 32px 0 0;">
				<a class="btn gold" href="<?= e(url('register.php')) ?>">Create your student account</a>
			</p>
		</section>

		<section class="landing-section" id="schedule">
			<div class="landing-section-head">
				<p class="eyebrow">Schedule</p>
				<h2>The master schedule is the single source of truth.</h2>
				<p>
					Every published section carries its meeting time, room,
					instructor, and seat count. A schedule is only published once
					every section has a room, an authorized instructor, a valid
					meeting time, and no room or teaching conflicts.
				</p>
			</div>

			<?php if (count($stats) > 0): ?>
				<section class="stat-band" aria-label="Ashford University at a glance">
					<?php foreach ($stats as $stat): ?>
						<article class="stat">
							<div class="num"><?= e($stat[1]) ?></div>
							<p class="cap"><?= e($stat[0]) ?></p>
						</article>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>
		</section>

	</main>

</div>

<footer class="landing-footer">
	<p>
		&copy; <?= date('Y') ?> Ashford University &middot;
		<a href="<?= e(url('public/departments.php')) ?>">Departments</a> &middot;
		<a href="<?= e(url('public/courses.php')) ?>">Courses</a> &middot;
		<a href="<?= e(url('public/programs.php')) ?>">Programs</a> &middot;
		<a href="<?= e(url('public/schedule.php')) ?>">Master schedule</a>
	</p>
	<p><a href="<?= e(url('index.php')) ?>">Sign in to your portal</a></p>
</footer>

<script src="<?= e(url('assets/js/landing.js')) ?>"></script>

</body>
</html>

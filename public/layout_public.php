<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

/**
 * Public layout for unauthenticated visitors.
 */

$publicNav = [
	'index.php' => 'Home',
	'departments.php' => 'Departments',
	'courses.php' => 'Courses & Prerequisites',
	'programs.php' => 'Majors & Minors',
	'schedule.php' => 'Master Schedule',
];

$publicCurrent = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));

/**
 * Run one data query for a public page.
 *
 * Returns [rows, errorMessage]. A failing query yields an empty list plus a
 * plain message, so the page renders a readable failure state instead of a
 * raw exception. The underlying error goes to the server log only.
 *
 * @param callable(): array<int, array<string, mixed>> $query
 * @return array{0: array, 1: string}
 */
function public_load(callable $query): array
{
	try {
		return [$query(), ''];
	} catch (Throwable $e) {
		error_log('Public data load failed: ' . $e->getMessage());

		return [[], 'This information is temporarily unavailable. Please try again in a few minutes.'];
	}
}

function public_page_start(string $title): void
{
	global $publicNav, $publicCurrent;

	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta
			name="description"
			content="Ashford University public catalog: departments, courses, prerequisites, majors and minors, and the master course schedule with meeting times, rooms, instructors, and open seats."
		>
		<title><?= e($title) ?> | Ashford University</title>
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

	<script src="<?= e(url('assets/js/public.js')) ?>" defer></script>

	<header class="public-topbar">
		<div class="public-brand">
			<span class="seal" aria-hidden="true">A</span>
			<span><b>Ashford University</b></span>
		</div>

		<nav aria-label="Public navigation">
			<?php foreach ($publicNav as $file => $label): ?>
				<a
					href="<?= e($file) ?>"
					class="<?= $publicCurrent === $file ? 'active' : '' ?>"
					<?= $publicCurrent === $file ? 'aria-current="page"' : '' ?>
				>
					<?= e($label) ?>
				</a>
			<?php endforeach; ?>
			<a class="public-signin" href="<?= e(url('index.php')) ?>">Sign in</a>
		</nav>
	</header>

	<main class="public-main" id="main-content">
	<?php
}

function public_page_end(): void
{
	?>
	</main>

	<footer class="public-footer">
		<p>
			&copy; <?= date('Y') ?> Ashford University &middot;
			Public information page &middot;
			<a href="<?= e(url('index.php')) ?>">Open your portal</a>
		</p>
	</footer>

	</body>
	</html>
	<?php
}

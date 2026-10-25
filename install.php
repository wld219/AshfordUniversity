<?php

declare(strict_types=1);

/**
 * First-run installer.
 *
 * Creates the initial administrator account after a fresh database import.
 * The installer only works while the database contains no users, so it cannot
 * be used to inject extra admins once the portal is in use. Additional
 * accounts are created from the admin portal afterward.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// ---------------------------------------------------------------------------
// Environment checks
// ---------------------------------------------------------------------------
$schemaReady = false;

try {
	$tables = all_rows(
		$pdo,
		"SELECT TABLE_NAME AS name
		FROM information_schema.TABLES
		WHERE TABLE_SCHEMA = DATABASE()"
	);

	$names = array_column($tables, 'name');

	$required = ['User', 'Login', 'Admin', 'Student', 'Faculty'];

	$schemaReady = !array_diff($required, $names)
		&& in_array('v_section_seats', $names, true);
} catch (PDOException $e) {
	$schemaReady = false;
}

$userCount = 0;

if ($schemaReady) {
	$row = one($pdo, 'SELECT COUNT(*) AS total FROM User');
	$userCount = (int) ($row['total'] ?? 0);
}

$isInstalled = $userCount > 0;
$error = '';

// ---------------------------------------------------------------------------
// Handle installer submission
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isInstalled && $schemaReady) {

	csrf_verify();

	require_once __DIR__ . '/install_actions.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Setup - Ashford University Portal</title>
	<link rel="stylesheet" href="<?= e(url('assets/css/login.css')) ?>">
</head>
<body class="login-page">

<main class="login-card" style="max-width: 560px;">
	<h1>Portal Setup</h1>

	<?php if (!$schemaReady): ?>
		<div class="login-error" role="alert">
			The database schema was not found. Import
			<code>database/schema.sql</code> followed by every file in
			<code>database/migrations/</code> into your MySQL server first
			(see the README setup steps), then reload this page.
		</div>
	<?php elseif ($isInstalled): ?>
		<div class="login-error" role="alert">
			The portal is already installed. You can
			<a href="<?= e(url('index.php')) ?>">sign in here</a>.
		</div>
	<?php else: ?>
		<p>Create the first administrator account for the portal.</p>

		<?php if ($error !== ''): ?>
			<div class="login-error" role="alert"><?= e($error) ?></div>
		<?php endif; ?>

		<?php require_once __DIR__ . '/install_form.php'; ?>
	<?php endif; ?>

	<p class="login-footer">&copy; <?= date('Y') ?> Ashford University</p>
</main>

</body>
</html>

<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

if (!empty($_SESSION['user_id'])) {
	require_once __DIR__ . '/includes/auth.php';
	header('Location: ' . role_home($_SESSION['user_type']));
	exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

$flashMessage = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta
		name="description"
		content="Sign in to the Ashford University portal. Students, faculty, administrators, and statistics staff all use this page."
	>
	<title>Sign in | Ashford University</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link
		rel="stylesheet"
		href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap"
	>
	<link rel="stylesheet" href="<?= e(url('assets/css/login.css')) ?>">
</head>

<body class="login-page">

	<main class="login-card">
		<img
			class="login-logo"
			src="<?= e(url('assets/img/ashford-university-logo.svg')) ?>"
			alt="Ashford University"
			width="210"
			height="77"
		>
		<h1>Sign in to Ashford</h1>
		<p>Use your university email. Students, faculty, administrators, and statistics staff all sign in here.</p>

		<?php if ($flashMessage): ?>
			<div class="login-error" role="status">
				<?= e($flashMessage['message']) ?>
			</div>
		<?php endif; ?>

		<?php if ($error): ?>
			<div class="login-error" role="alert">
				<?= e($error) ?>
			</div>
		<?php endif; ?>

		<form class="login-form" action="<?= e(url('login.php')) ?>" method="post">
			<?= csrf_field() ?>
			<label for="email">
				University email
				<input
					type="email"
					id="email"
					name="email"
					autocomplete="email"
					placeholder="first.last@ashford.edu"
					required
				>
			</label>

			<label for="password">
				Password
				<input
					type="password"
					id="password"
					name="password"
					autocomplete="current-password"
					placeholder="Enter your password"
					required
				>
			</label>

		<button class="login-button" type="submit">Continue</button>
	</form>

	<p class="login-help">
		<a href="<?= e(url('forgot_password.php')) ?>">Forgot password?</a>
	</p>

	<p class="login-help">
		<a href="<?= e(url('public/index.php')) ?>">Explore the public catalog</a>
	</p>

	<p class="login-help">
		New here? <a href="<?= e(url('register.php')) ?>">Register for an account</a>
	</p>

		<p class="login-footer">
			&copy; <?= date('Y') ?> Ashford University
		</p>
	</main>

</body>
</html>

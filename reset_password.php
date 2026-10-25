<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/password_reset.php';

/*
 * Password reset step 2: choose a new password using the emailed link.
 *
 * The link is single-use and expires after AUTH_RESET_TTL seconds.
 */

$error = '';
$success = '';

$token = get_str('token', 128) ?? '';
$tokenState = reset_token_status($pdo, $token);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	$password = (string) ($_POST['password'] ?? '');
	$confirm = (string) ($_POST['confirm_password'] ?? '');

	[$ok, $message] = complete_password_reset(
		$pdo,
		$token,
		$password,
		$confirm
	);

	if ($ok) {

		$success = $message;
		$tokenState = 'invalid';

	} else {

		$error = $message;
	}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="UTF-8">

	<meta
		name="viewport"
		content="width=device-width, initial-scale=1.0"
	>

	<title>Reset Password - Ashford University</title>

	<link
		rel="stylesheet"
		href="<?= e(url('assets/css/login.css')) ?>"
	>

</head>


<body>

<header class="login-header">

	<h1>Ashford University</h1>

	<p>Student &amp; Academic Information System</p>

</header>



<div class="login-container">

	<h2>Reset Password</h2>

	<p class="login-description">
		Choose a new password for your account.
	</p>


	<?php if ($error !== ''): ?>

		<div class="error-message">
			<?= e($error) ?>
		</div>

	<?php elseif ($success !== ''): ?>

		<div class="success-message">
			<?= e($success) ?>
		</div>

	<?php endif; ?>



	<?php if ($tokenState === 'valid'): ?>

		<form method="post" action="<?= e(url('reset_password.php')) ?>">

			<?= csrf_field() ?>

			<input
				type="hidden"
				name="token"
				value="<?= e($token) ?>"
			>


			<div class="form-group">

				<label for="password">
					New Password
				</label>

				<input
					type="password"
					id="password"
					name="password"
					minlength="10"
					autocomplete="new-password"
					placeholder="10+ characters, upper, lower, and a number"
					required
				>

			</div>



			<div class="form-group">

				<label for="confirm_password">
					Confirm New Password
				</label>

				<input
					type="password"
					id="confirm_password"
					name="confirm_password"
					minlength="10"
					autocomplete="new-password"
					required
				>

			</div>



			<button
				type="submit"
				class="login-button"
			>
				Update Password
			</button>


		</form>

	<?php else: ?>

		<div class="error-message">
			This password reset link is invalid, expired, or has already been
			used. Request a new link from the
			<a href="<?= e(url('forgot_password.php')) ?>">Forgot Password</a>
			page.
		</div>

	<?php endif; ?>



	<p class="login-note">

		<a href="<?= e(url('index.php')) ?>">
			Back to Login
		</a>

	</p>

</div>



<footer class="login-footer">

	<p>
		&copy; <?= date('Y') ?> Ashford University
	</p>

</footer>



</body>
</html>

<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/password_reset.php';

/*
 * Password reset step 1: ask for an email address.
 *
 * The page always shows the same confirmation message, whether or not the
 * address belongs to an account, so it cannot be used to discover users.
 */

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	$email = post_str('email', 255);

	if (!valid_email($email)) {

		$error = 'Please enter a valid university email address.';

	} else {

		request_password_reset($pdo, $email);

		$message =
			'If an account exists for that address, a password reset link '
			. 'has been sent. The link expires in '
			. (int) round(env_int('AUTH_RESET_TTL', 3600) / 60)
			. ' minutes.';
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

	<title>Forgot Password - Ashford University</title>

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

	<h2>Forgot Password</h2>

	<p class="login-description">
		Enter the university email address on your account and we will send
		you a link to choose a new password.
	</p>


	<?php if ($error !== ''): ?>

		<div class="error-message">
			<?= e($error) ?>
		</div>

	<?php elseif ($message !== ''): ?>

		<div class="success-message">
			<?= e($message) ?>
		</div>

	<?php endif; ?>



	<form method="post" action="<?= e(url('forgot_password.php')) ?>">

		<?= csrf_field() ?>


		<div class="form-group">

			<label for="email">
				University Email
			</label>

			<input
				type="email"
				id="email"
				name="email"
				maxlength="255"
				placeholder="first.last@ashford.edu"
				required
			>

		</div>



		<button
			type="submit"
			class="login-button"
		>
			Send Reset Link
		</button>


	</form>



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

<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$error = '';
$success = '';

// Get majors for the dropdown
$majors = all_rows(
	$pdo,
	"SELECT major_ID, major_Name, dept_ID
	FROM Major
	ORDER BY major_Name"
);

// Process a registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	require_once __DIR__ . '/register_actions.php';
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

	<title>Create Account - Ashford University</title>

	<link
		rel="stylesheet"
		href="<?= e(url('assets/css/style.css')) ?>"
	>

</head>


<body>


<header class="login-header">

	<h1>Ashford University</h1>

	<p>Student &amp; Academic Information System</p>

</header>



<div class="login-container">

	<h2>Create Student Account</h2>

	<p class="login-description">
		Enter your information below to create an account.
	</p>


	<?php if ($error): ?>

		<div class="error-message">
			<?= e($error) ?>
		</div>

	<?php endif; ?>


	<?php if ($success): ?>

		<div class="success-message">
			<?= e($success) ?>
		</div>

	<?php endif; ?>



	<form method="post" action="<?= e(url('register.php')) ?>">

		<?= csrf_field() ?>

		<?php require_once __DIR__ . '/register_form.php'; ?>

		<button
			type="submit"
			class="login-button"
		>
			Create Account
		</button>


	</form>



	<p class="login-note">

		Already have an account?

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

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

<style>
.modern-role-field {
    margin-bottom: 22px;
}
.modern-role-field > label {
    display: block;
    margin-bottom: 9px;
    font-size: 13px;
    font-weight: 650;
    letter-spacing: .025em;
}
.modern-role-select {
    position: relative;
}
.modern-role-select::after {
    content: "";
    position: absolute;
    right: 19px;
    top: 50%;
    width: 8px;
    height: 8px;
    border-right: 2px solid #64748b;
    border-bottom: 2px solid #64748b;
    transform: translateY(-70%) rotate(45deg);
    pointer-events: none;
}
.modern-role-select select {
    appearance: none;
    -webkit-appearance: none;
    display: block;
    width: 100%;
    min-height: 52px;
    padding: 0 46px 0 16px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    color: #172033;
    font: inherit;
    font-size: 15px;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.modern-role-select select:hover {
    background: #fff;
    border-color: #94a3b8;
}
.modern-role-select select:focus {
    outline: none;
    background: #fff;
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37,99,235,.12);
}
</style>

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
            <div class="modern-role-field">
                <label for="user_type">Sign in as</label>
                <div class="modern-role-select">
                    <select id="user_type" name="user_type" required>
                        <option value="" disabled selected>Choose your portal</option>
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty</option>
                        <option value="Admin">Administrator</option>
                        <option value="StatStaff">Statistics Department Staff</option>
                    </select>
                </div>
            </div>

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

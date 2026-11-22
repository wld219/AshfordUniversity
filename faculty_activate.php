<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/faculty_account_activation.php';

$activationResult = null;
$facultyIDValue = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_verify();
	$facultyIDValue = trim((string) ($_POST['faculty_id'] ?? ''));
	$emailValue = trim((string) ($_POST['email'] ?? ''));
	$facultyID = filter_var($facultyIDValue, FILTER_VALIDATE_INT);
	[$ok, $message] = complete_faculty_account_activation(
		$pdo,
		$facultyID === false ? 0 : (int) $facultyID,
		$emailValue,
		(string) ($_POST['verification_code'] ?? ''),
		(string) ($_POST['password'] ?? ''),
		(string) ($_POST['confirm_password'] ?? '')
	);
	$activationResult = ['ok' => $ok, 'message' => $message];
	if ($ok) {
		$facultyIDValue = '';
		$emailValue = '';
	}
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Activate Faculty Account | Ashford University</title>
	<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
	<style>
		.faculty-claim-page{min-height:100vh;display:grid;place-items:center;padding:24px 16px;background:#f2f5fa;color:#172b45}
		.faculty-claim-card{width:min(100%,520px);box-sizing:border-box;padding:clamp(20px,5vw,36px);background:#fff;border:1px solid #d5dfeb;border-radius:14px;box-shadow:0 8px 28px rgba(17,43,75,.09)}
		.faculty-claim-brand{margin:0 0 8px;color:#24466f;font-size:13px;font-weight:750;letter-spacing:.08em;text-transform:uppercase}
		.faculty-claim-card h1{margin:0 0 8px;color:#142d4d;font-size:clamp(24px,6vw,30px);line-height:1.2}
		.faculty-claim-description{margin:0 0 22px;color:#4e627c;line-height:1.55}
		.faculty-claim-card label{display:block;margin:15px 0 6px;color:#223c5e;font-weight:650}
		.faculty-claim-card input{width:100%;min-height:46px;box-sizing:border-box;padding:10px 12px;border:1px solid #9fb2c8;border-radius:8px;background:#fff;color:#172b45;font:inherit;font-size:16px}
		.faculty-claim-card input:focus-visible,.faculty-claim-card button:focus-visible,.faculty-claim-card a:focus-visible{outline:3px solid #2875bf;outline-offset:2px}
		.faculty-claim-card button{width:100%;min-height:46px;margin-top:22px;border:0;border-radius:8px;background:#173a63;color:#fff;font:inherit;font-weight:700;cursor:pointer}
		.faculty-claim-card button:hover{background:#214d7c}
		.faculty-claim-result{padding:12px 14px;margin:16px 0;border-radius:8px;line-height:1.45}
		.faculty-claim-result.error{background:#fff0ed;border:1px solid #efb8b1;color:#872a24}
		.faculty-claim-result.success{background:#eaf6ee;border:1px solid #abd8b7;color:#175b34}
		.faculty-claim-help{margin:18px 0 0;color:#526780;font-size:13px;line-height:1.5}
		.faculty-claim-help a{color:#174d86;font-weight:650}
	</style>
</head>
<body>
	<main class="faculty-claim-page">
		<section class="faculty-claim-card" aria-labelledby="faculty-claim-title">
			<p class="faculty-claim-brand">Ashford University · Faculty Portal</p>
			<h1 id="faculty-claim-title">Activate Faculty Account</h1>
			<p class="faculty-claim-description">Enter the Faculty ID and email address that received the one-time verification code. After verification, choose a password for your existing Faculty account.</p>
			<?php if (is_array($activationResult)): ?>
				<div class="faculty-claim-result <?= $activationResult['ok'] ? 'success' : 'error' ?>" role="<?= $activationResult['ok'] ? 'status' : 'alert' ?>">
					<?= e($activationResult['message']) ?>
					<?php if ($activationResult['ok']): ?> <a href="<?= e(url('index.php')) ?>">Go to sign in</a><?php endif; ?>
				</div>
			<?php endif; ?>
			<form method="post" action="<?= e(url('faculty_activate.php')) ?>" autocomplete="on">
				<?= csrf_field() ?>
				<label for="faculty-id">Faculty ID</label>
				<input id="faculty-id" name="faculty_id" type="text" inputmode="numeric" pattern="[0-9]+" maxlength="10" required value="<?= e($facultyIDValue) ?>" autocomplete="username">

				<label for="faculty-email">Verified email address</label>
				<input id="faculty-email" name="email" type="email" maxlength="255" required value="<?= e($emailValue) ?>" autocomplete="email">

				<label for="verification-code">Six-digit verification code</label>
				<input id="verification-code" name="verification_code" type="text" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" required autocomplete="one-time-code">

				<label for="faculty-password">Choose password</label>
				<input id="faculty-password" name="password" type="password" minlength="10" required autocomplete="new-password" aria-describedby="faculty-password-help">
				<p id="faculty-password-help" class="faculty-claim-help">Use at least 10 characters, including uppercase and lowercase letters, a number, and a symbol.</p>

				<label for="faculty-confirm-password">Confirm password</label>
				<input id="faculty-confirm-password" name="confirm_password" type="password" minlength="10" required autocomplete="new-password">
				<button type="submit">Verify and activate account</button>
			</form>
			<p class="faculty-claim-help">Codes expire after 10 minutes and allow up to five attempts. If the code expired or you did not receive one, contact an authorized administrator.</p>
		</section>
	</main>
</body>
</html>

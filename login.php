<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$authConfig = require __DIR__ . '/config/auth.php';
$maxAttempts = max(1, (int) ($authConfig['max_attempts'] ?? 3));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_verify();
}

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
	$_SESSION['login_error'] = 'Please enter your email and password.';
	header('Location: ' . url('index.php'));
	exit;
}

$sql = "SELECT Login.*, User.first_Name, User.last_Name
		FROM Login
		JOIN User ON User.user_ID = Login.user_ID
		WHERE Login.user_Email = ?
		LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);

$user = $stmt->fetch();

if (!$user) {
	$_SESSION['login_error'] = 'Invalid email or password.';
	header('Location: ' . url('index.php'));
	exit;
}

if ($user['lock_var']) {
	$_SESSION['login_error'] = 'This account is locked. Contact an administrator.';
	header('Location: ' . url('index.php'));
	exit;
}

// Hashed comparison only: plaintext seeds are no longer accepted.
if (!password_verify($password, $user['user_Password'])) {

	$tries = (int) $user['no_Of_Tries'] + 1;
	$locked = $tries >= $maxAttempts ? 1 : 0;

	$sql = "UPDATE Login
			SET no_Of_Tries = ?, lock_var = ?
			WHERE user_ID = ?";

	$stmt = $pdo->prepare($sql);

	$stmt->execute([
		$tries,
		$locked,
		$user['user_ID']
	]);

	audit_log(
		$pdo,
		'LoginFailed',
		'Login',
		(string) $user['user_ID']
	);

	if ($locked) {
		$_SESSION['login_error'] =
			"Account locked after {$maxAttempts} unsuccessful attempts.";
	} else {
		$_SESSION['login_error'] =
			'Invalid email or password.';
	}

	header('Location: ' . url('index.php'));
	exit;
}

// Validate the selected portal against the authenticated account.
$selectedRole = (string) ($_POST['user_type'] ?? '');
$allowedRoles = ['Student', 'Faculty', 'Admin', 'StatStaff'];

if (!in_array($selectedRole, $allowedRoles, true)
    || $selectedRole !== $user['user_Type']) {
    $_SESSION['login_error'] = 'Invalid credentials for the selected account type.';
    header('Location: ' . url('index.php'));
    exit;
}

$sql = "UPDATE Login
		SET no_Of_Tries = 0, lock_var = 0
		WHERE user_ID = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user['user_ID']]);

// Prevent session fixation: start a fresh session id on login.
session_regenerate_id(true);

$_SESSION['user_id'] = (int) $user['user_ID'];
$_SESSION['user_type'] = $user['user_Type'];
$_SESSION['email'] = $user['user_Email'];
$_SESSION['name'] = $user['first_Name'] . ' ' . $user['last_Name'];

audit_log($pdo, 'Login', 'Login', (string) $user['user_ID']);

header('Location: ' . role_home($user['user_Type']));
exit;

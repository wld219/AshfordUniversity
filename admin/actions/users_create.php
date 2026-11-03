<?php

declare(strict_types=1);

// User creation action. Uses $pdo, $error from the entry point.
// Redirects on success; on failure sets the session error and returns.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

require_once __DIR__ . '/users_create_records.php';

$firstName = post_str('first_name', 50);
$lastName = post_str('last_name', 50);
$email = post_str('user_email', 255);
$role = post_str('user_role', 20);
$password = (string) ($_POST['password'] ?? '');
$confirm = (string) ($_POST['confirm_password'] ?? '');

$allowedRoles = ['Student', 'Faculty', 'StatStaff'];

// Only the lock manager may create administrator accounts.
if ($isLockManager) {
    $allowedRoles[] = 'Admin';
}

if (
	$firstName === '' ||
	$lastName === '' ||
	!valid_email($email) ||
	!in_array($role, $allowedRoles, true)
) {
	$_SESSION['users_error'] = 'Please complete every field with a valid value.';
	return;
}

if ($password !== $confirm) {
	$_SESSION['users_error'] = 'Passwords do not match.';
	return;
}

[$policyOk, $policyMessage] = password_policy_ok($password);

if (!$policyOk) {
	$_SESSION['users_error'] = $policyMessage;
	return;
}

$duplicate = one(
	$pdo,
	'SELECT user_ID FROM Login WHERE user_Email = ?',
	[$email]
);

if ($duplicate) {
	$_SESSION['users_error'] = 'An account with this email already exists.';
	return;
}

try {

	$pdo->beginTransaction();

	$userID = next_user_id($pdo, $role);

	// Shared user record
	$pdo->prepare(
		'INSERT INTO User
			(user_ID, first_Name, middle_Name, last_Name, gender,
			DOB, street, city, state, zip_Code, user_Type)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
	)->execute([
		$userID,
		$firstName,
		null,
		$lastName,
		'Other',
		'1990-01-01',
		'N/A',
		'N/A',
		'N/A',
		'00000',
		$role,
	]);

	// Login record
	$pdo->prepare(
		'INSERT INTO Login
			(user_ID, user_Email, user_Password, no_Of_Tries, lock_var, user_Type)
		VALUES (?, ?, ?, 0, 0, ?)'
	)->execute([
		$userID,
		$email,
		password_hash($password, PASSWORD_DEFAULT),
		$role,
	]);

	// Role record
	if ($role === 'Student') {
		create_student_record($pdo, $userID);
	} elseif ($role === 'Faculty') {
		create_faculty_record($pdo, $userID);
	} else {
		create_staff_record($pdo, $userID, $role);
	}

	$pdo->commit();

	audit_log(
		$pdo,
		'Create',
		$role,
		(string) $userID,
		'Admin portal account creation'
	);

	$_SESSION['flash'] = [
		'type' => 'success',
		'message' => "{$role} account {$userID} created.",
	];

} catch (Throwable $e) {

	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}

	error_log('User creation failed: ' . $e->getMessage());

	$_SESSION['users_error'] =
		'The account could not be created. Please check the details.';
}

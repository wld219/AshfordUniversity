<?php

declare(strict_types=1);

// Unlock a locked user account. Uses $pdo, $adminID, $error from the entry
// point. Resets lock_var and no_Of_Tries so the user can log in again.
// Full-Access administrators only.

$userID = post_int('user_id') ?? 0;

$target = one(
	$pdo,
	'SELECT user_ID, user_Type FROM User WHERE user_ID = ?',
	[$userID]
);

if (!$target) {
	$error = 'User not found.';
	return;
}

try {

	$pdo->beginTransaction();

	$pdo->prepare(
		'UPDATE Login
		SET lock_var = 0, no_Of_Tries = 0
		WHERE user_ID = ?'
	)->execute([$userID]);

	audit_log(
		$pdo,
		'Unlock',
		'Login',
		(string) $userID,
		"Account unlocked by administrator {$adminID}"
	);

	$pdo->commit();

	$_SESSION['flash'] = [
		'type' => 'success',
		'message' => "Account {$userID} ({$target['user_Type']}) has been unlocked.",
	];

} catch (Throwable $e) {

	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}

	error_log('Account unlock failed: ' . $e->getMessage());

	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => 'The account could not be unlocked.',
	];

}

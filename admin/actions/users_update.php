<?php

declare(strict_types=1);

// User update action. Uses $pdo from the entry point.
// Redirects on success; on failure sets a flash message and returns.

$userID = post_int('user_id') ?? 0;

$firstName = post_str('first_name', 50);
$lastName = post_str('last_name', 50);
$city = post_str('city', 50);
$state = post_str('state', 50);

$oldUser = one(
	$pdo,
	"SELECT first_Name, last_Name, city, state
	FROM User
	WHERE user_ID = ?",
	[$userID]
);

if (!$oldUser) {
	return;
}

try {

	$pdo->beginTransaction();

	$pdo->prepare(
		'UPDATE User
		SET first_Name = ?, last_Name = ?, city = ?, state = ?
		WHERE user_ID = ?'
	)->execute([
		$firstName,
		$lastName,
		$city,
		$state,
		$userID
	]);

	log_user_changes(
		$pdo,
		$userID,
		$oldUser,
		[
			'first_Name' => $firstName,
			'last_Name' => $lastName,
			'city' => $city,
			'state' => $state
		],
		'Admin portal update'
	);

	$pdo->commit();

	audit_log($pdo, 'Update', 'User', (string) $userID);

	$_SESSION['flash'] = [
		'type' => 'success',
		'message' => 'User information updated.',
	];

} catch (PDOException $e) {

	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}

	error_log('User update failed: ' . $e->getMessage());

	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => 'The user could not be updated.',
	];
}

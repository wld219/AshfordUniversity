<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/mail.php';

/**
 * Password-reset workflow, plain functions.
 *
 * Storage model (table Password_Reset):
 *   user_ID     - who requested the reset
 *   token_Hash  - SHA-256 of the random token (the token itself is never stored)
 *   expires_At  - when the link stops working
 *   used_At     - set once the link has been consumed
 *
 * Flow: requestPassword() creates and emails the token, resetTokenStatus()
 * checks a link, and completePasswordReset() consumes it and sets the new
 * password. Requests never reveal whether an address is registered.
 */

// The values from config/auth.php, read once.
function auth_config(): array
{
	static $config = null;

	if ($config === null) {
		$config = require dirname(__DIR__) . '/config/auth.php';
	}

	return $config;
}

// Start a password reset for an email address (no-op when unknown).
function request_password_reset(PDO $pdo, string $email): void
{
	$email = strtolower(trim($email));

	if ($email === '') {
		return;
	}

	// Look the account up; unknown addresses are silently ignored.
	$user = one(
		$pdo,
		"SELECT u.user_ID, u.first_Name, u.last_Name
		FROM Login l
		JOIN User u ON u.user_ID = l.user_ID
		WHERE l.user_Email = ?
		LIMIT 1",
		[$email]
	);

	if (!$user) {
		return;
	}

	$userId = (int) $user['user_ID'];

	// Rate limit: one request per cooldown window.
	$recent = one(
		$pdo,
		"SELECT 1 FROM Password_Reset
		WHERE user_ID = ?
		AND requested_At > DATE_SUB(NOW(), INTERVAL ? SECOND)
		LIMIT 1",
		[$userId, auth_config()['reset_cooldown']]
	);

	if ($recent) {
		return;
	}

	// Random token; only its SHA-256 hash is stored.
	$token = bin2hex(random_bytes(32));
	$tokenHash = hash('sha256', $token);
	$ttl = (int) auth_config()['reset_ttl'];

	// Any previous unused links for this account stop working.
	$pdo->prepare('DELETE FROM Password_Reset WHERE user_ID = ? AND used_At IS NULL')
		->execute([$userId]);

	$pdo->prepare(
		'INSERT INTO Password_Reset (user_ID, token_Hash, expires_At)
		VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))'
	)->execute([$userId, $tokenHash, $ttl]);

	$name = trim((string) $user['first_Name'] . ' ' . (string) $user['last_Name']);
	$resetUrl = url('reset_password.php?token=' . urlencode($token));

	try {
		send_password_reset_email($email, $name, $resetUrl);
	} catch (Throwable $e) {
		// Never tell the requester whether delivery failed.
		error_log('Password reset email failed: ' . $e->getMessage());
	}

	audit_log($pdo, 'PasswordResetRequest', 'Login', (string) $userId);
}

// Is this reset link usable right now? 'valid' or 'invalid'.
function reset_token_status(PDO $pdo, string $token): string
{
	$token = trim($token);

	if ($token === '' || preg_match('/^[A-Za-z0-9]+$/', $token) !== 1) {
		return 'invalid';
	}

	$row = one(
		$pdo,
		"SELECT expires_At, used_At
		FROM Password_Reset
		WHERE token_Hash = ?
		LIMIT 1",
		[hash('sha256', $token)]
	);

	if (!$row || $row['used_At'] !== null) {
		return 'invalid';
	}

	if (strtotime((string) $row['expires_At']) <= time()) {
		return 'invalid';
	}

	return 'valid';
}

// Consume a token and set the new password.
function complete_password_reset(
	PDO $pdo,
	string $token,
	string $password,
	string $confirm
): array {

	$token = trim($token);

	if (reset_token_status($pdo, $token) !== 'valid') {
		return [false, 'This password reset link is invalid, expired, or already used.'];
	}

	if ($password !== $confirm) {
		return [false, 'Passwords do not match.'];
	}

	[$policyOk, $policyMessage] = password_policy_ok($password);

	if (!$policyOk) {
		return [false, $policyMessage];
	}

	[$same, $sameMessage] = password_is_same_as_current($pdo, (int) $row['user_ID'], $password);

	if ($same) {
		return [false, $sameMessage];
	}

	[$reused, $reusedMessage] = password_is_reused($pdo, (int) $row['user_ID'], $password);

	if ($reused) {
		return [false, $reusedMessage];
	}

	$row = one(
		$pdo,
		"SELECT reset_ID, user_ID
		FROM Password_Reset
		WHERE token_Hash = ?
		LIMIT 1",
		[hash('sha256', $token)]
	);

	if (!$row) {
		return [false, 'This password reset link is invalid, expired, or already used.'];
	}

	try {

		$pdo->beginTransaction();

		// Preserve the old hash in Password_History to enforce no-reuse.
		$oldHash = one(
			$pdo,
			'SELECT user_Password FROM Login WHERE user_ID = ?',
			[(int) $row['user_ID']]
		);
		if ($oldHash) {
			$pdo->prepare(
				'INSERT INTO Password_History (user_ID, password_hash) VALUES (?, ?)'
			)->execute([(int) $row['user_ID'], $oldHash['user_Password']]);

			// Retain only the last 5 hashes per user.
			$pdo->prepare(
				'DELETE ph FROM Password_History ph
				 LEFT JOIN (
					 SELECT history_ID FROM Password_History
					 WHERE user_ID = ?
					 ORDER BY changed_At DESC LIMIT 5
				 ) keep ON keep.history_ID = ph.history_ID
				 WHERE ph.user_ID = ? AND keep.history_ID IS NULL'
			)->execute([(int) $row['user_ID'], (int) $row['user_ID']]);
		}

		// New hashed password, and the account is unlocked again.
		$pdo->prepare(
			'UPDATE Login
			SET user_Password = ?, no_Of_Tries = 0, lock_var = 0
			WHERE user_ID = ?'
		)->execute([
			password_hash($password, PASSWORD_DEFAULT),
			(int) $row['user_ID'],
		]);

		// Single use: mark the token consumed.
		$pdo->prepare('UPDATE Password_Reset SET used_At = NOW() WHERE reset_ID = ?')
			->execute([(int) $row['reset_ID']]);

		$pdo->commit();

	} catch (Throwable $e) {

		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}

		error_log('Password reset failed: ' . $e->getMessage());

		return [false, 'The password could not be updated. Please try again.'];
	}

	audit_log($pdo, 'PasswordReset', 'Login', (string) $row['user_ID']);

	return [true, 'Your password has been updated. You can now sign in.'];
}

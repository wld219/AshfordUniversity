<?php

declare(strict_types=1);

// Validation helpers.

// Basic RFC-style email validation plus realistic length limits.
function valid_email(string $email): bool
{
	return (bool) filter_var($email, FILTER_VALIDATE_EMAIL)
		&& strlen($email) <= 255;
}

// Validate a YYYY-MM-DD date.
function valid_date(string $date): bool
{
	if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
		return false;
	}

	[$y, $m, $d] = array_map('intval', explode('-', $date));

	return checkdate($m, $d, $y);
}

// Check that a date is strictly in the past (used for DOB).
function is_past_date(string $date): bool
{
	return valid_date($date) && strtotime($date) < strtotime('today');
}

// Check that a date is today or in the past (used for appointment dates).
function is_past_or_today(string $date): bool
{
	return valid_date($date) && strtotime($date) <= strtotime('today');
}

// Password policy: at least 10 characters with upper, lower, and a digit.
// @return array{0: bool, 1: string} [passed, message]
function password_policy_ok(string $password): array
{
	if (strlen($password) < 10) {
		return [false, 'Password must be at least 10 characters long.'];
	}

	if (!preg_match('/[A-Z]/', $password)
		|| !preg_match('/[a-z]/', $password)
		|| !preg_match('/[0-9]/', $password)) {
		return [false, 'Password must include an uppercase letter, a lowercase letter, and a number.'];
	}

	if (preg_match('/[^A-Za-z0-9]/', $password) === 0) {
		return [false, 'Password must include at least one symbol.'];
	}

	return [true, ''];
}

// Reject reuse of any of the last 5 passwords stored in Password_History.
// @return array{0: bool, 1: string} [reused, message]
function password_is_reused(PDO $pdo, int $userID, string $newPassword): array
{
	$hashes = all_rows(
		$pdo,
		'SELECT password_hash FROM Password_History WHERE user_ID = ? ORDER BY changed_At DESC LIMIT 5',
		[$userID]
	);

	foreach ($hashes as $row) {
		if (password_verify($newPassword, $row['password_hash'])) {
			return [true, 'Password has been used recently. Choose a different one.'];
		}
	}

	return [false, ''];
}

// Reject setting a new password equal to the current password.
// @return array{0: bool, 1: string} [same, message]
function password_is_same_as_current(PDO $pdo, int $userID, string $newPassword): array
{
	$row = one(
		$pdo,
		'SELECT user_Password FROM Login WHERE user_ID = ?',
		[$userID]
	);

	if (!$row) {
		return [false, ''];
	}

	if (password_verify($newPassword, $row['user_Password'])) {
		return [true, 'New password cannot be the same as the current password.'];
	}

	return [false, ''];
}

// Validate a letter grade (A-F with optional +/-) or an empty string.
function valid_grade(?string $grade): bool
{
	return $grade === null || $grade === '' || (bool) preg_match('/^[A-F][+-]?$/i', $grade);
}

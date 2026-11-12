<?php

declare(strict_types=1);

// Installer action: validate the form and create the first administrator.
// Uses $pdo, $error from the entry point.

$firstName = post_str('first_name', 50);
$lastName = post_str('last_name', 50);
$email = post_str('user_email', 255);
$password = (string) ($_POST['password'] ?? '');
$confirm = (string) ($_POST['confirm_password'] ?? '');

if ($firstName === '' || $lastName === '' || $email === '') {
	$error = 'Please fill in every field.';
} elseif (!valid_email($email)) {
	$error = 'Please enter a valid email address.';
} elseif ($password !== $confirm) {
	$error = 'Passwords do not match.';
} else {

	[$policyOk, $policyMessage] = password_policy_ok($password);

	if (!$policyOk) {
		$error = $policyMessage;
	} else {

		try {
			$pdo->beginTransaction();

			// Re-check inside the transaction to prevent a double install.
			$row = one($pdo, 'SELECT COUNT(*) AS total FROM User');
			if ((int) $row['total'] > 0) {
				throw new RuntimeException('Portal already installed.');
			}

			// Administrator IDs live in the 300000 range (students 100xxx,
			// faculty 200xxx, staff 400xxx).
			$nextId = (int) (one(
				$pdo,
				'SELECT GREATEST(COALESCE(MAX(user_ID), 0), 300000) + 1 AS next_id FROM User'
			)['next_id'] ?? 300001);

			$pdo->prepare(
				'INSERT INTO User
					(user_ID, first_Name, middle_Name, last_Name, gender, DOB,
					street, city, state, zip_Code, user_Type)
				VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?)'
			)->execute([
				$nextId,
				$firstName,
				$lastName,
				post_str('gender', 10) ?: 'Other',
				post_str('dob', 10) ?: '1990-01-01',
				post_str('street', 100) ?: 'N/A',
				post_str('city', 50) ?: 'N/A',
				post_str('state', 50) ?: 'NY',
				post_str('zip_code', 10) ?: '00000',
				'Admin',
			]);

			$pdo->prepare(
				'INSERT INTO Login (user_ID, user_Email, user_Password, no_Of_Tries, lock_var, user_Type)
				VALUES (?, ?, ?, 0, 0, ?)'
			)->execute([
				$nextId,
				$email,
				password_hash($password, PASSWORD_DEFAULT),
				'Admin',
			]);

			$pdo->prepare(
				'INSERT INTO Admin (user_ID, security_Level) VALUES (?, ?)'
			)->execute([$nextId, 'Full-Access']);

			$pdo->commit();

			audit_log($pdo, 'Install', 'User', (string) $nextId, 'First administrator');

			// Hash all seeded login passwords from database/login.sql so they
			// work with password_verify(). The seed file stores plaintext
			// passwords; bcrypt hashes are required by the login path.
			seedLoginPasswords($pdo);

			redirect_with_flash(
				url('index.php'),
				'success',
				'Administrator account created. You can now sign in.'
			);
		} catch (Throwable $e) {

			if ($pdo->inTransaction()) {
				$pdo->rollBack();
			}

			$error = $e->getMessage() === 'Portal already installed.'
				? 'The portal has already been installed.'
				: 'Installation failed. Please check the database and try again.';
		}
	}
}

/**
 * Hash all plaintext login passwords from database/login.sql so they work
 * with password_verify(). The seed file stores plaintext passwords; bcrypt
 * hashes are required by the login path. Passwords already hashed (starting
 * with $2y$ or $2a$) are skipped.
 */
function seedLoginPasswords(PDO $pdo): void
{
	$sqlFile = __DIR__ . '/../database/login.sql';

	if (!is_file($sqlFile)) {
		return;
	}

	$lines = file($sqlFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

	$update = $pdo->prepare(
		'UPDATE Login SET user_Password = ?, no_Of_Tries = 0, lock_var = 0 WHERE user_ID = ?'
	);

	$count = 0;

	foreach ($lines as $line) {
		$line = trim($line);

		if (!preg_match("/^\(\s*(\d+)\s*,\s*'[^']+'\s*,\s*'([^']+)'\s*,/", $line, $m)) {
			continue;
		}

		$userId = (int) $m[1];
		$password = $m[2];

		// Skip if already hashed
		if (str_starts_with($password, '$2y$') || str_starts_with($password, '$2a$')) {
			continue;
		}

		$hash = password_hash($password, PASSWORD_DEFAULT);

		if ($hash !== false) {
			$update->execute([$hash, $userId]);
			$count++;
		}
	}

	error_log('Seed login passwords hashed: ' . $count);
}

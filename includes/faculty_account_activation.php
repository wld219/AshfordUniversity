<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/password_reset.php';

/**
 * Bind a one-time activation code to the selected faculty identity and the
 * exact normalized email address. The database stores only a password hash.
 */
function faculty_activation_code_payload(string $code, int $facultyID, string $email): string
{
	return json_encode(
		[$code, $facultyID, strtolower(trim($email)), 'faculty-claim-v1'],
		JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
	);
}

/** Pre-hash the full binding before bcrypt so long email values are not truncated. */
function faculty_activation_code_verifier(string $code, int $facultyID, string $email): string
{
	return hash('sha256', faculty_activation_code_payload($code, $facultyID, $email));
}

/**
 * Send a verification code for an existing Faculty/User pair with no Login.
 * No User or Faculty records are created here.
 *
 * @return array{0:bool,1:string}
 */
function request_faculty_account_activation(PDO $pdo, int $facultyID, string $email): array
{
	$email = strtolower(trim($email));
	if (!reset_local_only()) {
		return [false, 'Faculty account activation is available only from localhost.'];
	}
	if ($facultyID <= 0 || !valid_email($email)) {
		return [false, 'Enter a valid university email address for this Faculty record.'];
	}

	try {
		$pdo->beginTransaction();
		$faculty = one(
			$pdo,
			"SELECT f.faculty_ID, u.user_Type
			 FROM Faculty f
			 JOIN User u ON u.user_ID = f.faculty_ID
			 WHERE f.faculty_ID = ?
			 FOR UPDATE",
			[$facultyID]
		);
		if (!$faculty || $faculty['user_Type'] !== 'Faculty') {
			$pdo->rollBack();
			return [false, 'That Faculty account is not available for activation.'];
		}

		if (one($pdo, 'SELECT user_ID FROM Login WHERE user_ID = ? LIMIT 1 FOR UPDATE', [$facultyID])) {
			$pdo->rollBack();
			return [false, 'This Faculty record already has a Login account.'];
		}

		if (one($pdo, 'SELECT user_ID FROM Login WHERE user_Email = ? LIMIT 1 FOR UPDATE', [$email])) {
			$pdo->rollBack();
			return [false, 'That email address is already associated with an account.'];
		}

		$existingCode = one(
			$pdo,
			'SELECT requested_At FROM Password_Reset_Code WHERE user_ID = ? FOR UPDATE',
			[$facultyID]
		);
		if ($existingCode && strtotime((string) $existingCode['requested_At']) > time() - 60) {
			$pdo->rollBack();
			return [false, 'A verification code was requested recently. Wait one minute before requesting another.'];
		}

		$code = (string) random_int(100000, 999999);
		$codeHash = password_hash(
			faculty_activation_code_verifier($code, $facultyID, $email),
			PASSWORD_DEFAULT
		);
		$pdo->prepare(
			'INSERT INTO Password_Reset_Code
				(user_ID, code_hash, expires_At, requested_At, attempts, used_At)
			 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW(), 0, NULL)
			 ON DUPLICATE KEY UPDATE code_hash = VALUES(code_hash),
				expires_At = VALUES(expires_At), requested_At = NOW(), attempts = 0, used_At = NULL'
		)->execute([$facultyID, $codeHash]);
		$pdo->commit();

		$claimUrl = url('faculty_activate.php');
		$body = "A Faculty account activation was requested for Faculty ID {$facultyID}.\n\n"
			. "Your verification code is: {$code}\n\n"
			. "Enter this code, your email address, and a password you choose at:\n{$claimUrl}\n\n"
			. "The code expires in 10 minutes and allows up to five verification attempts. Ashford staff will never ask you to share your password. If you did not request this, ignore this message.";

		try {
			dev_mail_deliver($email, 'Ashford University Faculty Account Verification', $body);
		} catch (Throwable $mailError) {
			$pdo->prepare(
				'UPDATE Password_Reset_Code SET used_At = NOW(), attempts = 5
				 WHERE user_ID = ? AND code_hash = ? AND used_At IS NULL'
			)->execute([$facultyID, $codeHash]);
			audit_log($pdo, 'FacultyActivationDeliveryFailed', 'FacultyAccount', (string) $facultyID);
			return [false, 'The verification email could not be sent. No account was activated. Check mail delivery and try again later.'];
		}

		audit_log($pdo, 'FacultyActivationRequested', 'FacultyAccount', (string) $facultyID);
		return [true, 'A verification code was sent to the entered address. No Login account was created; sign-in remains unavailable until the code is verified.'];
	} catch (Throwable $error) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		error_log('Faculty activation request failed.');
		return [false, 'The verification request could not be completed. No account was activated.'];
	}
}

/**
 * Complete a verified claim by inserting Login for the existing Faculty row.
 *
 * @return array{0:bool,1:string}
 */
function complete_faculty_account_activation(
	PDO $pdo,
	int $facultyID,
	string $email,
	string $code,
	string $password,
	string $confirmPassword
): array {
	$email = strtolower(trim($email));
	$code = trim($code);
	$invalidCode = 'The Faculty ID, email, or verification code is invalid, expired, or already used.';

	if (!reset_local_only()) {
		return [false, 'Faculty account activation is available only from localhost.'];
	}
	if ($facultyID <= 0 || !valid_email($email) || !preg_match('/^[0-9]{6}$/', $code)) {
		return [false, $invalidCode];
	}
	if ($password !== $confirmPassword) {
		return [false, 'Passwords do not match.'];
	}
	[$passwordOk, $passwordMessage] = password_policy_ok($password);
	if (!$passwordOk) {
		return [false, $passwordMessage];
	}

	try {
		$pdo->beginTransaction();
		$faculty = one(
			$pdo,
			"SELECT f.faculty_ID, u.user_Type, u.is_active
			 FROM Faculty f
			 JOIN User u ON u.user_ID = f.faculty_ID
			 WHERE f.faculty_ID = ?
			 FOR UPDATE",
			[$facultyID]
		);
		if (!$faculty || $faculty['user_Type'] !== 'Faculty') {
			$pdo->rollBack();
			return [false, $invalidCode];
		}

		if (one($pdo, 'SELECT user_ID FROM Login WHERE user_ID = ? LIMIT 1 FOR UPDATE', [$facultyID])) {
			$pdo->rollBack();
			return [false, $invalidCode];
		}

		$verification = one(
			$pdo,
			'SELECT code_hash, expires_At, attempts, used_At
			 FROM Password_Reset_Code WHERE user_ID = ? FOR UPDATE',
			[$facultyID]
		);
		if (!$verification
			|| $verification['used_At'] !== null
			|| strtotime((string) $verification['expires_At']) <= time()
			|| (int) $verification['attempts'] >= 5) {
			$pdo->rollBack();
			return [false, $invalidCode];
		}

		$boundValue = faculty_activation_code_verifier($code, $facultyID, $email);
		if (!password_verify($boundValue, (string) $verification['code_hash'])) {
			$pdo->prepare('UPDATE Password_Reset_Code SET attempts = attempts + 1 WHERE user_ID = ?')
				->execute([$facultyID]);
			$pdo->commit();
			return [false, $invalidCode];
		}

		if (one($pdo, 'SELECT user_ID FROM Login WHERE user_Email = ? LIMIT 1 FOR UPDATE', [$email])) {
			$pdo->rollBack();
			return [false, 'That email address is already associated with an account. Contact an administrator.'];
		}

		[$passwordReused, $reuseMessage] = password_is_reused($pdo, $facultyID, $password);
		if ($passwordReused) {
			$pdo->rollBack();
			return [false, $reuseMessage];
		}

		$pdo->prepare(
			"INSERT INTO Login (user_ID, user_Email, user_Password, no_Of_Tries, lock_var, user_Type)
			 VALUES (?, ?, ?, 0, 0, 'Faculty')"
		)->execute([$facultyID, $email, password_hash($password, PASSWORD_DEFAULT)]);
		$pdo->prepare('UPDATE User SET is_active = 1 WHERE user_ID = ? AND user_Type = \'Faculty\'')
			->execute([$facultyID]);
		$pdo->prepare('UPDATE Password_Reset_Code SET used_At = NOW() WHERE user_ID = ?')
			->execute([$facultyID]);
		audit_log($pdo, 'FacultyAccountActivated', 'FacultyAccount', (string) $facultyID, 'Email ownership verified; Login created for existing Faculty record.');
		$pdo->commit();

		return [true, 'Faculty account activated. You can now sign in with the email address and password you chose.'];
	} catch (Throwable $error) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		if ($error instanceof PDOException && $error->getCode() === '23000') {
			return [false, 'The account or email address was activated in another request. Check your sign-in details or contact an administrator.'];
		}
		error_log('Faculty activation completion failed.');
		return [false, 'The account could not be activated. No duplicate account was created.'];
	}
}

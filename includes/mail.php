<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Password-reset email via PHPMailer.
 *
 * PHPMailer ships in lib/PHPMailer (three files, no Composer needed):
 *   lib/PHPMailer/Exception.php
 *   lib/PHPMailer/PHPMailer.php
 *   lib/PHPMailer/SMTP.php
 */

// Load PHPMailer when it has not been loaded yet.
if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {

	$lib = dirname(__DIR__) . '/lib/PHPMailer/';

	if (is_file($lib . 'PHPMailer.php')) {
		require_once $lib . 'Exception.php';
		require_once $lib . 'PHPMailer.php';
		require_once $lib . 'SMTP.php';
	}
}

// Which transport is active: 'phpmailer' or 'null' (log only).
function mail_transport(): string
{
	if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
		return 'null';
	}

	$transport = strtolower((string) env_value('MAIL_TRANSPORT', ''));

	if ($transport === 'null') {
		return 'null';
	}

	return 'phpmailer';
}

/**
 * Send the password-reset email. Returns true when the message was sent or
 * dropped on purpose; PHPMailer failures throw so callers can log them.
 */
function send_password_reset_email(
	string $toEmail,
	string $toName,
	string $resetUrl
): bool {

	$fromAddress = env_value('MAIL_FROM_ADDRESS', 'no-reply@ashford.edu') ?? 'no-reply@ashford.edu';
	$fromName = env_value('MAIL_FROM_NAME', 'Ashford University') ?? 'Ashford University';

	$subject = 'Reset your Ashford University password';

	$minutes = (int) round(env_int('AUTH_RESET_TTL', 3600) / 60);

	$body = "Hello " . ($toName !== '' ? $toName : 'there') . ",\n\n"
		. "A password reset was requested for your Ashford University account.\n\n"
		. "Use the link below to choose a new password. The link expires in "
		. $minutes . " minutes and can be used once:\n\n"
		. $resetUrl . "\n\n"
		. "If you did not request this, ignore this email; your password will not change.\n\n"
		. "-- Ashford University";

	// Local development: log the link instead of sending mail.
	if (mail_transport() === 'null') {

		error_log('[mail] Password reset link for ' . $toEmail . ': ' . $resetUrl);

		return true;
	}

	$mail = new PHPMailer\PHPMailer\PHPMailer(true);

	$mail->isSMTP();
	$mail->Host = env_value('MAIL_HOST', '127.0.0.1') ?? '127.0.0.1';
	$mail->Port = env_int('MAIL_PORT', 1025);

	$encryption = strtolower((string) env_value('MAIL_ENCRYPTION', 'tls'));

	if ($encryption === 'tls') {
		$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
	} elseif ($encryption === 'ssl') {
		$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
	}

	$username = (string) env_value('MAIL_USERNAME', '');

	if ($username !== '') {
		$mail->SMTPAuth = true;
		$mail->Username = $username;
		$mail->Password = (string) env_value('MAIL_PASSWORD', '');
	}

	$mail->CharSet = 'UTF-8';
	$mail->setFrom($fromAddress, $fromName);
	$mail->addAddress($toEmail, $toName !== '' ? $toName : $toEmail);
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->AltBody = $body;

	$mail->send();

	return true;
}

/**
 * Send a plain notification email (used for section cancellations).
 * Same transport rules as the password reset email.
 */
function send_generic_email(
	string $toEmail,
	string $toName,
	string $subject,
	string $body
): bool {

	if (mail_transport() === 'null') {

		error_log('[mail] ' . $subject . ' -> ' . $toEmail);

		return true;
	}

	$fromAddress = env_value('MAIL_FROM_ADDRESS', 'no-reply@ashford.edu') ?? 'no-reply@ashford.edu';
	$fromName = env_value('MAIL_FROM_NAME', 'Ashford University') ?? 'Ashford University';

	$mail = new PHPMailer\PHPMailer\PHPMailer(true);

	$mail->isSMTP();
	$mail->Host = env_value('MAIL_HOST', '127.0.0.1') ?? '127.0.0.1';
	$mail->Port = env_int('MAIL_PORT', 1025);

	$encryption = strtolower((string) env_value('MAIL_ENCRYPTION', 'tls'));

	if ($encryption === 'tls') {
		$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
	} elseif ($encryption === 'ssl') {
		$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
	}

	$username = (string) env_value('MAIL_USERNAME', '');

	if ($username !== '') {
		$mail->SMTPAuth = true;
		$mail->Username = $username;
		$mail->Password = (string) env_value('MAIL_PASSWORD', '');
	}

	$mail->CharSet = 'UTF-8';
	$mail->setFrom($fromAddress, $fromName);
	$mail->addAddress($toEmail, $toName !== '' ? $toName : $toEmail);
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->AltBody = $body;

	$mail->send();

	return true;
}

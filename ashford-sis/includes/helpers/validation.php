<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php';

// Password complexity: min 8, at least one upper, one lower, one digit, one symbol.
function password_is_complex(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[^A-Za-z0-9]/', $password);
}

// Reject reuse of the last 5 passwords.
function password_is_reused(PDO $pdo, int $userID, string $newPassword): bool
{
    $hashes = all_rows(
        $pdo,
        'SELECT password_hash FROM Password_History WHERE user_ID = ? ORDER BY changed_At DESC LIMIT 5',
        [$userID]
    );

    foreach ($hashes as $row) {
        if (password_verify($newPassword, $row['password_hash'])) {
            return true;
        }
    }

    return false;
}

// Return [bool $ok, string|null $message]
function change_password(PDO $pdo, int $userID, string $currentPassword, string $newPassword): array
{
    $row = one($pdo, 'SELECT password_hash FROM Login WHERE user_ID = ?', [$userID]);

    if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
        return [false, 'Current password is incorrect.'];
    }

    if (!password_is_complex($newPassword)) {
        return [false, 'New password must be at least 8 characters with upper, lower, digit, and symbol.'];
    }

    if ($newPassword === $currentPassword) {
        return [false, 'New password must differ from the current password.'];
    }

    if (password_is_reused($pdo, $userID, $newPassword)) {
        return [false, 'Password has been used recently. Choose a different one.'];
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    try {
        $pdo->prepare(
            'INSERT INTO Password_History (user_ID, password_hash) VALUES (?, ?)'
        )->execute([$userID, $row['password_hash']]);

        // Retain only last 5
        $pdo->prepare(
            'DELETE ph FROM Password_History ph
             LEFT JOIN (
                 SELECT history_ID FROM Password_History
                 WHERE user_ID = ?
                 ORDER BY changed_At DESC LIMIT 5
             ) keep ON keep.history_ID = ph.history_ID
             WHERE ph.user_ID = ? AND keep.history_ID IS NULL'
        )->execute([$userID, $userID]);

        $pdo->prepare(
            'UPDATE Login SET password_hash = ?, no_Of_Tries = 0, lock_var = FALSE WHERE user_ID = ?'
        )->execute([$newHash, $userID]);

        $pdo->commit();

        return [true, null];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return [false, 'Password change failed.'];
    }
}

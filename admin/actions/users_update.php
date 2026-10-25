<?php

declare(strict_types=1);

// User update action. Uses $pdo, $isLockManager, $adminID from the entry
// point. Redirects on success; on failure sets a flash message and returns.
//
//   - Full-access may update profile fields (name, city, state) for
//     non-admin users only.
//   - Lock manager may update any user, including admin security level
//     and lock-manager flag.

$userID = post_int('user_id') ?? 0;

$firstName = post_str('first_name', 50);
$lastName = post_str('last_name', 50);
$city = post_str('city', 50);
$state = post_str('state', 50);

$oldUser = one(
    $pdo,
    "SELECT first_Name, last_Name, city, state, user_Type
     FROM User
     WHERE user_ID = ?",
    [$userID]
);

if (!$oldUser) {
    return;
}

$isAdminTarget = $oldUser['user_Type'] === 'Admin';

$oldSecurity = null;
$oldLockManager = null;

if ($isAdminTarget) {
    $oldSecurity = one(
        $pdo,
        'SELECT security_Level, lock_manager FROM Admin WHERE user_ID = ?',
        [$userID]
    );
    $oldSecurityLevel = $oldSecurity['security_Level'] ?? null;
    $oldLockManagerFlag = (int) ($oldSecurity['lock_manager'] ?? 0);
}

$newSecurityLevel = post_str('security_level', 20);
$newLockManager = post_int('lock_manager');

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
        [
            'first_Name' => $oldUser['first_Name'],
            'last_Name' => $oldUser['last_Name'],
            'city' => $oldUser['city'],
            'state' => $oldUser['state'],
        ],
        [
            'first_Name' => $firstName,
            'last_Name' => $lastName,
            'city' => $city,
            'state' => $state,
        ],
        'Admin portal update'
    );

    // Lock manager updating an admin's security level / lock-manager flag.
    if ($isAdminTarget && $isLockManager && $oldSecurity !== null) {

        $finalSecurityLevel = $newSecurityLevel !== ''
            ? $newSecurityLevel
            : $oldSecurityLevel;

        $finalLockManager = $newLockManager !== null
            ? (int) $newLockManager
            : $oldLockManagerFlag;

        $pdo->prepare(
            'UPDATE Admin
            SET security_Level = ?, lock_manager = ?
            WHERE user_ID = ?'
        )->execute([
            $finalSecurityLevel,
            $finalLockManager,
            $userID
        ]);

        if ($finalSecurityLevel !== $oldSecurityLevel) {
            log_user_changes(
                $pdo,
                $userID,
                ['security_Level' => $oldSecurityLevel],
                ['security_Level' => $finalSecurityLevel],
                'Admin portal update'
            );
        }

        if ((int) $finalLockManager !== $oldLockManagerFlag) {
            log_user_changes(
                $pdo,
                $userID,
                ['lock_manager' => (string) $oldLockManagerFlag],
                ['lock_manager' => (string) $finalLockManager],
                'Admin portal update'
            );
        }
    }

    $pdo->commit();

    audit_log($pdo, 'Update', 'User', (string) $userID);

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'User information updated.',
    ];

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('User update failed: ' . $e->getMessage());

    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'The user could not be updated.',
    ];
}

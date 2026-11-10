<?php

declare(strict_types=1);

// User update action. Uses $pdo, $isLockManager, $adminID from the entry
// point. Redirects on success; on failure sets a flash message and returns.
//
//   - Full-access may update profile fields (name, city, state) for
//     non-admin users only.
//   - Lock manager may update any user, including admin security level
//     and lock-manager flag.
//
// Read-only users (StatStaff) are blocked from all write operations.
require_write_access();

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
    $_SESSION['users_save_result'] = ['type'=>'error','message'=>'User not found.'];
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

$middleName = trim((string)($_POST['middle_name'] ?? ''));
$dob = trim((string)($_POST['dob'] ?? ''));
$gender = trim((string)($_POST['gender'] ?? ''));
$street = trim((string)($_POST['street'] ?? ''));
$zipCode = trim((string)($_POST['zip_code'] ?? ''));
$email = trim((string)($_POST['user_email'] ?? ''));
$active = post_int('is_active');
$isStudentTarget = $oldUser['user_Type'] === 'Student';
$studentType = trim((string)($_POST['student_type'] ?? ''));
$studentYear = post_int('student_year');
$majorID = post_int('major_id');
$majorIDs = array_values(array_unique(array_map('intval', (array)($_POST['major_ids'] ?? []))));
$minorIDs = array_values(array_unique(array_map('intval', (array)($_POST['minor_ids'] ?? []))));
$validDate = DateTimeImmutable::createFromFormat('!Y-m-d', $dob);
if (!$firstName || !$lastName || !$city || !$state || !$street || !$zipCode ||
    !in_array($gender, ['Male','Female','Other'], true) ||
    !$validDate || $validDate->format('Y-m-d') !== $dob ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($active, [0,1], true) ||
    ($isStudentTarget && (!in_array($studentType, ['Undergraduate','Graduate'],true) ||
        !$studentYear || $studentYear < 1 || $studentYear > 8 || !$majorID))) {
    $_SESSION['users_save_result'] = ['type'=>'error','message'=>'Please enter valid user information. All required fields must be completed.'];
    return;
}
if ($isStudentTarget) {
    $majorIDs[] = $majorID;
    $majorIDs = array_values(array_unique($majorIDs));
    foreach ($majorIDs as $id) {
        if ($id <= 0 || !one($pdo, 'SELECT major_ID FROM Major WHERE major_ID = ?', [$id])) {
            $_SESSION['users_save_result'] = ['type'=>'error','message'=>'Invalid major selection.']; return;
        }
    }
    foreach ($minorIDs as $id) {
        if ($id <= 0 || !one($pdo, 'SELECT minor_ID FROM Minor WHERE minor_ID = ?', [$id])) {
            $_SESSION['users_save_result'] = ['type'=>'error','message'=>'Invalid minor selection.']; return;
        }
    }
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

    $pdo->prepare('UPDATE User SET middle_Name=?, DOB=?, gender=?, street=?, zip_Code=?, is_active=? WHERE user_ID=?')
        ->execute([$middleName === '' ? null : $middleName,$dob,$gender,$street,$zipCode,$active,$userID]);
    $pdo->prepare('UPDATE Login SET user_Email=? WHERE user_ID=?')->execute([$email,$userID]);
    if ($isStudentTarget) {
        $pdo->prepare('UPDATE Student SET major_ID=?, student_Year=?, student_Type=? WHERE student_ID=?')
            ->execute([$majorID,$studentYear,$studentType,$userID]);
        foreach ([['Student_Major','major_ID',$majorIDs],['Student_Minor','minor_ID',$minorIDs]] as [$table,$column,$desired]) {
            $current = array_map('intval',array_column(all_rows($pdo,"SELECT {$column} FROM {$table} WHERE student_ID = ?",[$userID]),$column));
            $remove = array_diff($current,$desired);
            $add = array_diff($desired,$current);
            foreach ($remove as $id) {
                $pdo->prepare("DELETE FROM {$table} WHERE student_ID=? AND {$column}=?")->execute([$userID,$id]);
            }
            foreach ($add as $id) {
                $pdo->prepare("INSERT INTO {$table} (student_ID,{$column},date_Of_Choice) VALUES (?,?,CURRENT_DATE())")
                    ->execute([$userID,$id]);
            }
        }
    }
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

        if (!in_array($finalSecurityLevel, ['Full-Access', 'Read-Only'], true)) {
            throw new RuntimeException('Invalid security level.');
        }
        if ($newLockManager !== null && !in_array($newLockManager, [0, 1], true)) {
            throw new RuntimeException('Invalid lock manager flag.');
        }

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

    $_SESSION['users_save_result'] = ['type'=>'success','message'=>'User information updated successfully.'];

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('User update failed: ' . $e->getMessage());

    $_SESSION['users_save_result'] = ['type'=>'error','message'=>'The user could not be updated. Check for duplicate email, invalid values, or database constraints.'];
}

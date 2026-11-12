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
$secondMajorID = post_int('second_major_id');
$majorIDs = $majorID ? [$majorID] : [];
if ($secondMajorID) {
    $majorIDs[] = $secondMajorID;
}
$minorIDs = array_values(array_unique(array_map('intval', (array)($_POST['minor_ids'] ?? []))));
// Empty optional minor values are allowed; normalize them before validation.
$minorIDs = array_values(array_filter($minorIDs, static fn(int $id): bool => $id > 0));
// Enforce SRS: one required major, up to two majors total,
// at most one minor, and no more than two declarations combined.
if ($isStudentTarget && (
    ($secondMajorID && $secondMajorID === $majorID) ||
    count($majorIDs) > 2 ||
    count($minorIDs) > 1 ||
    count($majorIDs) + count($minorIDs) > 2
)) {
    $_SESSION['users_save_result'] = [
        'type' => 'error',
        'message' => 'Choose two majors or one major and one minor. Duplicate majors are not allowed.'
    ];
    return;
}


// ASHFORD_PROFILE_VALIDATION_V4
$profileErrors = [];

$nameOK = static function (string $value, bool $required = true): bool {
    if (!$required && $value === '') return true;
    return (bool) preg_match(
        "/\A(?=.{2,50}\z)[\p{L}\p{M}][\p{L}\p{M} .'-]*\z/u",
        $value
    );
};

if (!$nameOK($firstName)) $profileErrors[] = 'Invalid first name.';
if (!$nameOK($lastName)) $profileErrors[] = 'Invalid last name.';
if (!$nameOK($middleName, false)) $profileErrors[] = 'Invalid middle name.';

if (!preg_match(
    "/\A(?=.{2,50}\z)[\p{L}\p{M}][\p{L}\p{M} .'-]*\z/u",
    $city
)) {
    $profileErrors[] = 'Invalid city.';
}

$states = explode(' ',
    'AL AK AZ AR CA CO CT DE FL GA HI ID IL IN IA KS KY LA ME MD MA MI MN MS MO MT NE NV NH NJ NM NY NC ND OH OK OR PA RI SC SD TN TX UT VT VA WA WV WI WY DC'
);

if (!in_array(strtoupper($state), $states, true)) {
    $profileErrors[] = 'Use a valid two-letter US state abbreviation.';
}

if (!preg_match('/\A\d{5}(?:-\d{4})?\z/', $zipCode)) {
    $profileErrors[] = 'Invalid ZIP code.';
}

if (!preg_match(
    "/\A(?=.{5,100}\z)[\p{L}\p{M}\p{N} .,'#\/-]+\z/u",
    $street
)) {
    $profileErrors[] = 'Invalid street address.';
}

$birth = DateTimeImmutable::createFromFormat('!Y-m-d', $dob);
$today = new DateTimeImmutable('today');

if (!$birth || $birth->format('Y-m-d') !== $dob ||
    $birth > $today ||
    $birth < $today->modify('-120 years')) {
    $profileErrors[] = 'Invalid date of birth.';
}

if (!in_array($gender, ['Male', 'Female', 'Other'], true)) {
    $profileErrors[] = 'Invalid gender.';
}

// ASHFORD_FIRST_LAST_EMAIL_V2
$normalizeEmailName = static function (string $name): string {
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);

    return strtolower(preg_replace(
        '/[^a-z]/i',
        '',
        $ascii !== false ? $ascii : $name
    ));
};

$emailBase = $normalizeEmailName($firstName)
    . $normalizeEmailName($lastName);

if ($emailBase === '' ||
    !preg_match(
        '/\A' . preg_quote($emailBase, '/') .
        '(?:[2-9]|[1-9][0-9]+)?@ashford\.edu\z/i',
        $email
    )) {
    $profileErrors[] =
        'University email must match first name + last name, with an optional duplicate number, @ashford.edu.';
}

// A user may keep their own email, but cannot take another user's.
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $duplicateEmail = one(
        $pdo,
        'SELECT user_ID FROM Login WHERE LOWER(user_Email) = LOWER(?) AND user_ID <> ?',
        [$email, $userID]
    );
    if ($duplicateEmail) {
        $profileErrors[] = 'University email is already assigned to another user.';
    }
}


// ASHFORD_CITY_ZIP_STATE_V2
$postalFile = __DIR__ . '/../assets/us_zip_city.json';
$postalData = is_readable($postalFile)
    ? json_decode(file_get_contents($postalFile), true)
    : null;

if (!is_array($postalData)) {
    $profileErrors[] = 'Postal validation is unavailable.';
} elseif (preg_match('/^\d{5}(?:-\d{4})?$/', $zipCode)) {
    $zip5 = substr($zipCode, 0, 5);
    $postal = $postalData[$zip5] ?? null;

    if (!$postal) {
        $profileErrors[] = 'ZIP code is not recognized.';
    } else {
        $normalizeCity = static function (string $value): string {
            return preg_replace(
                '/[^\p{L}\p{N}]/u',
                '',
                mb_strtolower(trim($value), 'UTF-8')
            );
        };

        if (strtoupper($state) !== $postal[0]) {
            $profileErrors[] =
                'ZIP code does not belong to the selected state.';
        } else {
            $cityMatches = false;

            foreach ($postal[1] as $knownCity) {
                if ($normalizeCity($city) === $normalizeCity($knownCity)) {
                    $cityMatches = true;
                    break;
                }
            }

            if (!$cityMatches) {
                $profileErrors[] =
                    'City does not match the selected state and ZIP code.';
            }
        }
    }
}

if ($profileErrors) {
    $_SESSION['users_save_result'] = [
        'type' => 'error',
        'message' => implode(' ', $profileErrors)
    ];
    return;
}

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
    // Student.major_ID is NOT NULL and references Major.major_ID.
    if (!one($pdo, 'SELECT major_ID FROM Major WHERE major_ID = ?', [$majorID])) {
        $_SESSION['users_save_result'] = [
            'type' => 'error',
            'message' => 'Please select a valid primary major.'
        ];
        return;
    }

    // The primary major must also appear in Student_Major.
    $majorIDs[] = $majorID;
    $majorIDs = array_values(array_unique($majorIDs));
    $minorIDs = array_values(array_unique($minorIDs));

    // Foreign keys require every selected major to exist.
    foreach ($majorIDs as $id) {
        if ($id <= 0 || !one($pdo, 'SELECT major_ID FROM Major WHERE major_ID = ?', [$id])) {
            $_SESSION['users_save_result'] = [
                'type' => 'error',
                'message' => 'One or more selected majors are invalid.'
            ];
            return;
        }
    }

    // Foreign keys require every selected minor to exist.
    foreach ($minorIDs as $id) {
        if ($id <= 0 || !one($pdo, 'SELECT minor_ID FROM Minor WHERE minor_ID = ?', [$id])) {
            $_SESSION['users_save_result'] = [
                'type' => 'error',
                'message' => 'One or more selected minors are invalid.'
            ];
            return;
        }
    }
}


// Read saved values to report only actual changes.
$readUserChangeSnapshot = static function (PDO $db, int $id): array {
    $record = one(
        $db,
        "SELECT u.first_Name, u.middle_Name, u.last_Name,
                u.DOB, u.gender, u.street, u.city, u.state,
                u.zip_Code, l.user_Email,
                s.student_Year, s.student_Type, s.major_ID,
                a.security_Level, a.lock_manager
         FROM User u
         JOIN Login l ON l.user_ID = u.user_ID
         LEFT JOIN Student s ON s.student_ID = u.user_ID
         LEFT JOIN Admin a ON a.user_ID = u.user_ID
         WHERE u.user_ID = ?",
        [$id]
    ) ?: [];

    $record['Majors'] = array_column(all_rows(
        $db,
        'SELECT major_ID FROM Student_Major WHERE student_ID = ? ORDER BY major_ID',
        [$id]
    ), 'major_ID');

    $record['Minors'] = array_column(all_rows(
        $db,
        'SELECT minor_ID FROM Student_Minor WHERE student_ID = ? ORDER BY minor_ID',
        [$id]
    ), 'minor_ID');

    return $record;
};

$changeLabels = [
    'first_Name' => 'First Name',
    'middle_Name' => 'Middle Name',
    'last_Name' => 'Last Name',
    'DOB' => 'Date of Birth',
    'gender' => 'Gender',
    'street' => 'Street',
    'city' => 'City',
    'state' => 'State',
    'zip_Code' => 'ZIP Code',
    'user_Email' => 'Email',
    'student_Year' => 'Academic Year',
    'student_Type' => 'Student Type',
    'major_ID' => 'Primary Major',
    'Majors' => 'Majors',
    'Minors' => 'Minor',
    'security_Level' => 'Security Level',
    'lock_manager' => 'Lock Manager',
];

try {

    $pdo->beginTransaction();
    $beforeSave = $readUserChangeSnapshot($pdo, $userID);

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

    $afterSave = $readUserChangeSnapshot($pdo, $userID);
    $changedFields = [];

    foreach ($changeLabels as $field => $label) {
        $before = $beforeSave[$field] ?? null;
        $after = $afterSave[$field] ?? null;

        if (is_array($before) && is_array($after)) {
            if (array_map('strval', $before) !== array_map('strval', $after)) {
                $changedFields[] = $label;
            }
        } elseif ((string) $before !== (string) $after) {
            $changedFields[] = $label;
        }
    }

    $pdo->commit();

    audit_log($pdo, 'Update', 'User', (string) $userID);

    $_SESSION['users_save_result'] = ['type'=>'success','message'=> $changedFields ? 'User updated successfully! Changed: ' . implode(', ', $changedFields) : 'No changes were made.'];

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('User update failed: ' . $e->getMessage());

    $_SESSION['users_save_result'] = ['type'=>'error','message'=>'The user could not be updated. Check for duplicate email, invalid values, or database constraints.'];
}

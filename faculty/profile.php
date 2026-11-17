<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Faculty']);

// Get the logged-in faculty member's ID
$facultyID = (int) $_SESSION['user_id'];


// Get faculty profile information
$sql = "SELECT
            User.first_Name,
            User.middle_Name,
            User.last_Name,
            User.city,
            User.state,
            Login.user_Email,
            Faculty.faculty_Rank,
            Faculty.faculty_Type,
            Faculty.Specialty,
            Faculty.office_Building,
            Faculty.office_Room,
            Faculty.office_Hours,
            Faculty.office_Phone
        FROM User
        JOIN Faculty
            ON Faculty.faculty_ID = User.user_ID
        JOIN Login
            ON Login.user_ID = User.user_ID
        WHERE User.user_ID = ?";

$faculty = one(
    $pdo,
    $sql,
    [$facultyID]
);


page_start(
    'My Profile',
    'Faculty',
    'profile.php'
);

?>

<div class="card">

    <h2>Faculty Information</h2>

    <p>
        <strong>Name:</strong>
        <?= e(
            $faculty['first_Name'] . ' ' .
            $faculty['last_Name']
        ) ?>
    </p>

    <p>
        <strong>Rank:</strong>
        <?= e($faculty['faculty_Rank']) ?>
    </p>

    <p>
        <strong>Faculty Type:</strong>
        <?= e($faculty['faculty_Type']) ?>
    </p>

    <p>
        <strong>Specialty:</strong>
        <?= e($faculty['Specialty']) ?>
    </p>

    <p>
        <strong>Email:</strong>
        <a href="mailto:<?= e($faculty['user_Email']) ?>"><?= e($faculty['user_Email']) ?></a>
    </p>

    <p>
        <strong>Rank:</strong>
        <?= e($faculty['faculty_Rank']) ?>
    </p>

    <p>
        <strong>Faculty Type:</strong>
        <?= e($faculty['faculty_Type']) ?>
    </p>

    <p>
        <strong>Specialty:</strong>
        <?= e($faculty['Specialty']) ?>
    </p>

    <p>
        <strong>Location:</strong>
        <?= e(
            $faculty['city'] . ', ' .
            $faculty['state']
        ) ?>
    </p>

    <h3>Office Information</h3>

    <p>
        <strong>Building:</strong>
        <?= e($faculty['office_Building'] ?? 'Not assigned') ?>
    </p>

    <p>
        <strong>Room:</strong>
        <?= e($faculty['office_Room'] ?? 'Not assigned') ?>
    </p>

    <p>
        <strong>Office Hours:</strong>
        <?= e($faculty['office_Hours'] ?? 'Not posted') ?>
    </p>

    <p>
        <strong>Office Phone:</strong>
        <?= e($faculty['office_Phone'] ?? 'Not listed') ?>
    </p>

</div>

<?php /* ASHFORD_ALL_ENHANCEMENTS_V2 */ ?>
<link rel="stylesheet" href="<?= e(url('assets/ashford-enhancements/enhancements.css')) ?>">
<script defer src="<?= e(url('assets/ashford-enhancements/enhancements.js')) ?>"></script>

<?php

page_end();

?>
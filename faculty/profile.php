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
            Faculty.faculty_Rank,
            Faculty.faculty_Type,
            Faculty.Specialty
        FROM User
        JOIN Faculty
            ON Faculty.faculty_ID = User.user_ID
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
        <strong>Location:</strong>
        <?= e(
            $faculty['city'] . ', ' .
            $faculty['state']
        ) ?>
    </p>

</div>

<?php

page_end();

?>
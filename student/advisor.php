<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


// Get the student's assigned advisor
$sql = "SELECT
            User.first_Name,
            User.last_Name,
            Faculty.faculty_Rank,
            Faculty.Specialty,
            Advisor.date_Of_Appnt
        FROM Advisor
        JOIN User
            ON User.user_ID = Advisor.faculty_ID
        JOIN Faculty
            ON Faculty.faculty_ID = Advisor.faculty_ID
        WHERE Advisor.student_ID = ?
        LIMIT 1";

$advisor = one(
    $pdo,
    $sql,
    [$studentID]
);


page_start(
    'My Advisor',
    'Student',
    'advisor.php'
);

?>

<div class="card">

    <h2>Advisor Information</h2>

    <?php if ($advisor): ?>

        <p>
            <strong>Name:</strong>
            <?= e(
                $advisor['faculty_Rank'] . ' ' .
                $advisor['first_Name'] . ' ' .
                $advisor['last_Name']
            ) ?>
        </p>

        <p>
            <strong>Specialty:</strong>
            <?= e($advisor['Specialty']) ?>
        </p>

        <p>
            <strong>Appointment Date:</strong>
            <?= e($advisor['date_Of_Appnt']) ?>
        </p>

    <?php else: ?>

        <p>
            No advisor has been assigned.
        </p>

    <?php endif; ?>

</div>

<?php

page_end();

?>
<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Faculty']);

// Get the ID of the faculty member who is logged in
$facultyID = (int) $_SESSION['user_id'];


// Get all students assigned to this faculty member
$sql = "SELECT
            Advisor.student_ID,
            User.first_Name,
            User.last_Name,
            Student.student_Type,
            Student.student_Year,
            Advisor.date_Of_Appnt
        FROM Advisor
        JOIN User
            ON User.user_ID = Advisor.student_ID
        JOIN Student
            ON Student.student_ID = Advisor.student_ID
        WHERE Advisor.faculty_ID = ?
        ORDER BY User.last_Name, User.first_Name";

$advisees = all_rows(
    $pdo,
    $sql,
    [$facultyID]
);


page_start(
    'Advisees',
    'Faculty',
    'advisees.php'
);

?>

<div class="table-wrap">

    <table>

        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Level</th>
            <th>Year</th>
            <th>Appointment Date</th>
        </tr>

        <?php foreach ($advisees as $student): ?>

            <tr>

                <td>
                    <?= e($student['student_ID']) ?>
                </td>

                <td>
                    <?= e(
                        $student['first_Name'] . ' ' .
                        $student['last_Name']
                    ) ?>
                </td>

                <td>
                    <?= e($student['student_Type']) ?>
                </td>

                <td>
                    <?= e($student['student_Year']) ?>
                </td>

                <td>
                    <?= e($student['date_Of_Appnt']) ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>

<?php

page_end();

?>
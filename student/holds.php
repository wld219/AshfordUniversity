<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


// Get all holds for the student
$sql = "SELECT
            Hold.hold_Type,
            Student_Hold.hold_Date
        FROM Student_Hold
        JOIN Hold
            ON Hold.hold_ID = Student_Hold.hold_ID
        WHERE Student_Hold.student_ID = ?
        ORDER BY Student_Hold.hold_Date";

$holds = all_rows(
    $pdo,
    $sql,
    [$studentID]
);


page_start(
    'Holds',
    'Student',
    'holds.php'
);

?>

<p class="muted">
    Holds must be resolved before registering for courses.
</p>


<?php if (count($holds) === 0): ?>

    <div class="alert success">

        <strong>No Holds</strong>

        <p>
            Your account is clear for registration.
        </p>

    </div>

<?php else: ?>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Hold</th>
                <th>Date</th>
            </tr>


            <?php foreach ($holds as $hold): ?>

                <tr>

                    <td>
                        <?= e($hold['hold_Type']) ?>
                    </td>

                    <td>
                        <?= e($hold['hold_Date']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

<?php endif; ?>


<?php

page_end();

?>
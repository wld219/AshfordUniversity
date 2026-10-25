<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Faculty']);

// Get all courses and their departments
$sql = "SELECT
            Course.course_ID,
            Course.course_Name,
            Course.course_Credits,
            Department.dept_Name
        FROM Course
        JOIN Department
            ON Department.dept_ID = Course.dept_ID
        ORDER BY
            Department.dept_Name,
            Course.course_ID";

$courses = all_rows($pdo, $sql);


page_start(
    'Catalog',
    'Faculty',
    'catalog.php'
);

?>

<div class="table-wrap">

    <table>

        <tr>
            <th>Course</th>
            <th>Title</th>
            <th>Department</th>
            <th>Credits</th>
        </tr>

        <?php foreach ($courses as $course): ?>

            <tr>

                <td>
                    <?= e($course['course_ID']) ?>
                </td>

                <td>
                    <?= e($course['course_Name']) ?>
                </td>

                <td>
                    <?= e($course['dept_Name']) ?>
                </td>

                <td>
                    <?= e($course['course_Credits']) ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>

<?php

page_end();

?>
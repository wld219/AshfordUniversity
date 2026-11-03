<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Faculty']);

// Get the logged-in faculty member's ID
$facultyID = (int) $_SESSION['user_id'];


// Get all course sections assigned to this faculty member
$sql = "SELECT
            Course_Section.CRN,
            Course.course_ID,
            Course.course_Name,
            Course_Section.section_No,
            Semester.semester_Name,
            Course_Section.available_Seats
        FROM Course_Section
        JOIN Course
            ON Course.course_ID = Course_Section.course_ID
        JOIN Semester
            ON Semester.semester_ID = Course_Section.semester_ID
        WHERE Course_Section.faculty_ID = ?
        ORDER BY
            Semester.semester_ID,
            Course.course_ID";

$sections = all_rows(
    $pdo,
    $sql,
    [$facultyID]
);


page_start(
    'My Schedule',
    'Faculty',
    'schedule.php'
);

?>

<?php if (count($sections) > 0): ?>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Course</th>
                <th>Title</th>
                <th>Section</th>
                <th>CRN</th>
                <th>Semester</th>
                <th>Available Seats</th>
            </tr>

            <?php foreach ($sections as $section): ?>

                <tr>

                    <td>
                        <?= e($section['course_ID']) ?>
                    </td>

                    <td>
                        <?= e($section['course_Name']) ?>
                    </td>

                    <td>
                        <?= e($section['section_No']) ?>
                    </td>

                    <td>
                        <?= e($section['CRN']) ?>
                    </td>

                    <td>
                        <?= e($section['semester_Name']) ?>
                    </td>

                    <td>
                        <?= e($section['available_Seats']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

<?php else: ?>

    <div class="card">
        <p>No course sections are currently assigned to you.</p>
    </div>

<?php endif; ?>


<?php

page_end();

?>
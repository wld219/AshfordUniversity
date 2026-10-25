<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Faculty']);


// Get the logged-in faculty member
$facultyID = (int) $_SESSION['user_id'];


// Get all sections taught by this faculty member
$sql = "SELECT
            Course_Section.CRN,
            Course.course_ID,
            Course.course_Name,
            Semester.semester_Name
        FROM Course_Section

        JOIN Course
            ON Course.course_ID = Course_Section.course_ID

        JOIN Semester
            ON Semester.semester_ID = Course_Section.semester_ID

        WHERE Course_Section.faculty_ID = ?

        ORDER BY
            Semester.semester_ID DESC,
            Course.course_ID";

$sections = all_rows(
    $pdo,
    $sql,
    [$facultyID]
);


// Choose which course to display
if (isset($_GET['crn'])) {

    $crn = (int) $_GET['crn'];

} else {

    $crn = 0;

    // Default to Fall 2026
    foreach ($sections as $section) {

        if ($section['semester_Name'] === 'Fall 2026') {

            $crn = (int) $section['CRN'];
            break;
        }
    }


    // If there is no Fall 2026 section,
    // use the first available section
    if ($crn === 0 && !empty($sections)) {

        $crn = (int) $sections[0]['CRN'];
    }
}


// Get attendance history for the selected course
if ($crn > 0) {

    $sql = "SELECT
                Attendance.attendance_Date,
                Attendance.student_ID,
                User.first_Name,
                User.last_Name,
                Attendance.Present_Absent
            FROM Attendance

            JOIN User
                ON User.user_ID = Attendance.student_ID

            JOIN Course_Section
                ON Course_Section.CRN = Attendance.CRN

            WHERE Attendance.CRN = ?
            AND Course_Section.faculty_ID = ?

            ORDER BY
                Attendance.attendance_Date DESC,
                User.last_Name,
                User.first_Name";

    $attendanceRecords = all_rows(
        $pdo,
        $sql,
        [$crn, $facultyID]
    );

} else {

    $attendanceRecords = [];
}


// Start the page
page_start(
    'Attendance History',
    'Faculty',
    'attendance-history.php'
);

?>


<div class="card">

    <form method="get">

        <label for="crn">
            <strong>Select Course</strong>
        </label>

        <select
            name="crn"
            id="crn"
            onchange="this.form.submit()"
        >

            <?php foreach ($sections as $section): ?>

                <option
                    value="<?= e($section['CRN']) ?>"
                    <?= $crn === (int) $section['CRN']
                        ? 'selected'
                        : '' ?>
                >

                    <?= e($section['course_ID']) ?>
                    -
                    <?= e($section['course_Name']) ?>
                    -
                    <?= e($section['semester_Name']) ?>
                    (CRN <?= e($section['CRN']) ?>)

                </option>

            <?php endforeach; ?>

        </select>

    </form>

</div>


<?php if (count($attendanceRecords) > 0): ?>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Date</th>
                <th>Student ID</th>
                <th>Student</th>
                <th>Attendance</th>
            </tr>


            <?php foreach ($attendanceRecords as $record): ?>

                <tr>

                    <td>
                        <?= e($record['attendance_Date']) ?>
                    </td>

                    <td>
                        <?= e($record['student_ID']) ?>
                    </td>

                    <td>
                        <?= e(
                            $record['first_Name'] . ' ' .
                            $record['last_Name']
                        ) ?>
                    </td>

                    <td>
                        <?= e($record['Present_Absent']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>


<?php else: ?>

    <div class="card">

        <p>
            No attendance records are available for this course.
        </p>

    </div>

<?php endif; ?>


<?php

page_end();

?>
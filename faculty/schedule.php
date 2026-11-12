<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

// Get the logged-in faculty member's ID
$facultyID = (int) $_SESSION['user_id'];

// Semester filter (GET)
$filterSemester = get_int('semester');
$filterSemester = $filterSemester > 0 ? $filterSemester : null;

// Available semesters for the filter dropdown
$semesters = all_rows(
    $pdo,
    "SELECT DISTINCT Semester.semester_ID, Semester.semester_Name
        FROM Semester
        JOIN Course_Section ON Course_Section.semester_ID = Semester.semester_ID
        WHERE Course_Section.faculty_ID = ?
        ORDER BY
            RIGHT(Semester.semester_Name, 4) DESC,
            CASE
                WHEN Semester.semester_Name LIKE 'Fall %' THEN 2
                WHEN Semester.semester_Name LIKE 'Spring %' THEN 1
                ELSE 0
            END DESC",
    [$facultyID]
);

// Build WHERE clause for optional semester filter
$where = "WHERE Course_Section.faculty_ID = ?";
$params = [$facultyID];

if ($filterSemester !== null) {
    $where .= " AND Course_Section.semester_ID = ?";
    $params[] = $filterSemester;
}

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
        $where
        ORDER BY
            RIGHT(Semester.semester_Name, 4) DESC,
            CASE
                WHEN Semester.semester_Name LIKE 'Fall %' THEN 2
                WHEN Semester.semester_Name LIKE 'Spring %' THEN 1
                ELSE 0
            END DESC,
            Course.course_ID";

$sections = all_rows($pdo, $sql, $params);


page_start(
    'My Schedule',
    'Faculty',
    'schedule.php'
);

?>

<?php if (count($sections) > 0): ?>

    <div class="card">

        <div class="dashboard-table-header">
            <div>
                <h2>My Schedule</h2>
                <p class="muted"><?= count($sections) ?> section(s) scheduled.</p>
            </div>

            <form method="get" class="filters">
                <div>
                    <label for="semester">Semester</label>
                    <select name="semester" id="semester">
                        <option value="">All semesters</option>
                        <?php foreach ($semesters as $sem): ?>
                            <option
                                value="<?= e($sem['semester_ID']) ?>"
                                <?= ($filterSemester === (int) $sem['semester_ID']) ? 'selected' : '' ?>
                            >
                                <?= e($sem['semester_Name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="align-self: end;">
                    <button type="submit" class="btn">Filter</button>
                    <a class="btn light" href="schedule.php">Clear</a>
                </div>
            </form>
        </div>

        <div class="table-wrap">

            <table>

                <tr>
                    <th>CRN</th>
                    <th>Course</th>
                    <th>Section</th>
                    <th>Title</th>
                    <th>Semester</th>
                    <th>Available Seats</th>
                </tr>

                <?php foreach ($sections as $section): ?>

                    <tr>
                        <td>
                            <?= e($section['CRN']) ?>
                        </td>

                        <td>
                            <?= e($section['course_ID']) ?>
                        </td>

                        <td>
                            <?= e($section['section_No']) ?>
                        </td>

                        <td>
                            <?= e($section['course_Name']) ?>
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

    </div>

<?php else: ?>

    <div class="card">
        <p>No course sections are currently assigned to you.</p>
    </div>

<?php endif; ?>


<?php

page_end();

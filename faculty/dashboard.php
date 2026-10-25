<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Faculty']);

// Get the ID of the faculty member who is logged in
$facultyID = (int) $_SESSION['user_id'];


// Get faculty information
$sql = "SELECT
            Faculty.*,
            User.first_Name,
            User.last_Name
        FROM Faculty
        JOIN User
            ON User.user_ID = Faculty.faculty_ID
        WHERE Faculty.faculty_ID = ?";

$faculty = one(
    $pdo,
    $sql,
    [$facultyID]
);


// Get the faculty member's course sections
$sql = "SELECT
            Course_Section.CRN,
            Course.course_ID,
            Course.course_Name,
            Course_Section.section_No,
            Course_Section.available_Seats,
            Semester.semester_Name
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


// Count the faculty member's advisees
$sql = "SELECT COUNT(*) AS total
        FROM Advisor
        WHERE faculty_ID = ?";

$adviseeCount = one(
    $pdo,
    $sql,
    [$facultyID]
);


page_start(
    'Welcome, ' . ($_SESSION['name'] ?? 'Faculty'),
    'Faculty',
    'dashboard.php'
);

?>

<div class="dashboard-page">

    <section class="dashboard-hero">
        <div>
            <p class="dashboard-eyebrow">Faculty overview</p>
            <h2><?= e($faculty['faculty_Rank'] ?? 'Faculty') ?></h2>
            <p><?= e($faculty['Specialty'] ?? $faculty['faculty_Type'] ?? 'Faculty profile') ?></p>
        </div>
        <span class="dashboard-badge"><?= e($faculty['faculty_Type'] ?? 'Faculty') ?></span>
    </section>

    <section class="dashboard-stats" aria-label="Faculty summary">
        <article class="dashboard-stat card">
            <span class="dashboard-stat-label">Assigned sections</span>
            <strong><?= count($sections) ?></strong>
            <span class="dashboard-stat-note">Current assignments</span>
        </article>

		<article class="dashboard-stat card">
			<span class="dashboard-stat-label">Advisees</span>
			<strong><?= e($adviseeCount['total'] ?? 0) ?></strong>
			<span class="dashboard-stat-note">Active students</span>
		</article>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section-heading">
            <div>
                <p class="dashboard-eyebrow">Teaching</p>
                <h2>My sections</h2>
            </div>
            <span class="dashboard-count"><?= count($sections) ?> assigned</span>
        </div>

<?php if (count($sections) > 0): ?>

    <div class="grid2">

        <?php foreach ($sections as $section): ?>

            <div class="card">

                <h3>
                    <?= e($section['course_ID']) ?>
                    -
                    <?= e($section['section_No']) ?>
                </h3>

                <p>
                    <?= e($section['course_Name']) ?>
                </p>

                <p>
                    Semester:
                    <?= e($section['semester_Name']) ?>
                </p>

                <p>
                    Available Seats:
                    <?= e($section['available_Seats']) ?>
                </p>

            </div>

        <?php endforeach; ?>

    </div>

<?php else: ?>

    <div class="card">
        <p>No course sections are currently assigned.</p>
    </div>

<?php endif; ?>

    </section>

</div>

<?php

page_end();

?>
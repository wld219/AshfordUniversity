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
          AND Semester.semester_Name = 'Fall 2026'
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


<style>
.apple-overview {
    max-width: 1180px;
    margin: 0 auto;
    padding: 12px 0 40px;
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", sans-serif;
}
.apple-overview .welcome {
    background: linear-gradient(135deg, #f5f7fb, #eaf0f8);
    border-radius: 26px;
    padding: 32px;
    margin-bottom: 22px;
}
.apple-overview .eyebrow {
    color: #667085;
    font-size: 13px;
    font-weight: 600;
}
.apple-overview h2 {
    font-size: 28px;
    letter-spacing: -.7px;
    margin: 8px 0;
    color: #172235;
}
.apple-overview .muted {
    color: #667085;
    font-size: 14px;
}
.apple-overview .stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 22px;
}
.apple-overview .tile,
.apple-overview .panel {
    background: #fff;
    border: 1px solid #edf0f5;
    border-radius: 24px;
    padding: 24px;
    box-shadow: 0 5px 22px rgba(25, 40, 70, .045);
}
.apple-overview .tile {
    transition: transform .2s ease, box-shadow .2s ease;
}
.apple-overview .tile:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(25, 40, 70, .09);
}
.apple-overview .tile strong {
    display: block;
    font-size: 34px;
    margin-top: 12px;
    color: #172235;
}
.apple-overview .panel {
    margin-bottom: 20px;
}
.apple-overview .panel-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}
.apple-overview .panel-heading h2 {
    font-size: 21px;
    margin: 0;
}
.apple-overview .course {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #eef0f4;
}
.apple-overview .course:last-child {
    border-bottom: none;
}
.apple-overview .course-title {
    font-weight: 650;
    color: #172235;
}
.apple-overview .course-meta {
    font-size: 13px;
    color: #667085;
    margin-top: 5px;
}
.apple-overview .pill {
    display: inline-block;
    background: #f0f4fa;
    color: #43546d;
    border-radius: 999px;
    padding: 8px 12px;
    font-size: 12px;
    white-space: nowrap;
}
.apple-overview .actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.apple-overview .action {
    display: inline-block;
    text-decoration: none;
    color: #1b3e6b;
    background: #f1f5fb;
    border-radius: 999px;
    padding: 12px 18px;
    font-size: 14px;
    font-weight: 600;
    transition: background .2s ease, transform .2s ease;
}
.apple-overview .action:hover {
    background: #e4ecf8;
    transform: translateY(-2px);
}
@media (max-width: 700px) {
    .apple-overview .stats {
        grid-template-columns: 1fr;
    }
    .apple-overview .welcome {
        padding: 24px;
    }
    .apple-overview h2 {
        font-size: 24px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .apple-overview .tile,
    .apple-overview .action {
        transition: none;
    }
}
</style>

<main class="apple-overview">
    <section class="welcome">
        <div class="eyebrow">FACULTY OVERVIEW</div>
        <h2>Welcome back, <?= e($faculty['first_Name'] ?? 'Faculty') ?></h2>
        <div class="muted">
            <?= e($faculty['faculty_Rank'] ?? 'Faculty') ?>
            · <?= e($faculty['Specialty'] ?? $faculty['faculty_Type'] ?? 'Ashford University') ?>
        </div>
    </section>

    <section class="stats">
        <article class="tile">
            <div class="muted">Assigned Sections</div>
            <strong><?= count($sections) ?></strong>
        </article>
        <article class="tile">
            <div class="muted">Total Advisees</div>
            <strong><?= (int)($adviseeCount['total'] ?? 0) ?></strong>
        </article>
        <article class="tile">
            <div class="muted">Course Offerings</div>
            <strong><?= count(array_unique(array_column($sections, 'course_ID'))) ?></strong>
        </article>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>My Courses</h2>
            <span class="pill"><?= count($sections) ?> sections</span>
        </div>

        <?php if ($sections): ?>
            <?php foreach ($sections as $section): ?>
                <div class="course">
                    <div>
                        <div class="course-title">
                            <?= e($section['course_ID']) ?>
                            — <?= e($section['course_Name']) ?>
                        </div>
                        <div class="course-meta">
                            Section <?= e($section['section_No']) ?>
                            · CRN <?= e($section['CRN']) ?>
                            · <?= e($section['semester_Name']) ?>
                        </div>
                    </div>
                    <span class="pill">
                        <?= e($section['available_Seats']) ?> seats available
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="muted">No assigned course sections.</p>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>Quick Actions</h2>
        </div>
        <div class="actions">
            <a class="action" href="<?= e(url('faculty/advisees.php')) ?>">Advisees</a>
            <a class="action" href="<?= e(url('faculty/rosters.php')) ?>">Rosters</a>
            <a class="action" href="<?= e(url('faculty/attendance-calendar.php')) ?>">Attendance</a>
            <a class="action" href="<?= e(url('faculty/master-schedule.php')) ?>">Master Schedule</a>
        </div>
    </section>
</main>

<?php /* ASHFORD_ALL_ENHANCEMENTS_V2 */ ?>
<link rel="stylesheet" href="<?= e(url('assets/ashford-enhancements/enhancements.css')) ?>">
<script defer src="<?= e(url('assets/ashford-enhancements/enhancements.js')) ?>"></script>

<?php

page_end();

?>
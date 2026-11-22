<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_once '../includes/functions/faculty_teaching_load.php';

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


// Keep the dashboard and roster term options aligned even when a faculty
// member has no active section in one of the two demonstration semesters.
$semesterOptions = all_rows(
    $pdo,
    "SELECT semester_ID, semester_Name
     FROM Semester
     WHERE semester_Name IN ('Fall 2026', 'Spring 2027')
     ORDER BY CASE semester_Name WHEN 'Fall 2026' THEN 1 WHEN 'Spring 2027' THEN 2 ELSE 3 END, semester_ID"
);
$semesterOptionsById = [];
$fallSemesterId = null;
foreach ($semesterOptions as $option) {
    $optionId = (int) $option['semester_ID'];
    $semesterOptionsById[$optionId] = $option;
    if ($option['semester_Name'] === 'Fall 2026') {
        $fallSemesterId = $optionId;
    }
}

$defaultSemesterId = $fallSemesterId ?? (int) (array_key_first($semesterOptionsById) ?? 0);
$requestedSemesterId = filter_var($_GET['semester'] ?? null, FILTER_VALIDATE_INT);
$selectedSemesterId = $requestedSemesterId !== false
    && $requestedSemesterId !== null
    && isset($semesterOptionsById[$requestedSemesterId])
        ? $requestedSemesterId
        : $defaultSemesterId;
$selectedSemesterName = $semesterOptionsById[$selectedSemesterId]['semester_Name'] ?? 'Fall 2026';

$departments = faculty_departments($pdo, $facultyID);
$departmentNames = array_column($departments, 'dept_Name');
$departmentCourseGroups = faculty_department_course_groups($pdo, $facultyID);
$sections = faculty_teaching_sections($pdo, $facultyID, $selectedSemesterId);
$workload = faculty_teaching_workload_summary($sections);


// Count distinct students with active enrollments in this faculty member's
// selected-term sections. Enrollment and section semesters must agree.
$sql = "SELECT COUNT(DISTINCT e.student_ID) AS total
        FROM Course_Section cs
        JOIN Enrollment e
          ON e.CRN = cs.CRN
         AND e.semester_ID = cs.semester_ID
        WHERE cs.faculty_ID = ?
          AND cs.semester_ID = ?
          AND cs.is_Cancelled = 0";

$enrolledStudentCount = one(
    $pdo,
    $sql,
    [$facultyID, $selectedSemesterId]
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
.apple-overview .semester-control {
    display: flex;
    align-items: end;
    gap: 8px;
    margin: 0;
}
.apple-overview .semester-control label {
    color: #43546d;
    font-size: 13px;
    font-weight: 650;
}
.apple-overview .semester-control select {
    min-height: 40px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    color: #172235;
    padding: 8px 34px 8px 11px;
    font: inherit;
}
.apple-overview .semester-control button {
    min-height: 40px;
    border: 0;
    border-radius: 10px;
    background: #1b3e6b;
    color: #fff;
    padding: 8px 14px;
    font: inherit;
    font-weight: 650;
    cursor: pointer;
}
.apple-overview .semester-control button:focus-visible,
.apple-overview .semester-control select:focus-visible {
    outline: 3px solid #f0b429;
    outline-offset: 2px;
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
    .apple-overview .panel-heading {
        align-items: flex-start;
        flex-wrap: wrap;
    }
    .apple-overview .semester-control {
        flex-wrap: wrap;
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
            <br>Faculty Department<?= count($departmentNames) === 1 ? '' : 's' ?>:
            <?= e(implode(', ', $departmentNames) ?: 'Not assigned') ?>
        </div>
    </section>

    <section class="stats">
        <article class="tile">
            <div class="muted">Assigned Course Sections · <?= e($selectedSemesterName) ?></div>
            <strong><?= e($workload['assigned_sections']) ?></strong>
        </article>
        <article class="tile">
            <div class="muted">Total Teaching Credits · <?= e($selectedSemesterName) ?></div>
            <strong><?= e($workload['teaching_credits']) ?></strong>
        </article>
        <article class="tile">
            <div class="muted">Calculated Teaching Workload</div>
            <strong><?= e($workload['status']) ?></strong>
        </article>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>My Teaching Courses · <?= e($selectedSemesterName) ?></h2>
                <p class="muted" style="margin:5px 0 0">
                    <?= e($workload['course_count']) ?> courses · <?= e($workload['assigned_sections']) ?> assigned sections ·
                    <?= (int) ($enrolledStudentCount['total'] ?? 0) ?> enrolled students
                </p>
            </div>
            <form class="semester-control" method="get" action="dashboard.php">
                <label for="dashboard-semester">Semester</label>
                <select id="dashboard-semester" name="semester">
                    <?php foreach ($semesterOptions as $option): ?>
                        <option value="<?= (int)$option['semester_ID'] ?>" <?= (int)$option['semester_ID'] === $selectedSemesterId ? 'selected' : '' ?>>
                            <?= e($option['semester_Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">View</button>
            </form>
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
                            · <?= e($section['course_Credits']) ?> credits
                            · <?= e($section['semester_Name']) ?>
                            <br><?= e($section['meeting_Schedule']) ?>
                            · <?= e($section['course_Department']) ?>
                        </div>
                    </div>
                    <span class="pill">
                        <?= e($section['available_Seats']) ?> seats available
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="muted">No active course sections are assigned to you for <?= e($selectedSemesterName) ?>.</p>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>Department Courses</h2>
            <span class="pill"><?= count($departmentCourseGroups) ?> departments</span>
        </div>
        <?php if ($departmentCourseGroups): ?>
            <?php foreach ($departmentCourseGroups as $departmentGroup): ?>
                <details class="department-course-group">
                    <summary>
                        <?= e($departmentGroup['dept_Name']) ?>
                        (<?= count($departmentGroup['courses']) ?> courses)
                    </summary>
                    <?php if ($departmentGroup['courses']): ?>
                        <div class="department-course-list">
                            <?php foreach ($departmentGroup['courses'] as $departmentCourse): ?>
                                <div class="department-course-row">
                                    <span><strong><?= e($departmentCourse['course_ID']) ?></strong> — <?= e($departmentCourse['course_Name']) ?></span>
                                    <span><?= e($departmentCourse['course_Credits']) ?> credits</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="muted">No catalog courses are mapped to this department.</p>
                    <?php endif; ?>
                </details>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="muted">No faculty department assignment is recorded.</p>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>Quick Actions</h2>
        </div>
        <div class="actions">
            <a class="action" href="<?= e(url('faculty/advisees.php')) ?>">Advisees</a>
            <a class="action" href="<?= e(url('faculty/rosters.php?term=' . $selectedSemesterId)) ?>">Rosters · <?= e($selectedSemesterName) ?></a>
            <a class="action" href="<?= e(url('faculty/attendance-calendar.php')) ?>">Attendance</a>
            <a class="action" href="<?= e(url('faculty/master-schedule.php')) ?>">Master Schedule</a>
        </div>
    </section>
</main>

<style>
.apple-overview .department-course-group {
    border-top: 1px solid #eef0f4;
    padding: 12px 0;
}
.apple-overview .department-course-group summary {
    color: #1b3e6b;
    cursor: pointer;
    font-weight: 650;
}
.apple-overview .department-course-group summary:focus-visible {
    outline: 3px solid #f0b429;
    outline-offset: 3px;
}
.apple-overview .department-course-list {
    margin-top: 8px;
}
.apple-overview .department-course-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #eef0f4;
    color: #43546d;
    font-size: 14px;
}
@media (max-width: 700px) {
    .apple-overview .department-course-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 4px;
    }
}
</style>

<?php /* ASHFORD_ALL_ENHANCEMENTS_V2 */ ?>
<link rel="stylesheet" href="<?= e(url('assets/ashford-enhancements/enhancements.css')) ?>">
<script defer src="<?= e(url('assets/ashford-enhancements/enhancements.js')) ?>"></script>

<?php

page_end();

?>

<?php
declare(strict_types=1);
// Student dashboard visual redesign. Uses existing controller and data variables.
$displayName = trim(($student['first_Name'] ?? '') . ' ' . ($student['last_Name'] ?? ''));
$displayName = $displayName !== '' ? $displayName : 'Student';
$courseDetails = [];
try {
    if (!empty($currentSemester)) {
        $rows = all_rows($pdo, "SELECT cs.CRN, cs.course_ID, cs.section_No, cs.time_Slot_ID,
                    CONCAT(u.first_Name, ' ', u.last_Name) AS faculty_Name,
                    d.dept_Name
                FROM Course_Section cs
                JOIN Course c ON c.course_ID = cs.course_ID
                JOIN Department d ON d.dept_ID = c.dept_ID
                LEFT JOIN Faculty f ON f.faculty_ID = cs.faculty_ID
                LEFT JOIN User u ON u.user_ID = f.faculty_ID
                WHERE cs.semester_ID = ?", [(int)$currentSemester]);
        foreach ($rows as $r) { $courseDetails[(int)$r['CRN']] = $r; }
    }
} catch (Throwable $ex) { error_log('Dashboard section details: ' . $ex->getMessage()); }
$deptCode = static function(string $name): string {
    $map = ['Computer Science'=>'CS','Mathematics'=>'MATH','Business'=>'BUS','Natural Sciences'=>'SCI',
            'Engineering'=>'ENGR','English'=>'ENG','Social Sciences'=>'SOC','Education'=>'EDU',
            'Health Sciences'=>'HLTH','Arts and Media'=>'ART'];
    return $map[$name] ?? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name),0,4));
};
?>


<style>
/* ASHFORD_STUDENT_OVERVIEW_OPTION2_V1 */

.ash-student {
    --ash-navy: #10244a;
    --ash-border: #e1e6f0;
    color: #142345;
    font-size: 15px;
}

.ash-student * {
    box-sizing: border-box;
}

.ash-student .ash-head {
    margin-bottom: 22px;
}

.ash-student .ash-head h2 {
    font-size: 30px;
    line-height: 1.25;
    margin: 0 0 6px;
    font-weight: 750;
    color: var(--ash-navy);
}

.ash-student .ash-sub {
    color: #566581;
    margin: 0;
}

.ash-student .ash-card {
    background: #fff;
    border: 1px solid var(--ash-border);
    border-radius: 13px;
    padding: 22px;
    box-shadow: 0 4px 16px rgba(16,36,74,.045);
    min-width: 0;
}

.ash-student .ash-card h3 {
    font-size: 19px;
    color: var(--ash-navy);
    margin: 0 0 18px;
}

.ash-student .ash-panel {
    margin-bottom: 18px;
}

.ash-student .ash-table-wrap {
    overflow-x: auto;
}

.ash-student table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    min-width: 640px;
}

.ash-student th {
    background: #edf1f8;
    color: #18254c;
    text-align: left;
    font-weight: 700;
    padding: 13px 11px;
    white-space: nowrap;
}

.ash-student td {
    border-bottom: 1px solid #e8eaf1;
    padding: 14px 11px;
    vertical-align: top;
    color: #253451;
}

.ash-student .ash-course-code {
    font-weight: 750;
    color: #142c6a;
    white-space: nowrap;
}

.ash-student .ash-note {
    font-size: 14px;
    color: #63718d;
    line-height: 1.6;
}

.ash-student .ash-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    align-items: stretch;
}

.ash-student .ash-info-card {
    margin-bottom: 0;
}

.ash-student .ash-info-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 14px;
    padding: 12px 0;
    border-bottom: 1px solid #edf0f6;
}

.ash-student .ash-info-row:last-child {
    border-bottom: 0;
}

.ash-student .ash-info-label {
    color: #63718d;
    font-size: 14px;
}

.ash-student .ash-info-value {
    color: var(--ash-navy);
    font-size: 15px;
    font-weight: 700;
    text-align: right;
    overflow-wrap: anywhere;
}

@media (max-width: 850px) {
    .ash-student .ash-info-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 550px) {
    .ash-student .ash-card {
        padding: 16px;
    }

    .ash-student .ash-head h2 {
        font-size: 25px;
    }
}
</style>



<style>
/* ASHFORD_OVERVIEW_REFINEMENTS_V2 */

.ash-student .ash-head {
    margin-bottom: 14px !important;
}

.ash-student .ash-head h2 {
    margin-bottom: 4px !important;
}

.ash-student form[action="dashboard.php"] {
    margin: 0 0 14px !important;
}

.ash-student form[action="dashboard.php"] label {
    display: block;
    margin-bottom: 6px;
}

.ash-student form[action="dashboard.php"] select {
    width: 100%;
    min-height: 39px;
    margin-bottom: 10px;
}

.ash-student .ash-semester-confirm {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 8px 22px;
    border: 0;
    border-radius: 8px;
    background: #39318d;
    color: white;
    font-weight: 700;
    cursor: pointer;
}

.ash-student .ash-semester-confirm:hover {
    background: #2c2577;
}

.ash-student .ash-card {
    padding: 18px !important;
}

.ash-student .ash-card h3 {
    margin-bottom: 14px !important;
}

.ash-student .ash-panel {
    margin-bottom: 14px !important;
}

.ash-student .ash-info-grid {
    gap: 14px !important;
}

.ash-student .ash-info-row {
    padding: 9px 0 !important;
}
</style>


<style>
/* ASHFORD_SEMESTER_BUBBLE_V3 */

/* More compact welcome area */
.ash-student .ash-head {
    margin-bottom: 16px !important;
}

/* Semester selector layout */
.ash-student form[action="dashboard.php"] {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    margin: 0 0 20px !important;
}

/* Label above the controls */
.ash-student form[action="dashboard.php"] label {
    flex: 0 0 100%;
    margin: 0 !important;
    font-size: 14px;
    font-weight: 700;
    color: #243456;
}

/* Rounded bubble dropdown */
.ash-student form[action="dashboard.php"] select {
    flex: 1 1 260px;
    width: auto !important;
    min-width: 0;
    min-height: 48px;
    margin: 0 !important;
    padding: 11px 18px !important;
    border: 1px solid #d9d8ef !important;
    border-radius: 24px !important;
    background-color: #fff;
    color: #172a52;
    font-size: 15px;
    font-weight: 650;
    cursor: pointer;
}

/* Rounded Confirm button */
.ash-student .ash-semester-confirm {
    flex: 0 0 auto;
    min-height: 48px;
    padding: 11px 26px;
    border-radius: 24px;
    background: #42349a;
    color: white;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
}

/* Keyboard focus */
.ash-student form[action="dashboard.php"] select:focus-visible,
.ash-student .ash-semester-confirm:focus-visible {
    outline: 3px solid #a99bf0;
    outline-offset: 2px;
}

/* Tighten spacing around course section */
.ash-student .ash-panel {
    margin-bottom: 16px !important;
}

.ash-student .ash-info-grid {
    gap: 16px !important;
}

@media (max-width: 520px) {
    .ash-student form[action="dashboard.php"] {
        gap: 10px;
    }

    .ash-student form[action="dashboard.php"] select {
        flex-basis: 100%;
    }

    .ash-student .ash-semester-confirm {
        width: 100%;
    }
}
</style>


<style>
/* ASHFORD_OVERVIEW_MODERN_SPACING_V4 */

/* Compact dashboard spacing */
.ash-student .ash-head {
    margin-bottom: 14px !important;
}

.ash-student .ash-head h2 {
    margin-bottom: 4px !important;
    font-size: 29px;
}

.ash-student .ash-sub {
    font-size: 14px;
}

/* Semester selector as one compact panel */
.ash-student form[action="dashboard.php"] {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: end !important;
    column-gap: 12px !important;
    row-gap: 8px !important;
    margin: 0 0 16px !important;
    padding: 14px 16px !important;
    background: #ffffff;
    border: 1px solid #e0e5ef;
    border-radius: 14px;
    box-shadow: 0 3px 12px rgba(16,36,74,.035);
}

.ash-student form[action="dashboard.php"] label {
    grid-column: 1 / -1;
    margin: 0 !important;
    font-size: 13px;
    font-weight: 700;
    color: #263958;
}

.ash-student form[action="dashboard.php"] select {
    grid-column: 1;
    width: 100% !important;
    min-width: 0;
    min-height: 44px !important;
    height: 44px !important;
    margin: 0 !important;
    padding: 8px 16px !important;
    border: 1px solid #d9ddec !important;
    border-radius: 12px !important;
    background-color: #f8f9fd;
    color: #172a52;
    font-size: 15px;
    font-weight: 650;
}

.ash-student .ash-semester-confirm {
    grid-column: 2;
    min-height: 44px !important;
    height: 44px;
    padding: 0 24px !important;
    border-radius: 12px !important;
    background: #42349a;
    font-size: 14px;
    font-weight: 700;
    white-space: nowrap;
}

/* Reduce unused space between sections */
.ash-student .ash-panel {
    margin-bottom: 14px !important;
}

.ash-student .ash-card {
    padding: 18px 20px !important;
    border-radius: 14px !important;
}

.ash-student .ash-card h3 {
    margin: 0 0 14px !important;
    font-size: 19px;
}

.ash-student .ash-info-grid {
    gap: 14px !important;
}

.ash-student .ash-info-row {
    padding: 9px 0 !important;
}

/* More compact course table */
.ash-student th {
    padding: 11px 12px !important;
}

.ash-student td {
    padding: 12px !important;
}

.ash-student .ash-table-wrap {
    margin: 0 !important;
}

@media (max-width: 600px) {
    .ash-student form[action="dashboard.php"] {
        grid-template-columns: 1fr;
        padding: 12px !important;
    }

    .ash-student form[action="dashboard.php"] select,
    .ash-student .ash-semester-confirm {
        grid-column: 1;
        width: 100% !important;
    }

    .ash-student .ash-card {
        padding: 15px !important;
    }
}
</style>

<div class="dashboard-page ash-student">

<header class="ash-head">
    <h2>Welcome, <?= e($displayName) ?></h2>
    <p class="ash-sub">
        <?= e($student['student_Type'] ?? 'Student') ?>
        · <?= e($semesterLabel ?? 'Current semester') ?>
    </p>
</header>

<!-- Academic semester selector -->
<form method="get" action="dashboard.php" style="margin:0 0 20px">
  <label for="semester" style="font-weight:650;margin-right:10px">Academic Semester</label>
  <select id="semester" name="semester"  style="padding:10px;border:1px solid #d9deeb;border-radius:8px">
    <?php foreach ($availableSemesters as $term): ?>
      <option value="<?= (int)$term['semester_ID'] ?>"
        <?= (int)$currentSemester === (int)$term['semester_ID'] ? 'selected' : '' ?>>
        <?= e($term['semester_Name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="ash-semester-confirm">
      Confirm
  </button>
</form>

<!-- Full-width current courses -->
<section class="ash-card ash-panel"><h3>My Current Courses</h3>
    <?php if (!empty($schedule)): ?><div class="ash-table-wrap"><table><thead><tr><th>Course</th><th>Course Title</th><th>Faculty</th><th>Schedule</th><th>Credits</th></tr></thead><tbody>
    <?php foreach ($schedule as $course): $detail=$courseDetails[(int)$course['CRN']] ?? []; $code=(string)$course['course_ID']; ?>
     <tr><td class="ash-course-code"><?= e($code) ?></td><td><?= e($course['course_Name']) ?></td><td><?= e($detail['faculty_Name'] ?? 'Not assigned') ?></td><td><?= isset($detail['time_Slot_ID']) ? e(time_slot_label($pdo,(int)$detail['time_Slot_ID'])) : 'Not scheduled' ?></td><td><?= e($course['course_Credits']) ?></td></tr>
    <?php endforeach; ?></tbody></table></div>
    <?php else: ?><p class="ash-note">No courses are registered for <?= e($semesterLabel ?? 'this semester') ?>.</p><?php endif; ?>
    <p style="text-align:right;margin:15px 0 0"><a href="schedule.php">View My Schedule →</a></p>
   </section>

<!-- Academic and student details -->
<div class="ash-info-grid">

    <section class="ash-card ash-info-card">
        <h3>Academic Summary</h3>

        <div class="ash-info-row">
            <span class="ash-info-label">Academic Level</span>
            <strong class="ash-info-value">
                <?= e($student['student_Type'] ?? 'Not available') ?>
            </strong>
        </div>

        <div class="ash-info-row">
            <span class="ash-info-label">Cumulative GPA</span>
            <strong class="ash-info-value">
                <?= $gpa !== null
                    ? e(number_format((float)$gpa, 2))
                    : '—' ?>
            </strong>
        </div>

        <div class="ash-info-row">
            <span class="ash-info-label">Enrolled Credits</span>
            <strong class="ash-info-value">
                <?= e((int)($credits['total'] ?? 0)) ?>
            </strong>
        </div>

        <div class="ash-info-row">
            <span class="ash-info-label">Major</span>
            <strong class="ash-info-value">
                <?= e($student['major_Name'] ?? 'Undeclared') ?>
            </strong>
        </div>

        <div class="ash-info-row">
            <span class="ash-info-label">Minor</span>
            <strong class="ash-info-value">
                <?= e($studentMinorDisplay ?? '') ?>
            </strong>
        </div>
    </section>

    <section class="ash-card ash-info-card">
        <h3>Student Information</h3>

        <div class="ash-info-row">
            <span class="ash-info-label">Student ID</span>
            <strong class="ash-info-value">
                <?= e((string)($student['user_ID'] ?? 'Unavailable')) ?>
            </strong>
        </div>

        <div class="ash-info-row">
            <span class="ash-info-label">Account Status</span>
            <strong class="ash-info-value">Logged in</strong>
        </div>

        <div class="ash-info-row">
            <span class="ash-info-label">Academic Advisor</span>
            <strong class="ash-info-value">
                <?= e($student['advisor_Name'] ?? 'Not assigned') ?>
            </strong>
        </div>

        <div class="ash-info-row">
            <span class="ash-info-label">Registration Holds</span>
            <strong class="ash-info-value">
                <?= count($holds ?? []) ?>
            </strong>
        </div>
    </section>

</div>
</div>

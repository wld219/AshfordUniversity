<?php
declare(strict_types=1);
require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_role(['Student']);
$studentID = (int) $_SESSION['user_id'];
$crn = filter_input(INPUT_GET, 'crn', FILTER_VALIDATE_INT);
$section = null;
if ($crn && $crn > 0) {
    $section = one($pdo,
        'SELECT cs.CRN, cs.section_No, cs.semester_ID, cs.time_Slot_ID,
                cs.available_Seats, cs.is_Cancelled, c.course_ID, c.course_Name,
                c.course_Credits, d.dept_Name, s.semester_Name
         FROM Course_Section cs
         JOIN Course c ON c.course_ID = cs.course_ID
         JOIN Department d ON d.dept_ID = c.dept_ID
         JOIN Semester s ON s.semester_ID = cs.semester_ID
         WHERE cs.CRN = ?', [(int) $crn]);
}
$eligible = false;
$eligibilityMessage = 'Course section not found.';
if ($section) {
    [$eligible, $eligibilityMessage] = registration_check($pdo, $studentID, (int) $crn);
}
page_start('Course Details', 'Student', 'registration.php');
flash();
?>
<style>
.ashford-course-detail {font-size:16px;line-height:1.6}
.ashford-course-detail .detail-grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:18px;margin:18px 0}
.ashford-course-detail dt {font-weight:700;margin-bottom:4px}
.ashford-course-detail dd {margin:0;overflow-wrap:anywhere}
.ashford-course-detail .detail-actions {display:flex;flex-wrap:wrap;gap:12px;margin-top:20px}
</style>
<div class="dashboard-page ashford-course-detail">
  <section class="dashboard-header"><div class="header-title"><p class="header-eyebrow">Student Portal / Registration</p><h2>Course Details</h2><p class="header-subtitle">Review course information and eligibility before registering.</p></div><div class="dashboard-actions"><a class="btn light" href="registration.php">Back to Course Search</a><a class="btn light" href="schedule.php">My Schedule</a></div></section>
  <?php if (!$section): ?>
    <div class="alert error">This course section could not be found.</div>
  <?php else: ?>
    <section class="card"><h3><?= e($section['course_ID']) ?> — <?= e($section['course_Name']) ?></h3>
      <dl class="detail-grid">
        <div><dt>CRN</dt><dd><?= e($section['CRN']) ?></dd></div>
        <div><dt>Section</dt><dd><?= e($section['section_No']) ?></dd></div>
        <div><dt>Department</dt><dd><?= e($section['dept_Name']) ?></dd></div>
        <div><dt>Credits</dt><dd><?= e($section['course_Credits']) ?></dd></div>
        <div><dt>Semester</dt><dd><?= e($section['semester_Name']) ?></dd></div>
        <div><dt>Meeting Time</dt><dd><?= e(time_slot_label($pdo, (int) $section['time_Slot_ID'])) ?></dd></div>
        <div><dt>Available Seats</dt><dd><?= e($section['available_Seats']) ?></dd></div>
        <div><dt>Section Status</dt><dd><?= (int) $section['is_Cancelled'] === 1 ? 'Cancelled' : 'Active' ?></dd></div>
      </dl>
    </section>
    <section class="card" aria-labelledby="eligibility-heading"><h3 id="eligibility-heading">Registration Eligibility</h3>
      <div class="alert <?= $eligible ? 'success' : 'error' ?>"><?= e($eligible ? 'Eligible to register. Final checks run when you submit.' : $eligibilityMessage) ?></div>
      <div class="detail-actions">
        <?php if ($eligible): ?><form action="registration.php" method="post"><?= csrf_field() ?><input type="hidden" name="crn" value="<?= e($section['CRN']) ?>"><button class="btn" type="submit">Confirm Registration</button></form><?php endif; ?>
        <a class="btn light" href="registration.php">Back to Search</a>
      </div>
    </section>
  <?php endif; ?>
</div>
<?php page_end(); ?>

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
.ash-student{--ash-navy:#10244a;--ash-purple:#37318a;--ash-border:#e1e6f0; color:#142345;font-size:16px}
.ash-student *{box-sizing:border-box}.ash-student .ash-head{margin-bottom:25px}
.ash-student .ash-head h2{font-size:30px;line-height:1.25;margin:0 0 5px;font-weight:750;color:var(--ash-navy)}
.ash-student .ash-sub{color:#566581;margin:0}.ash-student .ash-grid{display:grid;grid-template-columns:minmax(0,2.3fr) minmax(275px,1fr);gap:18px;align-items:start}
.ash-student .ash-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
.ash-student .ash-card{background:#fff;border:1px solid var(--ash-border);border-radius:13px;padding:21px;box-shadow:0 4px 16px rgba(16,36,74,.045);min-width:0}
.ash-student .ash-metric-label{color:#435477;font-size:14px;font-weight:650;display:block;margin-bottom:8px}
.ash-student .ash-metric-number{display:block;font-size:29px;line-height:1.15;font-weight:800;color:var(--ash-navy)}
.ash-student .ash-metric-note{display:block;font-size:13px;color:#697692;margin-top:8px}
.ash-student .ash-card h3{font-size:19px;color:var(--ash-navy);margin:0 0 18px}
.ash-student .ash-panel{margin-bottom:18px}.ash-student .ash-table-wrap{overflow-x:auto}
.ash-student table{width:100%;border-collapse:collapse;font-size:14px;min-width:640px}
.ash-student th{background:#efefff;color:#18254c;text-align:left;font-weight:700;padding:13px 11px;white-space:nowrap}
.ash-student td{border-bottom:1px solid #e8eaf1;padding:14px 11px;vertical-align:top;color:#253451}
.ash-student .ash-course-code{font-weight:750;color:#142c6a;white-space:nowrap}
.ash-student .ash-action{display:block;text-decoration:none;background:#f3f4ff;color:#1d2b65;padding:13px 15px;border:1px solid #e0e2f1;border-radius:9px;font-weight:650;margin-bottom:10px}
.ash-student .ash-action.primary{background:var(--ash-purple);color:#fff;border-color:var(--ash-purple)}
.ash-student .ash-action:hover{filter:brightness(.96)}.ash-student .ash-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.ash-student .ash-summary strong{display:block;color:var(--ash-navy);font-size:19px;margin-top:6px}
.ash-student .ash-summary span{color:#63718d;font-size:13px}
.ash-student .ash-note{font-size:14px;color:#63718d;line-height:1.6}
@media(max-width:1050px){.ash-student .ash-grid{grid-template-columns:1fr}.ash-student .ash-metrics{grid-template-columns:repeat(2,1fr)}}
@media(max-width:550px){.ash-student .ash-metrics,.ash-student .ash-summary{grid-template-columns:1fr}.ash-student .ash-card{padding:16px}}
</style>
<div class="dashboard-page ash-student">
 <header class="ash-head"><h2>Welcome, <?= e($displayName) ?></h2><p class="ash-sub">Student Dashboard · <?= e($semesterLabel ?? 'Current semester') ?></p></header>
 <form method="get" action="dashboard.php" style="margin:0 0 20px">
  <label for="semester" style="font-weight:650;margin-right:10px">Academic Semester</label>
  <select id="semester" name="semester" onchange="this.form.submit()" style="padding:10px;border:1px solid #d9deeb;border-radius:8px">
    <?php foreach ($availableSemesters as $term): ?>
      <option value="<?= (int)$term['semester_ID'] ?>"
        <?= (int)$currentSemester === (int)$term['semester_ID'] ? 'selected' : '' ?>>
        <?= e($term['semester_Name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
</form>

<div class="ash-grid">
  <main>
   <section class="ash-metrics" aria-label="Academic overview">
    <article class="ash-card"><span class="ash-metric-label">Enrolled Courses</span><strong class="ash-metric-number"><?= count($schedule ?? []) ?></strong><span class="ash-metric-note">Current semester</span></article>
    <article class="ash-card"><span class="ash-metric-label">Credit Hours</span><strong class="ash-metric-number"><?= e((int)($credits['total'] ?? 0)) ?></strong><span class="ash-metric-note">Current enrollment</span></article>
    <article class="ash-card"><span class="ash-metric-label">Cumulative GPA</span><strong class="ash-metric-number"><?= $gpa !== null ? e(number_format((float)$gpa,2)) : '—' ?></strong><span class="ash-metric-note">Completed coursework</span></article>
    <article class="ash-card"><span class="ash-metric-label">Registration Holds</span><strong class="ash-metric-number"><?= count($holds ?? []) ?></strong><span class="ash-metric-note"><?= count($holds ?? []) ? 'Review holds before registering' : 'No current holds' ?></span></article>
   </section>
   <section class="ash-card ash-panel"><h3>My Current Courses</h3>
    <?php if (!empty($schedule)): ?><div class="ash-table-wrap"><table><thead><tr><th>Course</th><th>Course Title</th><th>Faculty</th><th>Schedule</th><th>Credits</th></tr></thead><tbody>
    <?php foreach ($schedule as $course): $detail=$courseDetails[(int)$course['CRN']] ?? []; $code=$deptCode((string)($detail['dept_Name'] ?? 'Course')).' '.$course['course_ID']; ?>
     <tr><td class="ash-course-code"><?= e($code) ?></td><td><?= e($course['course_Name']) ?></td><td><?= e($detail['faculty_Name'] ?? 'Not assigned') ?></td><td><?= isset($detail['time_Slot_ID']) ? e(time_slot_label($pdo,(int)$detail['time_Slot_ID'])) : 'Not scheduled' ?></td><td><?= e($course['course_Credits']) ?></td></tr>
    <?php endforeach; ?></tbody></table></div>
    <?php else: ?><p class="ash-note">No courses are registered for <?= e($semesterLabel ?? 'this semester') ?>.</p><?php endif; ?>
    <p style="text-align:right;margin:15px 0 0"><a href="schedule.php">View My Schedule →</a></p>
   </section>
   <section class="ash-card ash-panel"><h3>Academic Summary</h3><div class="ash-summary">
     <div><span>Current Credits</span><strong><?= e((int)($credits['total'] ?? 0)) ?></strong></div>
     <div><span>Cumulative GPA</span><strong><?= $gpa !== null ? e(number_format((float)$gpa,2)) : '—' ?></strong></div>
     <div><span>Major</span><strong><?= e($student['major_Name'] ?? 'Undeclared') ?></strong></div>
    </div><p class="ash-note">Academic advisor: <?= e($student['advisor_Name'] ?? 'Not assigned') ?> · Student type: <?= e($student['student_Type'] ?? 'Not available') ?></p>
   </section>
  </main>
  <aside>
   <section class="ash-card ash-panel"><h3>Quick Actions</h3>
    <a class="ash-action primary" href="registration.php">Register for Courses →</a>
    <a class="ash-action" href="schedule.php">View My Schedule</a>
    <a class="ash-action" href="grades.php">View Grades</a>
    <a class="ash-action" href="transcript.php">View Transcript</a>
    <a class="ash-action" href="information.php">Update My Profile</a>
   </section>
   <section class="ash-card ash-panel"><h3>Academic Notices</h3>
    <?php if (!empty($holds)): ?><p class="ash-note">You have <?= count($holds) ?> registration hold(s). <a href="holds.php">View holds</a>.</p><?php else: ?><p class="ash-note">No registration holds are currently listed.</p><?php endif; ?>
    <p class="ash-note">Review the <a href="master-schedule.php">Master Schedule</a> for course offerings and meeting times.</p>
   </section>
  </aside>
 </div>
</div>

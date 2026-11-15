<?php
declare(strict_types=1);
// ASHFORD_SCHEDULE_REDESIGN_V1
$displayGrade = static function ($value): string {
    $value = trim((string)($value ?? ''));
    return $value === '' ? 'Not Posted' : $value;
};
?>
<style>
.ash-ms{max-width:1320px;margin:0 auto;color:#17243b}.ash-ms *{box-sizing:border-box}
.ash-ms-head{display:flex;justify-content:space-between;align-items:end;gap:18px;flex-wrap:wrap;margin:4px 0 22px}
.ash-ms h2{font-size:27px;margin:0 0 5px}.ash-ms-sub{color:#64748b;margin:0}
.ash-ms-term{display:flex;flex-direction:column;gap:6px;font-weight:650;font-size:13px}
.ash-ms-term select{min-width:190px;border:1px solid #cbd5e1;border-radius:9px;padding:10px 12px;background:#fff;color:#17243b}
.ash-ms-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:0 0 22px}
.ash-ms-stat,.ash-ms-panel{background:#fff;border:1px solid #dfe6ef;border-radius:14px;box-shadow:0 3px 12px rgba(22,36,59,.035)}
.ash-ms-stat{padding:19px}.ash-ms-stat strong{font-size:28px;display:block;line-height:1.3}.ash-ms-stat span{font-size:13px;color:#64748b}
.ash-ms-panel{padding:21px;margin:0 0 20px}.ash-ms-panel h3{font-size:18px;margin:0 0 5px}.ash-ms-panel .hint{font-size:13px;color:#64748b;margin:0 0 17px}
.ash-ms-scroll{overflow-x:auto}.ash-ms-table{width:100%;border-collapse:collapse;min-width:820px;text-align:left}
.ash-ms-table th{background:#f5f7fb;color:#475569;font-size:12px;text-transform:uppercase;letter-spacing:.035em;padding:13px 12px}
.ash-ms-table td{padding:15px 12px;border-bottom:1px solid #e9edf3;font-size:14px;vertical-align:middle}
.ash-ms-table tr:last-child td{border-bottom:0}.ash-ms-course{font-weight:750}.ash-ms-course small{display:block;font-weight:400;color:#64748b;margin-top:4px}
.ash-ms-grade{font-weight:650}.ash-ms-unposted{color:#64748b;font-size:12px;font-weight:400}
.ash-ms-classes{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.ash-ms-class{border:1px solid #e1e7ef;border-radius:11px;padding:16px;background:#fbfcfe}
.ash-ms-class h4{margin:0 0 6px;font-size:15px}.ash-ms-class p{margin:5px 0;color:#526079;font-size:13px}
.ash-ms-drop{white-space:nowrap}.ash-ms-empty{padding:22px;background:#f8fafc;border-radius:10px;color:#64748b}
@media(max-width:720px){.ash-ms-stats{grid-template-columns:1fr 1fr}.ash-ms-classes{grid-template-columns:1fr}.ash-ms-panel{padding:14px}.ash-ms h2{font-size:23px}}
</style>
<div class="ash-ms">
 <div class="ash-ms-head">
  <div><h2>My Academic Schedule</h2><p class="ash-ms-sub">Your enrolled courses, meeting times and grades</p></div>
  <?php if ($semesters): ?>
  <form method="get" action="schedule.php" class="ash-ms-term">
   <label for="ash-ms-semester">Academic Semester</label>
   <select name="semester" id="ash-ms-semester" onchange="this.form.submit()">
    <?php foreach ($semesters as $id => $name): ?><option value="<?= (int)$id ?>" <?= $selectedSemester === (int)$id ? 'selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?>
   </select><noscript><button class="btn" type="submit">View Semester</button></noscript>
  </form>
  <?php endif; ?>
 </div>
 <div class="ash-ms-stats">
  <div class="ash-ms-stat"><strong><?= count($termCourses) ?></strong><span>Enrolled Courses</span></div>
  <div class="ash-ms-stat"><strong><?= e(rtrim(rtrim(number_format($totalCredits, 2), '0'), '.')) ?></strong><span>Registered Credits</span></div>
  <div class="ash-ms-stat"><strong>—</strong><span>Term GPA · Official final grades only</span></div>
 </div>
 <section class="ash-ms-panel" aria-labelledby="ash-ms-grades-heading">
  <h3 id="ash-ms-grades-heading">Courses &amp; Grades</h3>
  <p class="hint">Current and midterm grades are informational. Final grades are recorded separately.</p>
  <?php if (!$termCourses): ?><div class="ash-ms-empty">No courses found for this semester.</div>
  <?php else: ?><div class="ash-ms-scroll"><table class="ash-ms-table"><thead><tr>
   <th>Course</th><th>CRN</th><th>Instructor</th><th>Credits</th><th>Current</th><th>Midterm</th><th>Final</th><th>Action</th>
  </tr></thead><tbody>
  <?php foreach ($termCourses as $course): ?><tr>
   <td><span class="ash-ms-course"><?= e($course['course_ID']) ?><small><?= e($course['course_Name']) ?></small></span></td>
   <td><?= e($course['CRN']) ?></td>
   <td><?= e(trim((string)($course['first_Name'] ?? '') . ' ' . (string)($course['last_Name'] ?? '')) ?: 'Not assigned') ?></td>
   <td><?= e($course['course_Credits']) ?></td>
   <?php foreach (['current_grade','midterm_grade','final_grade'] as $key): $grade = $displayGrade($course[$key] ?? null); ?>
    <td><span class="<?= $grade === 'Not Posted' ? 'ash-ms-unposted' : 'ash-ms-grade' ?>"><?= e($grade) ?></span></td>
   <?php endforeach; ?>
   <td class="ash-ms-drop"><form method="post" action="schedule.php?semester=<?= (int)$selectedSemester ?>">
     <?= csrf_field() ?><button type="submit" class="btn danger small" name="drop" value="<?= e($course['CRN']) ?>" data-confirm="Drop this course?">Drop</button>
   </form></td>
  </tr><?php endforeach; ?>
  </tbody></table></div><?php endif; ?>
 </section>
 <section class="ash-ms-panel" aria-labelledby="ash-ms-meetings-heading">
  <h3 id="ash-ms-meetings-heading">Weekly Class Schedule</h3>
  <p class="hint">Meeting days and times for your selected semester.</p>
  <?php if (!$termCourses): ?><div class="ash-ms-empty">No scheduled courses.</div>
  <?php else: ?><div class="ash-ms-classes">
   <?php foreach ($termCourses as $course): ?>
   <article class="ash-ms-class"><h4><?= e($course['course_ID']) ?> — <?= e($course['course_Name']) ?></h4>
    <p><strong>Meeting:</strong> <?= isset($course['time_Slot_ID']) && $course['time_Slot_ID'] !== null ? e((string)time_slot_label($pdo, (int)$course['time_Slot_ID'])) : 'Not scheduled' ?></p>
    <p><strong>Instructor:</strong> <?= e(trim((string)($course['first_Name'] ?? '') . ' ' . (string)($course['last_Name'] ?? '')) ?: 'Not assigned') ?></p>
    <p><strong>CRN:</strong> <?= e($course['CRN']) ?> · <strong>Credits:</strong> <?= e($course['course_Credits']) ?></p>
   </article>
   <?php endforeach; ?>
  </div><?php endif; ?>
 </section>
</div>

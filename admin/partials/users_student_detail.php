<?php declare(strict_types=1); ?>
<section class="users-student-detail" aria-label="Student record">
 <div class="dashboard-section-heading"><div><p class="dashboard-eyebrow">Student record · Read-only academic views</p><h3><?= e(trim($viewStudent['first_Name'].' '.$viewStudent['last_Name'])) ?> · <?= e($viewStudent['user_ID']) ?></h3></div><a class="btn small light" href="users.php">Close record</a></div>
 <div class="users-record-nav" role="navigation" aria-label="Student record sections">
 <?php foreach (['personal'=>'Personal','academic'=>'Academic','enrollment'=>'Enrollment','transcript'=>'Transcript & Grades','audit'=>'Degree Audit','holds'=>'Holds'] as $key=>$name): ?><a href="#student-<?= e($key) ?>"><?= e($name) ?></a><?php endforeach; ?>
 </div>
 <section id="student-personal" class="users-record-section"><h4>Personal information</h4><div class="users-detail-grid">
 <?php foreach (['user_Email'=>'Email','phone_no'=>'Phone','street'=>'Street','city'=>'City','state'=>'State','zip_Code'=>'ZIP code','gender'=>'Gender'] as $field=>$label): ?><div><strong><?= e($label) ?></strong><?= e((string)($viewStudent[$field] ?? '—')) ?></div><?php endforeach; ?>
 </div></section>
 <section id="student-academic" class="users-record-section"><h4>Academic information</h4><div class="users-detail-grid">
 <div><strong>Student type</strong><?= e($viewStudent['student_Type'] ?? '—') ?></div><div><strong>Academic year</strong><?= e($viewStudent['student_Year'] ?? '—') ?></div><div><strong>Primary major</strong><?= e($viewStudent['major_Name'] ?? '—') ?></div><div><strong>Minor</strong><?= e($viewStudentMinor['minor_Name'] ?? 'None recorded') ?></div><div><strong>Advisor</strong><?= $viewStudentAdvisor ? e(trim($viewStudentAdvisor['first_Name'].' '.$viewStudentAdvisor['last_Name'])) : 'Not assigned' ?></div>
 </div></section>
 <section id="student-enrollment" class="users-record-section"><h4>Course enrollment</h4><p class="muted">Current and recorded course registrations; changes are managed in the designated registration workflow.</p>
 <?php if (!$viewStudentEnrollments): ?><p>No enrollment records found.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Semester</th><th>Course</th><th>CRN</th><th>Credits</th><th>Recorded grade</th></tr></thead><tbody><?php $lastEnrollmentSemester = null; ?>
<?php foreach ($viewStudentEnrollments as $row): ?>
<?php if ($lastEnrollmentSemester !== $row['semester_Name']): ?>
<tr class="users-semester-heading">
<td colspan="5" class="users-semester-cell">
<?= e($row['semester_Name']) ?>
</td>
</tr>
<?php $lastEnrollmentSemester = $row['semester_Name']; ?>
<?php endif; ?><tr><td><?= e($row['semester_Name']) ?></td><td><?= e($row['course_ID'].' — '.$row['course_Name']) ?></td><td><?= e($row['CRN']) ?></td><td><?= e($row['course_Credits']) ?></td><td><?= e($row['Grade'] ?? 'In progress') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
 <section id="student-transcript" class="users-record-section"><h4>Unofficial transcript and grades</h4><div class="users-detail-grid"><div><strong>Cumulative GPA</strong><?= e($viewStudentSummary['gpa'] ?? '—') ?></div><div><strong>Credits attempted</strong><?= e($viewStudentSummary['attempted']) ?></div><div><strong>Credits earned</strong><?= e($viewStudentSummary['earned']) ?></div></div>
 <?php if (!$viewStudentHistory): ?><p>No completed course history recorded.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Semester</th><th>Course</th><th>Credits</th><th>Grade</th></tr></thead><tbody><?php $lastTranscriptSemester = null; ?>
<?php foreach ($viewStudentHistory as $row): ?>
<?php if ($lastTranscriptSemester !== $row['semester_Name']): ?>
<tr class="users-semester-heading">
<td colspan="4" class="users-semester-cell">
<?= e($row['semester_Name']) ?>
</td>
</tr>
<?php $lastTranscriptSemester = $row['semester_Name']; ?>
<?php endif; ?><tr><td><?= e($row['semester_Name']) ?></td><td><?= e($row['course_ID'].' — '.$row['course_Name']) ?></td><td><?= e($row['course_Credits']) ?></td><td><?= e($row['Grade'] ?? '—') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
 <section id="student-audit" class="users-record-section"><h4>Degree audit</h4><p class="muted">Requirement checks use recorded course history and minimum grades; this is an informational view.</p>
 <?php foreach ($viewStudentAudit as $program): ?><?php $met = count(array_filter($program['requirements'],static fn($r)=>$r['met'])); ?><h5><?= e($program['kind'].' — '.$program['name']) ?> (<?= e($met) ?>/<?= e(count($program['requirements'])) ?>)</h5><div class="table-wrap"><table><thead><tr><th>Required course</th><th>Minimum grade</th><th>Best grade</th><th>Status</th></tr></thead><tbody><?php foreach ($program['requirements'] as $req): ?><tr><td><?= e($req['course_ID'].' — '.$req['course_Name']) ?></td><td><?= e($req['min_Grade_Req']) ?></td><td><?= e($req['earned_grade'] ?? '—') ?></td><td><?= $req['met'] ? 'Met' : 'Not met' ?></td></tr><?php endforeach; ?></tbody></table></div><?php endforeach; ?></section>
 <section id="student-holds" class="users-record-section"><h4>Student holds</h4>
 <?php if (!$viewStudentHolds): ?><p class="muted">No holds on record.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Hold type</th><th>Date</th><?php if ($isFullAccess): ?><th>Action</th><?php endif; ?></tr></thead><tbody>
 <?php foreach ($viewStudentHolds as $hold): ?><tr><td><?= e($hold['hold_Type']) ?></td><td><?= e($hold['hold_Date']) ?></td><?php if ($isFullAccess): ?><td><form method="post" action="<?= e(url('admin/users.php')) ?>" onsubmit="return confirm('Remove this hold?')"><?= csrf_field() ?><input type="hidden" name="action" value="remove_hold"><input type="hidden" name="student_id" value="<?= e($viewStudent['user_ID']) ?>"><input type="hidden" name="hold_id" value="<?= e($hold['hold_ID']) ?>"><button class="btn danger small" type="submit">Remove</button></form></td><?php endif; ?></tr><?php endforeach; ?>
 </tbody></table></div><?php endif; ?>
 <?php if ($isFullAccess): ?><form method="post" action="<?= e(url('admin/users.php')) ?>" class="filters" style="margin-top:16px"><input type="hidden" name="action" value="add_hold"><?= csrf_field() ?><input type="hidden" name="student_id" value="<?= e($viewStudent['user_ID']) ?>"><div><label for="student-hold-type">Hold type</label><select id="student-hold-type" name="hold_id" required><option value="">Select hold</option><?php foreach ($holdTypes as $hold): ?><option value="<?= e($hold['hold_ID']) ?>"><?= e($hold['hold_Type']) ?></option><?php endforeach; ?></select></div><div><label for="student-hold-date">Date</label><input id="student-hold-date" type="date" name="hold_date" value="<?= e(date('Y-m-d')) ?>" required></div><div style="align-self:end"><button class="btn" type="submit">Add hold</button></div></form><?php endif; ?></section>
</section>

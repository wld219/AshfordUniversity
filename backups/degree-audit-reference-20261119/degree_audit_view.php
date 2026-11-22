<?php

declare(strict_types=1);

$majorAudits = array_values(array_filter($studentAuditPrograms, static fn(array $audit): bool => $audit['type'] === 'Major'));
$minorAudits = array_values(array_filter($studentAuditPrograms, static fn(array $audit): bool => $audit['type'] === 'Minor'));
?>

<style>
.degree-audit { --da-ink:#18304f; --da-muted:#65758b; --da-line:#dce4ec; --da-wash:#f5f8fb; --da-gold:#d5a62e; color:var(--da-ink); }
.degree-audit * { box-sizing:border-box; }
.degree-audit .da-heading { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; margin:0 0 1.1rem; }
.degree-audit .da-eyebrow { margin:0 0 .3rem; color:#88702d; font-size:.76rem; font-weight:750; letter-spacing:.11em; text-transform:uppercase; }
.degree-audit h1 { margin:0; font-size:clamp(1.65rem,3vw,2.35rem); line-height:1.12; }
.degree-audit h2 { margin:0; font-size:1.12rem; }
.degree-audit .da-term { border:1px solid var(--da-line); border-left:4px solid var(--da-gold); border-radius:9px; background:#fff; padding:.65rem .85rem; font-weight:700; white-space:nowrap; }
.degree-audit .da-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.8rem; margin-bottom:1rem; }
.degree-audit .da-metric { border:1px solid var(--da-line); border-radius:10px; background:#fff; padding:.9rem 1rem; min-width:0; }
.degree-audit .da-metric-label { display:block; color:var(--da-muted); font-size:.78rem; margin-bottom:.35rem; }
.degree-audit .da-metric-value { display:block; font-weight:750; font-size:1rem; overflow-wrap:anywhere; }
.degree-audit .da-metric-value.numeric { font-size:1.45rem; font-variant-numeric:tabular-nums; }
.degree-audit .da-section { margin:1rem 0; padding:1rem; border:1px solid var(--da-line); border-radius:11px; background:#fff; }
.degree-audit .da-section-head { display:flex; justify-content:space-between; align-items:baseline; gap:.75rem; padding-bottom:.7rem; border-bottom:1px solid var(--da-line); margin-bottom:.75rem; }
.degree-audit .da-section-note,.degree-audit .da-muted { color:var(--da-muted); font-size:.9rem; }
.degree-audit .da-program { margin:1rem 0; border:1px solid var(--da-line); border-radius:9px; overflow:hidden; }
.degree-audit .da-program-head { display:flex; justify-content:space-between; gap:.75rem; align-items:center; padding:.75rem .85rem; background:var(--da-wash); }
.degree-audit .da-program-head h3 { margin:0; font-size:1rem; }
.degree-audit .da-tag { display:inline-flex; align-items:center; border-radius:999px; padding:.2rem .55rem; background:#e9eff6; color:#23466a; font-size:.76rem; font-weight:700; white-space:nowrap; }
.degree-audit .da-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
.degree-audit table { width:100%; border-collapse:collapse; min-width:670px; }
.degree-audit th,.degree-audit td { padding:.65rem .75rem; border-bottom:1px solid #e8edf2; text-align:left; vertical-align:top; }
.degree-audit th { color:#53677f; font-size:.74rem; letter-spacing:.045em; text-transform:uppercase; }
.degree-audit td { font-size:.9rem; }
.degree-audit tbody tr:last-child td { border-bottom:0; }
.degree-audit .da-code { font-weight:750; white-space:nowrap; }
.degree-audit .da-credit { white-space:nowrap; font-variant-numeric:tabular-nums; }
.degree-audit .da-status { font-weight:700; }
.degree-audit .da-status.completed { color:#23623c; }
.degree-audit .da-status.progress { color:#825d0a; }
.degree-audit .da-status.not-started { color:#53677f; }
.degree-audit .da-empty { padding:.8rem; border-radius:8px; background:var(--da-wash); color:var(--da-muted); }
.degree-audit .da-picker { margin:0 0 1rem; display:flex; align-items:end; gap:.6rem; flex-wrap:wrap; }
.degree-audit .da-picker label { display:block; margin-bottom:.3rem; font-weight:700; }
.degree-audit .da-picker select { min-height:44px; padding:.5rem .7rem; border:1px solid #aab8c7; border-radius:7px; background:#fff; color:inherit; font:inherit; }
.degree-audit .da-picker button { min-height:44px; }
@media(max-width:800px) { .degree-audit .da-summary { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:520px) { .degree-audit .da-heading { align-items:flex-start; flex-direction:column; } .degree-audit .da-term { white-space:normal; } .degree-audit .da-summary { grid-template-columns:1fr 1fr; gap:.55rem; } .degree-audit .da-metric { padding:.75rem; } .degree-audit .da-section { padding:.75rem; } .degree-audit .da-program-head { align-items:flex-start; flex-direction:column; } }
</style>

<div class="degree-audit" aria-labelledby="degree-audit-title">
	<header class="da-heading">
		<div><p class="da-eyebrow">Academic planning</p><h1 id="degree-audit-title">Degree Audit</h1></div>
		<div class="da-term"><?= e($studentAuditSemesterName ?: 'Current semester unavailable') ?></div>
	</header>

	<?php if (!$studentAuditProfile): ?>
		<section class="da-section"><h2>Student record unavailable</h2><p class="da-muted">We could not load your academic record. Please contact the Registrar’s Office.</p></section>
	<?php else: ?>
		<section class="da-summary" aria-label="Student academic summary">
			<div class="da-metric"><span class="da-metric-label">Student</span><strong class="da-metric-value"><?= e($studentAuditFullName ?: 'Name unavailable') ?></strong><span class="da-metric-label">ID <?= e($studentAuditProfile['user_ID'] ?? $studentID) ?></span></div>
			<div class="da-metric"><span class="da-metric-label">Academic level</span><strong class="da-metric-value"><?= e($studentAuditProfile['student_Type']) ?></strong><span class="da-metric-label">Program: <?= e(($studentAuditProfile['student_Type'] ?? '') === 'Graduate' ? ($studentAuditProfile['graduate_Program'] ?? 'Not recorded') : ($studentAuditProfile['major_Name'] ?? 'Not declared')) ?></span></div>
			<div class="da-metric"><span class="da-metric-label">Enrollment classification</span><strong class="da-metric-value"><?= e($studentAuditSemesterStatus['status'] ?? 'Unavailable') ?></strong><span class="da-metric-label"><?= e($studentAuditSemesterStatus['registered_credits'] ?? 0) ?> registered semester credits</span></div>
			<div class="da-metric"><span class="da-metric-label">Academic standing</span><strong class="da-metric-value"><?= e($studentAuditAcademicStanding) ?></strong><span class="da-metric-label">Earned credits (distinct completed courses): <?= e($studentAuditEarnedCredits) ?></span></div>
		</section>

		<?php if ($studentAuditDeclaredMinors): ?><p class="da-section-note"><strong>Declared minor<?= count($studentAuditDeclaredMinors) === 1 ? '' : 's' ?>:</strong> <?= e(implode(', ', $studentAuditDeclaredMinors)) ?></p><?php endif; ?>

		<form class="da-picker" method="get" action="degree-audit.php">
			<div><label for="audit-semester">Audit semester</label><select id="audit-semester" name="semester"><?php foreach ($studentAuditSemesters as $term): ?><option value="<?= (int)$term['semester_ID'] ?>" <?= (int)$studentAuditSemesterID === (int)$term['semester_ID'] ? 'selected' : '' ?>><?= e($term['semester_Name']) ?></option><?php endforeach; ?></select></div>
			<button type="submit" class="btn">View semester</button>
		</form>

		<section class="da-section" aria-labelledby="general-ed-title"><div class="da-section-head"><h2 id="general-ed-title">General Education Requirements</h2><span class="da-tag">Not configured</span></div><p class="da-empty">General Education course mappings are not published in the academic requirements tables.</p></section>

		<section class="da-section" aria-labelledby="major-title"><div class="da-section-head"><h2 id="major-title">Major Requirements</h2><span class="da-section-note">Published course checklist</span></div>
			<?php if ($studentAuditGraduateMessage !== null): ?><p class="da-empty"><?= e($studentAuditGraduateMessage) ?></p>
			<?php elseif (!$majorAudits): ?><p class="da-empty">No major is declared for this student.</p>
			<?php else: foreach ($majorAudits as $audit): ?>
				<div class="da-program"><div class="da-program-head"><h3><?= e($audit['name']) ?><?= $audit['primary'] ? ' · Primary' : '' ?></h3><span class="da-tag"><?= (int)$audit['total'] ?> published courses</span></div>
					<?php if (!$audit['total']): ?><p class="da-empty">Requirements Not Published.</p><?php else: ?><div class="da-table-wrap"><table><caption class="visually-hidden"><?= e($audit['name']) ?> major requirements</caption><thead><tr><th scope="col">Course</th><th scope="col">Title</th><th scope="col">Required credits</th><th scope="col">Earned credits</th><th scope="col">Final grade</th><th scope="col">Status</th></tr></thead><tbody>
					<?php foreach ($audit['rows'] as $row): ?><tr><td class="da-code"><?= e($row['course_ID']) ?></td><td><?= e($row['course_Name']) ?><br><span class="da-section-note">Minimum grade: <?= e($row['min_Grade_Req']) ?></span></td><td class="da-credit"><?= e($row['course_Credits']) ?></td><td class="da-credit"><?= e($row['earned_credits']) ?></td><td><?= e($row['met'] ? ($row['grade'] ?? '—') : '—') ?></td><td class="da-status <?= $row['met'] ? 'completed' : ($row['in_progress'] ? 'progress' : 'not-started') ?>"><?= e($row['status']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
			<?php endforeach; endif; ?>
		</section>

		<section class="da-section" aria-labelledby="minor-title"><div class="da-section-head"><h2 id="minor-title">Minor Requirements</h2><span class="da-section-note">Declared minors only</span></div>
			<?php if (!$studentAuditDeclaredMinors): ?><p class="da-empty">No minor declared.</p><?php else: foreach ($minorAudits as $audit): ?>
				<div class="da-program"><div class="da-program-head"><h3><?= e($audit['name']) ?></h3><span class="da-tag"><?= (int)$audit['total'] ?> published courses</span></div>
					<?php if (!$audit['total']): ?><p class="da-empty">Requirements Not Published.</p><?php else: ?><div class="da-table-wrap"><table><caption class="visually-hidden"><?= e($audit['name']) ?> minor requirements</caption><thead><tr><th scope="col">Course</th><th scope="col">Title</th><th scope="col">Required credits</th><th scope="col">Earned credits</th><th scope="col">Final grade</th><th scope="col">Status</th></tr></thead><tbody><?php foreach ($audit['rows'] as $row): ?><tr><td class="da-code"><?= e($row['course_ID']) ?></td><td><?= e($row['course_Name']) ?><br><span class="da-section-note">Minimum grade: <?= e($row['min_Grade_Req']) ?></span></td><td><?= e($row['course_Credits']) ?></td><td><?= e($row['earned_credits']) ?></td><td><?= e($row['met'] ? ($row['grade'] ?? '—') : '—') ?></td><td class="da-status <?= $row['met'] ? 'completed' : ($row['in_progress'] ? 'progress' : 'not-started') ?>"><?= e($row['status']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
			<?php endforeach; endif; ?>
		</section>

		<section class="da-section" aria-labelledby="elective-title"><div class="da-section-head"><h2 id="elective-title">Electives</h2><span class="da-tag">Not configured</span></div><p class="da-empty">No elective course mapping is published. Courses outside a major/minor checklist are not assumed to satisfy elective credit.</p></section>
		<section class="da-section" aria-labelledby="overall-title"><div class="da-section-head"><h2 id="overall-title">Overall Degree Requirements</h2><span class="da-tag">Not published</span></div><p class="da-empty">A total required-credit threshold and overall graduation mapping are not configured. Earned credits above are distinct completed courses from Student_History; no overall completion percentage is inferred.</p></section>

		<section class="da-section" aria-labelledby="registered-title"><div class="da-section-head"><h2 id="registered-title"><?= e($studentAuditSemesterName) ?> — Currently Registered</h2><span class="da-tag"><?= e($studentAuditSemesterStatus['registered_credits'] ?? 0) ?> credits</span></div>
			<?php if (!$studentAuditRegisteredCourses): ?><p class="da-empty">No courses registered for <?= e($studentAuditSemesterName ?: 'this semester') ?>.</p><?php else: ?><div class="da-table-wrap"><table><caption class="visually-hidden">Current registered courses</caption><thead><tr><th scope="col">Course</th><th scope="col">Title</th><th scope="col">Credits</th><th scope="col">CRN</th><th scope="col">Meeting</th></tr></thead><tbody><?php foreach ($studentAuditRegisteredCourses as $course): ?><tr><td class="da-code"><?= e($course['course_ID']) ?></td><td><?= e($course['course_Name']) ?></td><td><?= e($course['course_Credits']) ?></td><td><?= e($course['CRN']) ?></td><td><?= e($course['meeting_days']) ?> · <?= e($course['meeting_time']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
		</section>
	<?php endif; ?>
</div>

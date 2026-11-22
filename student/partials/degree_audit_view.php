<?php

declare(strict_types=1);

$majorAudits = array_values(array_filter($studentAuditPrograms, static fn(array $audit): bool => $audit['type'] === 'Major'));
$minorAudits = array_values(array_filter($studentAuditPrograms, static fn(array $audit): bool => $audit['type'] === 'Minor'));
$studentAuditRegisteredUniqueCredits = 0;
$studentAuditRegisteredCourseIDs = [];
foreach ($studentAuditRegisteredCourses as $registeredCourse) {
	$courseID = (string) $registeredCourse['course_ID'];
	if (isset($studentAuditEarnedCourses[$courseID]) || isset($studentAuditRegisteredCourseIDs[$courseID])) continue;
	$studentAuditRegisteredCourseIDs[$courseID] = true;
	$studentAuditRegisteredUniqueCredits += (int) $registeredCourse['course_Credits'];
}
$studentAuditTotalCredits = $studentAuditEarnedCredits + $studentAuditRegisteredUniqueCredits;

$statusClass = static fn(string $status): string => match ($status) {
	'Completed' => 'is-complete',
	'In Progress' => 'is-progress',
	'Failed' => 'is-failed',
	'Withdrawn' => 'is-withdrawn',
	'Registered' => 'is-registered',
	default => 'is-pending',
};

$renderRequirementTable = static function (array $audit, string $caption) use ($statusClass): void { ?>
	<div class="da-table-wrap">
		<table>
			<caption class="visually-hidden"><?= e($caption) ?></caption>
			<thead><tr><th scope="col">Course</th><th scope="col">Title</th><th scope="col">Credits</th><th scope="col">Status</th><th scope="col">Term</th></tr></thead>
			<tbody>
			<?php foreach ($audit['rows'] as $row): ?>
				<tr>
					<td class="da-code"><?= e($row['course_ID']) ?></td>
					<td><?= e($row['course_Name']) ?></td>
					<td class="da-number"><?= e($row['course_Credits']) ?></td>
					<td><span class="da-status <?= e($statusClass((string) $row['status'])) ?>"><?= e($row['status']) ?><?= $row['grade'] ? ' (' . e($row['grade']) . ')' : '' ?><?= $row['minimum_not_met'] ? ' (minimum ' . e($row['min_Grade_Req']) . ' not met)' : '' ?></span></td>
					<td><?= e($row['term']) ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php };
?>

<style>
.degree-audit { --da-navy:#173456; --da-ink:#132b49; --da-muted:#61748b; --da-line:#d7e1ec; --da-bluewash:#edf4fc; color:var(--da-ink); }
.degree-audit * { box-sizing:border-box; }
.degree-audit .da-intro { margin:-.4rem 0 1rem; color:#263b55; font-size:1rem; }
.degree-audit .da-summary { overflow:hidden; margin:0 0 1.1rem; border:1px solid var(--da-line); border-radius:12px; background:#fff; box-shadow:0 2px 7px rgba(18,45,76,.05); }
.degree-audit .da-summary-head { display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:.8rem 1rem; border-bottom:1px solid var(--da-line); }
.degree-audit h2 { margin:0; font-size:1.2rem; line-height:1.3; color:var(--da-ink); }
.degree-audit .da-picker { display:flex; align-items:center; gap:.45rem; }
.degree-audit .da-picker label { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
.degree-audit .da-picker select { min-height:40px; padding:.4rem .7rem; border:1px solid #bdcad8; border-left:3px solid #d4a329; border-radius:7px; background:#fff; color:var(--da-ink); font:inherit; }
.degree-audit .da-summary-identity { display:grid; grid-template-columns:1fr 1.25fr 1.15fr 1fr; padding:.85rem 1rem; }
.degree-audit .da-identity-item { min-width:0; padding:.15rem .85rem; border-right:1px solid var(--da-line); }
.degree-audit .da-identity-item:first-child { padding-left:.2rem; }
.degree-audit .da-identity-item:last-child { border-right:0; }
.degree-audit .da-label { display:block; margin:0 0 .22rem; color:var(--da-muted); font-size:.79rem; }
.degree-audit .da-value { display:block; color:var(--da-ink); font-size:1rem; font-weight:750; overflow-wrap:anywhere; }
.degree-audit .da-subvalue { display:block; margin-top:.18rem; color:#4f637a; font-size:.84rem; }
.degree-audit .da-standing { color:#21633d; }
.degree-audit .da-metrics { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.75rem; padding:.25rem 1rem 1rem; }
.degree-audit .da-metric { display:flex; align-items:center; gap:.7rem; min-width:0; min-height:72px; padding:.7rem .8rem; border-radius:9px; background:var(--da-bluewash); }
.degree-audit .da-metric-mark { display:grid; width:38px; height:38px; flex:0 0 38px; place-items:center; border-radius:50%; background:#dceafb; color:#234c79; font-weight:800; }
.degree-audit .da-metric strong { display:block; font-size:1.18rem; line-height:1.15; font-variant-numeric:tabular-nums; }
.degree-audit .da-metric .da-label { margin-bottom:.18rem; }
.degree-audit .da-requirement { overflow:hidden; margin:1rem 0; border:1px solid var(--da-line); border-radius:10px; background:#fff; }
.degree-audit .da-requirement-head { display:flex; justify-content:space-between; align-items:center; gap:.75rem; min-height:40px; padding:.55rem .9rem; background:var(--da-navy); color:#fff; }
.degree-audit .da-requirement-head h2 { color:#fff; font-size:1.02rem; }
.degree-audit .da-count { padding:.18rem .65rem; border-radius:999px; background:#fff; color:#173456; font-size:.77rem; font-weight:750; white-space:nowrap; }
.degree-audit .da-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
.degree-audit table { width:100%; min-width:600px; border-collapse:collapse; }
.degree-audit th,.degree-audit td { padding:.56rem .75rem; border-bottom:1px solid #e3eaf1; text-align:left; vertical-align:middle; }
.degree-audit th { background:#f2f6fa; color:#334d6a; font-size:.75rem; font-weight:750; }
.degree-audit td { font-size:.88rem; }
.degree-audit tbody tr:last-child td { border-bottom:0; }
.degree-audit .da-code { font-weight:750; white-space:nowrap; }
.degree-audit .da-number { font-variant-numeric:tabular-nums; }
.degree-audit .da-status { font-weight:700; white-space:nowrap; }
.degree-audit .is-complete { color:#176236; }
.degree-audit .is-progress { color:#825c08; }
.degree-audit .is-failed { color:#9b2636; }
.degree-audit .is-withdrawn { color:#596a7e; }
.degree-audit .is-registered { color:#24598b; }
.degree-audit .is-pending { color:#314b68; }
.degree-audit .da-notice { display:flex; align-items:flex-start; gap:.6rem; margin:.7rem .85rem; padding:.65rem .75rem; border-radius:7px; background:#eaf2fc; color:#193a60; font-size:.88rem; }
.degree-audit .da-empty { padding:.8rem .9rem; color:var(--da-muted); }
.degree-audit .visually-hidden { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
@media(max-width:900px) { .degree-audit .da-summary-identity { grid-template-columns:repeat(2,minmax(0,1fr)); row-gap:.8rem; } .degree-audit .da-identity-item:nth-child(2) { border-right:0; } }
@media(max-width:560px) { .degree-audit .da-summary-head { align-items:flex-start; } .degree-audit .da-summary-identity { grid-template-columns:1fr; gap:.65rem; } .degree-audit .da-identity-item,.degree-audit .da-identity-item:first-child { padding:0 0 .6rem; border-right:0; border-bottom:1px solid var(--da-line); } .degree-audit .da-identity-item:last-child { padding-bottom:0; border-bottom:0; } .degree-audit .da-metrics { grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem; padding:.2rem .65rem .75rem; } .degree-audit .da-metric { min-height:66px; gap:.45rem; padding:.55rem; } .degree-audit .da-metric-mark { width:30px; height:30px; flex-basis:30px; } .degree-audit .da-metric strong { font-size:1rem; } .degree-audit .da-requirement-head { align-items:flex-start; } }
</style>

<div class="degree-audit">
	<p class="da-intro">Track your program requirements and completed coursework.</p>

	<?php if (!$studentAuditProfile): ?>
		<section class="da-summary"><div class="da-summary-head"><h2>Academic Summary</h2></div><p class="da-empty">Student record unavailable. Please contact the Registrar’s Office.</p></section>
	<?php else: ?>
		<section class="da-summary" aria-labelledby="academic-summary-title">
			<div class="da-summary-head">
				<h2 id="academic-summary-title">Academic Summary</h2>
				<form class="da-picker" method="get" action="degree-audit.php">
					<label for="audit-semester">Select audit semester</label>
					<select id="audit-semester" name="semester" onchange="this.form.submit()">
						<?php foreach ($studentAuditSemesters as $term): ?><option value="<?= (int)$term['semester_ID'] ?>" <?= (int)$studentAuditSemesterID === (int)$term['semester_ID'] ? 'selected' : '' ?>><?= e($term['semester_Name']) ?></option><?php endforeach; ?>
					</select>
					<noscript><button type="submit" class="btn">View</button></noscript>
				</form>
			</div>
			<div class="da-summary-identity">
				<div class="da-identity-item"><span class="da-label">Student</span><strong class="da-value"><?= e($studentAuditFullName ?: 'Name unavailable') ?></strong><span class="da-subvalue">ID: <?= e($studentAuditProfile['user_ID'] ?? $studentID) ?></span></div>
				<div class="da-identity-item"><span class="da-label"><?= ($studentAuditProfile['student_Type'] ?? '') === 'Graduate' ? 'Major' : 'Primary Major' ?></span><strong class="da-value"><?= e(($studentAuditProfile['student_Type'] ?? '') === 'Graduate' ? 'Not applicable' : ($studentAuditProfile['major_Name'] ?? 'Major needs review')) ?></strong><span class="da-subvalue">Degree Program: <?= e($studentAuditDegreeProgram) ?></span><?php if ($studentAuditMajorCandidates): ?><span class="da-subvalue">Major candidates: <?= e(implode(', ', $studentAuditMajorCandidates)) ?></span><?php elseif ($studentAuditDeclaredMajors): ?><span class="da-subvalue">Additional majors: <?= e(implode(', ', $studentAuditDeclaredMajors)) ?></span><?php endif; ?><?php if ($studentAuditProgramReviewMessage): ?><span class="da-subvalue"><?= e($studentAuditProgramReviewMessage) ?></span><?php endif; ?></div>
				<div class="da-identity-item"><span class="da-label">Declared Minor<?= count($studentAuditDeclaredMinors) === 1 ? '' : 's' ?></span><strong class="da-value"><?= e($studentAuditDeclaredMinors ? implode(', ', $studentAuditDeclaredMinors) : 'None declared') ?></strong></div>
				<div class="da-identity-item"><span class="da-label">Academic Standing</span><strong class="da-value da-standing"><?= e($studentAuditAcademicStanding) ?></strong></div>
			</div>
			<div class="da-metrics" aria-label="Credit summary">
				<div class="da-metric"><span class="da-metric-mark" aria-hidden="true">E</span><div><span class="da-label">Earned Credits</span><strong><?= e($studentAuditEarnedCredits) ?></strong></div></div>
				<div class="da-metric"><span class="da-metric-mark" aria-hidden="true">R</span><div><span class="da-label">Registered Credits</span><strong><?= e($studentAuditSemesterStatus['registered_credits'] ?? 0) ?></strong><span class="da-subvalue"><?= e($studentAuditSemesterName) ?></span></div></div>
				<div class="da-metric"><span class="da-metric-mark" aria-hidden="true">Σ</span><div><span class="da-label">Distinct Total Credits</span><strong><?= e($studentAuditTotalCredits) ?></strong></div></div>
				<div class="da-metric"><span class="da-metric-mark" aria-hidden="true">FT</span><div><span class="da-label">Enrollment Status</span><strong><?= e($studentAuditSemesterStatus['status'] ?? 'Unavailable') ?></strong></div></div>
			</div>
		</section>

		<section class="da-requirement" aria-labelledby="semester-course-status-title">
			<div class="da-requirement-head"><h2 id="semester-course-status-title"><?= e($studentAuditSemesterName ?: 'Selected Semester') ?> — Course Status</h2><span class="da-count"><?= count($studentAuditCurrentCourseStatuses) ?> Courses</span></div>
			<?php if ($studentAuditCurrentCourseStatuses === []): ?>
				<p class="da-empty">No courses registered for <?= e($studentAuditSemesterName ?: 'this semester') ?>.</p>
			<?php else: ?>
				<div class="da-table-wrap" role="region" aria-label="<?= e($studentAuditSemesterName) ?> course statuses" tabindex="0">
					<table>
						<thead><tr><th scope="col">Course</th><th scope="col">Title</th><th scope="col">Credits</th><th scope="col">Final Grade</th><th scope="col">Status</th></tr></thead>
						<tbody>
						<?php foreach ($studentAuditCurrentCourseStatuses as $course): ?>
							<?php $gradeDisplay = $studentAuditSemesterName === 'Spring 2027' && empty($course['grade_from_history']) ? '' : trim((string) ($course['Grade'] ?? '')); ?>
							<tr>
								<td class="da-code"><?= e($course['course_ID']) ?></td>
								<td><?= e($course['course_Name']) ?></td>
								<td class="da-number"><?= e($course['course_Credits']) ?></td>
								<td><?= e($gradeDisplay !== '' ? $gradeDisplay : '—') ?></td>
								<td><span class="da-status <?= e($statusClass((string) $course['status'])) ?>"><?= e($course['status']) ?></span></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</section>

		<section class="da-requirement" aria-labelledby="major-requirements-title">
			<div class="da-requirement-head"><h2 id="major-requirements-title">Major Requirements<?= $majorAudits ? ' — ' . e(implode(', ', array_column($majorAudits, 'name'))) : '' ?></h2><?php if ($majorAudits): ?><span class="da-count"><?= array_sum(array_column($majorAudits, 'total')) ?> Requirements</span><?php endif; ?></div>
			<?php if ($studentAuditGraduateMessage !== null): ?><p class="da-empty">Graduate degree requirements are not configured for this program.</p><?php elseif (!$majorAudits): ?><p class="da-empty">No primary or declared major is recorded.</p><?php else: foreach ($majorAudits as $audit): ?>
				<?php if ($audit['total'] === 0): ?><p class="da-empty"><?= e($audit['name']) ?>: Requirements Not Published.</p><?php else: ?><h3 class="visually-hidden"><?= e($audit['name']) ?><?= $audit['primary'] ? ' (Primary Major)' : '' ?></h3><?php $renderRequirementTable($audit, $audit['name'] . ' major requirements'); ?><?php endif; ?>
			<?php endforeach; endif; ?>
		</section>

		<section class="da-requirement" aria-labelledby="minor-requirements-title">
			<div class="da-requirement-head"><h2 id="minor-requirements-title">Minor Requirements<?= $minorAudits ? ' — ' . e(implode(', ', array_column($minorAudits, 'name'))) : '' ?></h2><?php if ($minorAudits): ?><span class="da-count"><?= array_sum(array_column($minorAudits, 'total')) ?> Requirements</span><?php endif; ?></div>
			<?php if (!$studentAuditDeclaredMinors): ?><p class="da-empty">No minor declared.</p><?php else: foreach ($minorAudits as $audit): ?>
				<?php if ($audit['total'] === 0): ?><p class="da-empty"><?= e($audit['name']) ?>: Requirements Not Published.</p><?php else: ?><h3 class="visually-hidden"><?= e($audit['name']) ?> minor requirements</h3><?php $renderRequirementTable($audit, $audit['name'] . ' minor requirements'); ?><?php endif; ?>
			<?php endforeach; endif; ?>
		</section>

		<section class="da-requirement" aria-labelledby="general-education-title">
			<div class="da-requirement-head"><h2 id="general-education-title">General Education Requirements</h2></div>
			<p class="da-notice"><span aria-hidden="true">ⓘ</span><span>General Education course mappings are not published in the academic requirements tables.</span></p>
		</section>
	<?php endif; ?>
</div>

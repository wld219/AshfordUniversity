<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];
$transcriptStudent = one(
	$pdo,
	"SELECT u.user_ID, u.first_Name, u.middle_Name, u.last_Name,
			st.student_Type, st.major_ID, m.major_Name AS stored_major_Name,
			gs.Program AS graduate_Program
	 FROM Student st
	 JOIN User u ON u.user_ID = st.student_ID
	 LEFT JOIN Major m ON m.major_ID = st.major_ID
	 LEFT JOIN Graduate_Student gs ON gs.student_ID = st.student_ID
	 WHERE st.student_ID = ? AND u.user_Type = 'Student'",
	[$studentID]
);

$transcriptName = trim(implode(' ', array_filter([
	(string) ($transcriptStudent['first_Name'] ?? ''),
	(string) ($transcriptStudent['middle_Name'] ?? ''),
	(string) ($transcriptStudent['last_Name'] ?? ''),
], static fn(string $part): bool => $part !== '')));
$transcriptName = $transcriptName !== '' ? $transcriptName : 'Student record unavailable';
$academicLevel = (string) ($transcriptStudent['student_Type'] ?? 'Unavailable');
$studentAcademicProgram = resolve_student_academic_program($pdo, $studentID);
$majorDisplay = (string) $studentAcademicProgram['major_display'];
$minorDisplay = (string) $studentAcademicProgram['minor_display'];
$degreeProgram = $academicLevel === 'Graduate'
	? (string) $studentAcademicProgram['degree_program_display']
	: '';
$additionalMajorNames = $studentAcademicProgram['additional_major_names'];
$majorCandidates = $studentAcademicProgram['candidate_major_names'];
$programReviewMessage = $studentAcademicProgram['review_message'];

// Historical transcript rows and GPA continue to use Student_History. This
// preserves the current grade scale, repeated-attempt behavior, and records
// whose linked course section has since been canceled.
$historyRows = all_rows(
	$pdo,
	"SELECT sh.CRN, sh.course_ID, sh.semester_ID, sh.Grade,
			c.course_Name, c.course_Credits,
			s.semester_Name, s.semester_Start
	 FROM Student_History sh
	 JOIN Course c ON c.course_ID = sh.course_ID
	 JOIN Semester s ON s.semester_ID = sh.semester_ID
	 WHERE sh.student_ID = ?
	 ORDER BY s.semester_Start DESC, s.semester_ID DESC, c.course_ID, sh.CRN",
	[$studentID]
);

$portalSemesters = student_portal_semesters($pdo);
$targetSemesters = [];
$semesterGroups = [];
foreach ($portalSemesters as $semester) {
	$semesterID = (int) $semester['semester_ID'];
	$semesterName = (string) $semester['semester_Name'];
	$semesterStart = (string) ($semester['semester_Start'] ?? '');
	$targetSemesters[$semesterID] = [
		'semester_ID' => $semesterID,
		'semester_Name' => $semesterName,
		'semester_Start' => $semesterStart,
	];
	$semesterGroups[$semesterID] = [
		'semester_ID' => $semesterID,
		'semester_Name' => $semesterName,
		'semester_Start' => $semesterStart,
		'items' => [],
		'attempted' => 0,
		'earned' => 0,
		'gradePoints' => 0.0,
		'gradedCredits' => 0,
		'activeCount' => 0,
		'activeUngraded' => 0,
	];
}

// Only real, non-canceled enrollments for Fall 2026 and Spring 2027 are
// added beside academic history. The semester IDs come from Semester by name.
$registrationRows = [];
$targetSemesterIDs = array_keys($targetSemesters);
if ($targetSemesterIDs !== []) {
	$marks = implode(',', array_fill(0, count($targetSemesterIDs), '?'));
	$registrationRows = all_rows(
		$pdo,
		"SELECT e.CRN, e.semester_ID, e.Grade AS enrollment_Grade,
				cs.section_No, c.course_ID, c.course_Name, c.course_Credits,
				s.semester_Name, s.semester_Start
		 FROM Enrollment e
		 JOIN Course_Section cs
			ON cs.CRN = e.CRN
			AND cs.semester_ID = e.semester_ID
			AND cs.is_Cancelled = 0
		 JOIN Course c ON c.course_ID = cs.course_ID
		 JOIN Semester s ON s.semester_ID = e.semester_ID
		 WHERE e.student_ID = ? AND e.semester_ID IN ($marks)
		 ORDER BY s.semester_ID, c.course_ID, cs.section_No, e.CRN",
		array_merge([$studentID], $targetSemesterIDs)
	);
}
$registrationsByTermCRN = [];
foreach ($registrationRows as $registration) {
	$registrationsByTermCRN[(int) $registration['semester_ID']][(int) $registration['CRN']] = $registration;
}

$historyByTermCRN = [];
$historyAttemptedCredits = 0;
$historyEarnedCredits = 0;
$cumulativeGradePoints = 0.0;
$cumulativeGradedCredits = 0;
foreach ($historyRows as $course) {
	$semesterID = (int) $course['semester_ID'];
	$crn = (int) $course['CRN'];
	$grade = trim((string) ($course['Grade'] ?? ''));
	$historyByTermCRN[$semesterID][$crn] = $grade;

	// If a target-term history row has no grade but a live registration exists,
	// show the actual registration state once instead of duplicating the CRN.
	if ($grade === '' && isset($registrationsByTermCRN[$semesterID][$crn])) {
		continue;
	}

	if (!isset($semesterGroups[$semesterID])) {
		$semesterGroups[$semesterID] = [
			'semester_ID' => $semesterID,
			'semester_Name' => (string) $course['semester_Name'],
			'semester_Start' => (string) ($course['semester_Start'] ?? ''),
			'items' => [],
			'attempted' => 0,
			'earned' => 0,
			'gradePoints' => 0.0,
			'gradedCredits' => 0,
			'activeCount' => 0,
			'activeUngraded' => 0,
		];
	}

	$credits = (int) $course['course_Credits'];
	$points = $grade !== '' ? grade_points($grade) : null;
	$qualityPoints = $points !== null ? $points * $credits : null;
	$courseStatus = student_course_status($grade, (string) $course['semester_Name'], false);
	$semesterGroups[$semesterID]['items'][] = [
		'CRN' => $crn,
		'course_ID' => (string) $course['course_ID'],
		'course_Name' => (string) $course['course_Name'],
		'credits' => $credits,
		'grade' => $grade !== '' ? $grade . ' · ' . $courseStatus : 'Grade not posted',
		'quality_points' => $qualityPoints,
		'kind' => $courseStatus,
	];
	$semesterGroups[$semesterID]['attempted'] += $credits;
	$historyAttemptedCredits += $credits;
	if ($points !== null) {
		$semesterGroups[$semesterID]['gradePoints'] += $qualityPoints;
		$semesterGroups[$semesterID]['gradedCredits'] += $credits;
		$cumulativeGradePoints += $qualityPoints;
		$cumulativeGradedCredits += $credits;
		if ($points >= 1.0) {
			$semesterGroups[$semesterID]['earned'] += $credits;
			$historyEarnedCredits += $credits;
		}
	}
}

foreach ($registrationRows as $registration) {
	$semesterID = (int) $registration['semester_ID'];
	$crn = (int) $registration['CRN'];
	$historyGradeExists = array_key_exists($crn, $historyByTermCRN[$semesterID] ?? []);
	if ($historyGradeExists && trim((string) $historyByTermCRN[$semesterID][$crn]) !== '') {
		continue;
	}
	if (!isset($semesterGroups[$semesterID])) {
		continue;
	}

	$semesterName = (string) $registration['semester_Name'];
	$credits = (int) $registration['course_Credits'];
	$grade = $semesterName === 'Spring 2027'
		? ''
		: trim((string) ($registration['enrollment_Grade'] ?? ''));
	$qualityPoints = null;
	$rowStatus = student_course_status($grade, $semesterName, true);
	$points = $grade !== '' ? grade_points($grade) : null;
	if (in_array($rowStatus, ['Completed', 'Failed'], true) && $points !== null) {
		$qualityPoints = $points * $credits;
		$semesterGroups[$semesterID]['gradePoints'] += $qualityPoints;
		$semesterGroups[$semesterID]['gradedCredits'] += $credits;
		if ($rowStatus === 'Completed') {
			$semesterGroups[$semesterID]['earned'] += $credits;
		}
	}
	if ($rowStatus === 'In Progress') {
		$semesterGroups[$semesterID]['activeUngraded']++;
	}

	$semesterGroups[$semesterID]['items'][] = [
		'CRN' => $crn,
		'course_ID' => (string) $registration['course_ID'],
		'course_Name' => (string) $registration['course_Name'],
		'credits' => $credits,
		'grade' => $grade !== '' ? $grade . ' · ' . $rowStatus : $rowStatus,
		'quality_points' => $qualityPoints,
		'kind' => $rowStatus,
	];
	$semesterGroups[$semesterID]['attempted'] += $credits;
	$semesterGroups[$semesterID]['activeCount']++;
}

$cumulativeGPA = $cumulativeGradedCredits > 0
	? round($cumulativeGradePoints / $cumulativeGradedCredits, 2)
	: null;
$academicStanding = 'Not available';
if ($cumulativeGPA !== null) {
	if ($cumulativeGPA >= 3.5) {
		$academicStanding = 'Dean\'s List';
	} elseif ($cumulativeGPA >= 2.0) {
		$academicStanding = 'Good Standing';
	} else {
		$academicStanding = 'Academic Probation';
	}
}

$semesterGroups = array_values($semesterGroups);
usort($semesterGroups, static function (array $a, array $b): int {
	$priority = ['Fall 2026' => 0, 'Spring 2027' => 1];
	$aPriority = $priority[$a['semester_Name']] ?? 2;
	$bPriority = $priority[$b['semester_Name']] ?? 2;
	if ($aPriority !== $bPriority) {
		return $aPriority <=> $bPriority;
	}
	if ($aPriority === 2 && $a['semester_Start'] !== $b['semester_Start']) {
		return strcmp((string) $b['semester_Start'], (string) $a['semester_Start']);
	}
	return (int) $b['semester_ID'] <=> (int) $a['semester_ID'];
});

$generatedAt = date('M j, Y \a\t g:i A');
page_start('Unofficial Transcript', 'Student', 'transcript.php');
?>

<style>
.transcript-page {
	--transcript-navy: #0b2345;
	--transcript-border: #d7e0eb;
	--transcript-muted: #53657d;
	color: #17263c;
}
.transcript-page, .transcript-page * { box-sizing: border-box; }
.transcript-page .transcript-topline {
	display: flex;
	align-items: end;
	justify-content: space-between;
	gap: 16px;
	margin: -12px 0 16px;
}
.transcript-page .transcript-subtitle { margin: 0; color: var(--transcript-muted); }
.transcript-page .transcript-actions { display: flex; flex-wrap: wrap; gap: 8px; }
.transcript-page .transcript-action {
	min-height: 42px;
	padding: 8px 13px;
	color: var(--transcript-navy);
	background: #fff;
	border: 1px solid #9aacc2;
	border-radius: 7px;
	font: inherit;
	font-weight: 700;
	cursor: pointer;
}
.transcript-page .transcript-action.primary { color: #fff; background: var(--transcript-navy); border-color: var(--transcript-navy); }
.transcript-page .transcript-action:hover { filter: brightness(.96); }
.transcript-page .transcript-action:focus-visible { outline: 3px solid #d8aa35; outline-offset: 2px; }
.transcript-page .pdf-hint { margin: 6px 0 0; color: #53657d; font-size: 12px; text-align: right; }
.transcript-page .transcript-identity {
	display: grid;
	grid-template-columns: repeat(6, minmax(0, 1fr));
	gap: 0;
	margin-bottom: 14px;
	padding: 14px 18px;
	color: #fff;
	background: var(--transcript-navy);
	border-radius: 9px;
}
.transcript-page .identity-item { min-width: 0; padding: 2px 14px; border-left: 1px solid rgba(255,255,255,.35); }
.transcript-page .identity-item:first-child { padding-left: 0; border-left: 0; }
.transcript-page .identity-label { display: block; margin-bottom: 4px; color: #dce7f4; font-size: 12px; font-weight: 650; }
.transcript-page .identity-value { display: block; color: #fff; font-size: 15px; font-weight: 750; overflow-wrap: anywhere; }
.transcript-page .generated-strip {
	margin-bottom: 14px;
	padding: 10px 14px;
	color: #183456;
	background: #e8eff8;
	border: 1px solid #d4e0ef;
	border-radius: 7px;
	font-size: 14px;
}
.transcript-page .generated-strip strong { margin-right: 7px; color: var(--transcript-navy); }
.transcript-page .term-card {
	margin: 0 0 14px;
	overflow: hidden;
	background: #fff;
	border: 1px solid var(--transcript-border);
	border-radius: 9px;
	box-shadow: 0 2px 7px rgba(11,35,69,.04);
}
.student-portal-theme .content .transcript-page .term-heading {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	margin: 0;
	padding: 9px 14px;
	color: #fff;
	background: var(--transcript-navy);
	font-size: 17px;
	line-height: 1.3;
}
.transcript-page .term-badge {
	flex: 0 0 auto;
	padding: 4px 9px;
	color: #12335b;
	background: #dcecff;
	border-radius: 999px;
	font-size: 12px;
	font-weight: 750;
}
.transcript-page .table-wrap { max-width: 100%; overflow-x: auto; }
.transcript-page .transcript-table { width: 100%; min-width: 640px; border-collapse: collapse; }
.transcript-page .transcript-table th {
	padding: 8px 11px;
	color: #142746;
	background: #edf2f8;
	border-bottom: 1px solid #d8e2ee;
	text-align: left;
	font-size: 12px;
	font-weight: 750;
	white-space: nowrap;
}
.transcript-page .transcript-table td {
	padding: 8px 11px;
	color: #20344f;
	border-bottom: 1px solid #e4eaf2;
	font-size: 13px;
	vertical-align: top;
}
.transcript-page .transcript-table tbody tr:last-child td { border-bottom: 0; }
.transcript-page .transcript-table .course-code { color: #123c70; font-weight: 800; white-space: nowrap; }
.transcript-page .transcript-table .numeric { text-align: center; white-space: nowrap; }
.transcript-page .course-state { color: #405875; font-style: italic; }
.transcript-page .course-status-completed { color: #176236; font-weight: 700; }
.transcript-page .course-status-failed { color: #9b2636; font-weight: 750; }
.transcript-page .course-status-withdrawn { color: #596a7e; font-weight: 700; }
.transcript-page .course-status-in-progress,
.transcript-page .course-status-registered { color: #405875; font-style: italic; }
.transcript-page .term-totals {
	display: flex;
	flex-wrap: wrap;
	justify-content: space-between;
	gap: 8px 18px;
	padding: 9px 14px;
	color: #142746;
	background: #e6eef8;
	font-size: 13px;
	font-weight: 700;
}
.transcript-page .term-empty { margin: 0; padding: 16px; color: #53657d; background: #f8fafc; }
.transcript-page .cumulative-summary {
	display: grid;
	grid-template-columns: minmax(160px, .85fr) repeat(3, minmax(0, 1fr));
	align-items: stretch;
	margin: 16px 0;
	overflow: hidden;
	background: #e8eff8;
	border: 1px solid #d2deec;
	border-radius: 9px;
}
.student-portal-theme .content .transcript-page .cumulative-title {
	display: flex;
	align-items: center;
	margin: 0;
	padding: 14px 17px;
	color: #fff;
	background: var(--transcript-navy);
	font-size: 17px;
}
.transcript-page .cumulative-metric { display: grid; align-content: center; gap: 3px; padding: 12px 16px; text-align: center; }
.transcript-page .cumulative-metric + .cumulative-metric { border-left: 1px solid #b8c8dc; }
.transcript-page .cumulative-label { color: #344b68; font-size: 12px; font-weight: 650; }
.transcript-page .cumulative-value { color: #102746; font-size: 23px; font-weight: 800; line-height: 1.2; }
.transcript-page .transcript-disclaimer { margin: 12px 0 0; color: #53657d; font-size: 12px; }
.transcript-page .print-title { display: none; }
@media (max-width: 1000px) {
	.transcript-page .transcript-identity { grid-template-columns: repeat(3, minmax(0, 1fr)); row-gap: 10px; }
	.transcript-page .identity-item:nth-child(4) { padding-left: 0; border-left: 0; }
}
@media (max-width: 700px) {
	.transcript-page .transcript-topline { align-items: flex-start; flex-direction: column; }
	.transcript-page .transcript-actions { width: 100%; }
	.transcript-page .transcript-action { flex: 1; }
	.transcript-page .pdf-hint { text-align: left; }
	.transcript-page .transcript-identity { grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 12px; }
	.transcript-page .identity-item { padding: 4px 9px; }
	.transcript-page .identity-item:nth-child(odd) { padding-left: 0; border-left: 0; }
	.transcript-page .identity-item:nth-child(4) { padding-left: 9px; border-left: 1px solid rgba(255,255,255,.35); }
	.transcript-page .cumulative-summary { grid-template-columns: 1fr 1fr; }
	.transcript-page .cumulative-title { grid-column: 1 / -1; }
	.transcript-page .cumulative-metric:nth-child(3) { border-left: 1px solid #b8c8dc; }
}
@media (max-width: 420px) {
	.transcript-page .transcript-identity { grid-template-columns: 1fr; }
	.transcript-page .identity-item,
	.transcript-page .identity-item:nth-child(4),
	.transcript-page .identity-item:nth-child(odd) { padding: 7px 0; border: 0; border-bottom: 1px solid rgba(255,255,255,.25); }
	.transcript-page .identity-item:last-child { border-bottom: 0; }
	.transcript-page .term-heading { align-items: flex-start; flex-direction: column; }
	.transcript-page .term-totals { flex-direction: column; gap: 4px; }
}
@page { size: letter portrait; margin: .55in; }
@media print {
	html, body { background: #fff !important; color: #111 !important; }
	.topbar, .sidebar, #admin-sidebar-toggle, .transcript-actions,
	.pdf-hint, .content > h1, .alert, .transcript-disclaimer { display: none !important; }
	.shell { display: block !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
	.content { width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
	.transcript-page { color: #111 !important; }
	.transcript-page .print-title { display: block !important; margin: 0 0 12px; color: #111 !important; text-align: center; font-size: 15pt; }
	.transcript-page .transcript-topline { margin: 0 0 10px; }
	.transcript-page .transcript-subtitle { display: none; }
	.transcript-page .transcript-identity,
	.transcript-page .term-heading,
	.transcript-page .cumulative-title { color: #111 !important; background: #e6edf5 !important; border: 1px solid #777; }
	.transcript-page .identity-label, .transcript-page .identity-value,
	.transcript-page .term-heading, .transcript-page .cumulative-title,
	.transcript-page .term-badge { color: #111 !important; }
	.transcript-page .term-badge { background: #fff !important; border: 1px solid #777; }
	.transcript-page .term-card, .transcript-page .cumulative-summary { break-inside: avoid; box-shadow: none !important; }
	.transcript-page .table-wrap { overflow: visible !important; }
	.transcript-page .transcript-table { min-width: 0 !important; font-size: 9pt; }
	.transcript-page .transcript-table th, .transcript-page .transcript-table td { padding: 4px 6px !important; color: #111 !important; border: 1px solid #777 !important; }
	.transcript-page .term-totals { color: #111 !important; background: #f0f0f0 !important; border: 1px solid #777; }
	.transcript-page .cumulative-summary { background: #fff !important; border: 1px solid #777; }
	.transcript-page .cumulative-metric, .transcript-page .cumulative-label, .transcript-page .cumulative-value { color: #111 !important; }
	.transcript-page .cumulative-metric + .cumulative-metric { border-color: #777 !important; }
}
</style>

<div class="transcript-page">
	<h2 class="print-title">ASHFORD UNIVERSITY — UNOFFICIAL TRANSCRIPT</h2>
	<div class="transcript-topline">
		<p class="transcript-subtitle">Academic history and semester registration record · Unofficial</p>
		<div>
			<div class="transcript-actions" aria-label="Transcript output options">
				<button class="transcript-action" type="button" onclick="window.print()">Print</button>
				<button class="transcript-action primary" type="button" onclick="window.print()" title="Opens the print dialog; choose Save as PDF to download.">Download PDF</button>
			</div>
			<p class="pdf-hint">Download PDF opens your browser’s Save as PDF option.</p>
		</div>
	</div>

	<section class="transcript-identity" aria-label="Student transcript identity">
		<div class="identity-item"><span class="identity-label">Student Name</span><strong class="identity-value"><?= e($transcriptName) ?></strong></div>
		<div class="identity-item"><span class="identity-label">Student ID</span><strong class="identity-value"><?= e((string) ($transcriptStudent['user_ID'] ?? $studentID)) ?></strong></div>
		<div class="identity-item"><span class="identity-label">Academic Level</span><strong class="identity-value"><?= e($academicLevel) ?></strong></div>
		<div class="identity-item"><span class="identity-label">Major</span><strong class="identity-value"><?= e($majorDisplay) ?></strong></div>
		<div class="identity-item"><span class="identity-label">Minor</span><strong class="identity-value"><?= e($minorDisplay) ?></strong></div>
		<div class="identity-item"><span class="identity-label"><?= $academicLevel === 'Graduate' ? 'Degree Program' : 'Academic Standing' ?></span><strong class="identity-value"><?= e($academicLevel === 'Graduate' ? ($degreeProgram !== '' ? $degreeProgram : 'Not recorded') : $academicStanding) ?></strong></div>
		<?php if ($academicLevel === 'Graduate'): ?>
			<div class="identity-item"><span class="identity-label">Academic Standing</span><strong class="identity-value"><?= e($academicStanding) ?></strong></div>
		<?php endif; ?>
		<?php if ($additionalMajorNames): ?><div class="identity-item"><span class="identity-label">Additional Majors</span><strong class="identity-value"><?= e(implode(', ', $additionalMajorNames)) ?></strong></div><?php endif; ?>
		<?php if ($majorCandidates): ?><div class="identity-item"><span class="identity-label">Major Candidates</span><strong class="identity-value"><?= e(implode(', ', $majorCandidates)) ?></strong></div><?php endif; ?>
		<?php if ($programReviewMessage): ?><div class="identity-item"><span class="identity-label">Program Review</span><strong class="identity-value"><?= e($programReviewMessage) ?></strong></div><?php endif; ?>
	</section>

	<div class="generated-strip"><strong>Transcript Generated:</strong> <time datetime="<?= e(date(DATE_ATOM)) ?>"><?= e($generatedAt) ?></time></div>

	<?php if ($historyRows === [] && $registrationRows === []): ?>
		<section class="term-card" role="status"><p class="term-empty">No academic history or registrations are available for this student.</p></section>
	<?php endif; ?>

	<?php foreach ($semesterGroups as $term): ?>
		<?php
			$termGPA = $term['gradedCredits'] > 0 && $term['activeUngraded'] === 0
				? round($term['gradePoints'] / $term['gradedCredits'], 2)
				: null;
			$termStatus = 'Academic History';
			if ($term['semester_Name'] === 'Fall 2026') {
				if ($term['activeCount'] > 0) {
					$termStatus = $term['activeUngraded'] > 0 ? 'In Progress' : 'Grades Posted';
				} elseif ($term['items'] === []) {
					$termStatus = 'No Record';
				}
			} elseif ($term['semester_Name'] === 'Spring 2027') {
				if ($term['activeCount'] > 0) {
					$termStatus = 'Registered';
				} elseif ($term['items'] === []) {
					$termStatus = 'No Record';
				}
			} elseif ($term['items'] === []) {
				$termStatus = 'No Record';
			}
		?>
		<section class="term-card" aria-label="<?= e($term['semester_Name']) ?> transcript records">
			<h2 class="term-heading"><span><?= e($term['semester_Name']) ?></span><span class="term-badge"><?= e($termStatus) ?></span></h2>
			<?php if ($term['items'] !== []): ?>
				<div class="table-wrap" role="region" aria-label="<?= e($term['semester_Name']) ?> courses" tabindex="0">
					<table class="transcript-table">
						<thead><tr><th scope="col">Course Code</th><th scope="col">Course Title</th><th scope="col">Credits</th><th scope="col">Final Grade / Status</th><th scope="col">Quality Points</th></tr></thead>
						<tbody>
						<?php foreach ($term['items'] as $item): ?>
							<tr>
								<td class="course-code"><?= e($item['course_ID']) ?></td>
								<td><?= e($item['course_Name']) ?></td>
								<td class="numeric"><?= e((string) $item['credits']) ?></td>
								<td class="course-status-<?= e(strtolower(str_replace(' ', '-', $item['kind']))) ?>"><?= e($item['grade']) ?></td>
								<td class="numeric"><?= $item['quality_points'] !== null ? e(number_format((float) $item['quality_points'], 2)) : '—' ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else: ?>
				<p class="term-empty"><?= $termStatus === 'No Record' ? 'No courses registered or completed for this semester.' : 'No course history is recorded for this semester.' ?></p>
			<?php endif; ?>
			<div class="term-totals">
				<span>Term Totals</span>
				<span>Attempted: <?= e((string) $term['attempted']) ?></span>
				<span>Earned: <?= e((string) $term['earned']) ?></span>
				<span>Term GPA: <?= $termGPA !== null ? e(number_format($termGPA, 2)) : 'N/A' ?></span>
			</div>
		</section>
	<?php endforeach; ?>

	<section class="cumulative-summary" aria-label="Cumulative transcript summary">
		<h2 class="cumulative-title">Cumulative Summary</h2>
		<div class="cumulative-metric"><span class="cumulative-label">Total Attempted</span><strong class="cumulative-value"><?= e((string) $historyAttemptedCredits) ?></strong></div>
		<div class="cumulative-metric"><span class="cumulative-label">Total Earned</span><strong class="cumulative-value"><?= e((string) $historyEarnedCredits) ?></strong></div>
		<div class="cumulative-metric"><span class="cumulative-label">Cumulative GPA</span><strong class="cumulative-value"><?= $cumulativeGPA !== null ? e(number_format($cumulativeGPA, 2)) : 'N/A' ?></strong></div>
	</section>
	<p class="transcript-disclaimer">Unofficial transcript for student reference. Official records are issued by the Registrar.</p>
</div>

<?php page_end(); ?>

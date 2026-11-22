<?php
declare(strict_types=1);

$semesterStatusLabel = (string) ($semesterEnrollmentStatus['status'] ?? 'Unavailable');
$registeredCreditCount = (int) ($semesterEnrollmentStatus['registered_credits'] ?? 0);
$holdCount = count($holds);
?>

<style>
.student-overview {
	--overview-navy: #0b2345;
	--overview-navy-soft: #17385f;
	--overview-border: #d7e0eb;
	--overview-muted: #53657d;
	color: #17263c;
}
.student-overview, .student-overview * { box-sizing: border-box; }
.student-overview .overview-intro { margin: -12px 0 16px; color: var(--overview-muted); }
.student-overview .welcome-banner {
	display: grid;
	grid-template-columns: minmax(0, 1.35fr) repeat(2, minmax(150px, .65fr));
	align-items: center;
	gap: 20px;
	margin-bottom: 16px;
	padding: 20px 24px;
	color: #fff;
	background: var(--overview-navy);
	border-radius: 10px;
}
.student-overview .welcome-person { min-width: 0; }
.student-overview .welcome-kicker { margin: 0 0 4px; color: #d9e5f4; font-size: 14px; font-weight: 650; }
.student-portal-theme .content .student-overview .welcome-name { margin: 0; color: #fff; font-size: clamp(22px, 2.3vw, 30px); line-height: 1.15; overflow-wrap: anywhere; }
.student-overview .welcome-id { margin: 8px 0 0; color: #e4ebf5; }
.student-overview .term-block { min-width: 0; padding-left: 18px; border-left: 1px solid rgba(255,255,255,.38); }
.student-overview .term-label { display: block; margin-bottom: 8px; color: #d9e5f4; font-size: 13px; }
.student-overview .term-value { display: inline-block; padding: 7px 11px; color: var(--overview-navy); background: #fff; border-radius: 6px; font-weight: 750; }
.student-overview .overview-grid {
	display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: 16px;
	margin-bottom: 16px;
}
.student-overview .overview-card {
	min-width: 0;
	background: #fff;
	border: 1px solid var(--overview-border);
	border-radius: 9px;
	box-shadow: 0 2px 7px rgba(11,35,69,.045);
	overflow: hidden;
}
.student-portal-theme .content .student-overview .overview-card-title {
	margin: 0;
	padding: 10px 15px;
	color: #fff;
	background: var(--overview-navy);
	font-size: 17px;
	line-height: 1.3;
}
.student-overview .overview-rows { padding: 4px 16px 8px; }
.student-overview .overview-row {
	display: grid;
	grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
	gap: 12px;
	align-items: start;
	padding: 9px 0;
	border-bottom: 1px solid #e7edf4;
}
.student-overview .overview-row:last-child { border-bottom: 0; }
.student-overview .overview-label { color: #344761; font-size: 14px; font-weight: 650; }
.student-overview .term-context { color: #697b90; font-size: 12px; font-weight: 500; }
.student-overview .overview-value { color: #10294c; font-size: 14px; font-weight: 750; text-align: left; overflow-wrap: anywhere; }
.student-overview .status-pill {
	display: inline-block;
	padding: 4px 9px;
	border-radius: 5px;
	background: #e8f4eb;
	color: #185b2b;
	font-weight: 750;
}
.student-overview .status-pill.neutral { background: #edf1f6; color: #33465e; }
.student-overview .courses-card { margin-bottom: 18px; }
.student-overview .courses-heading {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 14px;
	padding-right: 12px;
}
.student-overview .semester-form { display: flex; align-items: center; gap: 9px; margin: 0; }
.student-overview .semester-form label { color: #fff; font-size: 14px; font-weight: 650; }
.student-overview .semester-form select {
	min-width: 142px;
	min-height: 40px;
	padding: 7px 30px 7px 10px;
	color: #142746;
	background: #fff;
	border: 1px solid #c6d3e3;
	border-radius: 6px;
	font: inherit;
	font-weight: 700;
}
.student-overview .semester-form select:focus-visible { outline: 3px solid #f0c75e; outline-offset: 2px; }
.student-overview .course-table-wrap { overflow-x: auto; }
.student-overview .course-table { width: 100%; min-width: 600px; border-collapse: collapse; }
.student-overview .course-table th,
.student-overview .course-table td { padding: 10px 14px; border-bottom: 1px solid #e4eaf2; text-align: left; }
.student-overview .course-table th { color: #142746; background: #eff3f8; font-size: 13px; font-weight: 750; }
.student-overview .course-table td { color: #20344f; font-size: 14px; }
.student-overview .course-table tbody tr:last-child td { border-bottom: 0; }
.student-overview .course-code { color: #123c70; font-weight: 750; white-space: nowrap; }
.student-overview .empty-courses { margin: 0; padding: 22px 16px; color: #465a74; background: #f7f9fc; }
@media (max-width: 760px) {
	.student-overview .welcome-banner { grid-template-columns: 1fr 1fr; }
	.student-overview .welcome-person { grid-column: 1 / -1; }
	.student-overview .term-block:first-of-type { padding-left: 0; border-left: 0; }
	.student-overview .overview-grid { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
	.student-overview .welcome-banner { grid-template-columns: 1fr; gap: 14px; padding: 17px; }
	.student-overview .welcome-person { grid-column: auto; }
	.student-overview .term-block { padding: 0; border: 0; }
	.student-overview .courses-heading { align-items: flex-start; flex-direction: column; padding: 10px 14px; }
	.student-overview .courses-heading .overview-card-title { padding: 0; }
	.student-overview .semester-form { width: 100%; justify-content: space-between; }
	.student-overview .semester-form select { flex: 0 1 190px; }
	.student-overview .overview-rows { padding-inline: 12px; }
	.student-overview .overview-row { grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); gap: 8px; }
}
</style>

<div class="student-overview">
	<p class="overview-intro">Your academic information and current enrollment.</p>

	<section class="welcome-banner" aria-label="Student welcome and semester information">
		<div class="welcome-person">
			<p class="welcome-kicker">Welcome Back,</p>
			<h2 class="welcome-name"><?= e($displayName) ?></h2>
			<p class="welcome-id">Student ID: <?= e((string) ($student['user_ID'] ?? $studentID)) ?></p>
		</div>
		<div class="term-block">
			<span class="term-label">Current Semester</span>
			<span class="term-value"><?= e($currentSemesterLabel) ?></span>
		</div>
		<div class="term-block">
			<span class="term-label">Upcoming Semester</span>
			<span class="term-value"><?= e($upcomingSemesterLabel) ?></span>
		</div>
	</section>

	<div class="overview-grid">
		<section class="overview-card" aria-labelledby="academic-summary-title">
			<h2 class="overview-card-title" id="academic-summary-title">Academic Summary</h2>
			<div class="overview-rows">
				<div class="overview-row"><span class="overview-label">Academic Level</span><strong class="overview-value"><?= e($student['student_Type'] ?? 'Not available') ?></strong></div>
				<div class="overview-row"><span class="overview-label">Cumulative GPA</span><strong class="overview-value"><?= $gpa !== null ? e(number_format((float) $gpa, 2)) : 'Not available' ?></strong></div>
				<div class="overview-row"><span class="overview-label">Registered Credits <span class="term-context">(<?= e($semesterLabel) ?>)</span></span><strong class="overview-value"><?= e((string) $registeredCreditCount) ?></strong></div>
				<div class="overview-row"><span class="overview-label">Semester Enrollment Status <span class="term-context">(<?= e($semesterLabel) ?>)</span></span><strong class="overview-value"><span class="status-pill <?= $registeredCreditCount === 0 ? 'neutral' : '' ?>"><?= e($semesterStatusLabel) ?></span></strong></div>
				<div class="overview-row"><span class="overview-label"><?= e($studentProgramLabel) ?></span><strong class="overview-value"><?= e($studentProgramDisplay) ?></strong></div>
				<div class="overview-row"><span class="overview-label">Minor</span><strong class="overview-value"><?= e($studentMinorDisplay) ?></strong></div>
			</div>
		</section>

		<section class="overview-card" aria-labelledby="student-information-title">
			<h2 class="overview-card-title" id="student-information-title">Student Information</h2>
			<div class="overview-rows">
				<div class="overview-row"><span class="overview-label">Student ID</span><strong class="overview-value"><?= e((string) ($student['user_ID'] ?? 'Unavailable')) ?></strong></div>
				<div class="overview-row"><span class="overview-label">Account Status</span><strong class="overview-value"><span class="status-pill">Logged in</span></strong></div>
				<div class="overview-row"><span class="overview-label">Academic Advisor</span><strong class="overview-value"><?= e($advisorName) ?></strong></div>
				<div class="overview-row"><span class="overview-label">Registration Holds</span><strong class="overview-value"><span class="status-pill <?= $holdCount === 0 ? '' : 'neutral' ?>"><?= e($registrationHoldDisplay) ?></span></strong></div>
			</div>
		</section>
	</div>

	<section class="overview-card courses-card" aria-labelledby="current-courses-title">
		<div class="courses-heading">
			<h2 class="overview-card-title" id="current-courses-title">My Current Courses</h2>
			<form method="get" action="dashboard.php" class="semester-form">
				<label for="overview-semester">Semester:</label>
				<select id="overview-semester" name="semester" onchange="this.form.submit()">
					<?php foreach ($availableSemesters as $term): ?>
					<option value="<?= (int) $term['semester_ID'] ?>" <?= (int) $currentSemester === (int) $term['semester_ID'] ? 'selected' : '' ?>><?= e($term['semester_Name']) ?></option>
					<?php endforeach; ?>
				</select>
			</form>
		</div>
		<?php if ($schedule !== []): ?>
		<div class="course-table-wrap" role="region" aria-label="Registered courses for <?= e($semesterLabel) ?>" tabindex="0">
			<table class="course-table">
				<thead><tr><th scope="col">Course Code</th><th scope="col">Course Title</th><th scope="col">Section</th><th scope="col">Credits</th><th scope="col">Instructor</th></tr></thead>
				<tbody>
				<?php foreach ($schedule as $course): ?>
				<tr>
					<td class="course-code"><?= e($course['course_ID']) ?></td>
					<td><?= e($course['course_Name']) ?></td>
					<td><?= isset($course['section_No']) ? e(str_pad((string) $course['section_No'], 3, '0', STR_PAD_LEFT)) : '—' ?></td>
					<td><?= e((string) $course['course_Credits']) ?></td>
					<td><?= e($course['faculty_Name'] ?: 'Not assigned') ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php else: ?>
		<p class="empty-courses" role="status">No courses registered for <?= e($semesterLabel) ?>.</p>
		<?php endif; ?>
	</section>
</div>

<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_once '../includes/functions/faculty_teaching_load.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];
$semesterOptions = [];
$semesterById = [];
$faculty = null;
$departments = [];
$sections = [];
$workload = ['teaching_credits' => 0, 'assigned_sections' => 0];
$enrolledStudentCount = 0;
$enrollmentCountsByCRN = [];
$roomsByCRN = [];
$selectedSemesterID = null;
$selectedSemesterName = '';
$scheduleError = false;

try {
	// Keep the selector limited to the two published demonstration semesters.
	$semesterOptions = all_rows(
		$pdo,
		"SELECT semester_ID, semester_Name
		 FROM Semester
		 WHERE (semester_ID = 1 AND semester_Name = 'Fall 2026')
		    OR (semester_ID = 2 AND semester_Name = 'Spring 2027')
		 ORDER BY semester_ID"
	);
	foreach ($semesterOptions as $option) {
		$semesterById[(int) $option['semester_ID']] = $option;
	}

	$requestedSemesterID = filter_var(
		$_GET['semester'] ?? null,
		FILTER_VALIDATE_INT
	);
	$selectedSemesterID = is_int($requestedSemesterID)
		&& isset($semesterById[$requestedSemesterID])
		? $requestedSemesterID
		: (isset($semesterById[1]) ? 1 : null);
	$selectedSemesterName = $selectedSemesterID !== null
		? (string) $semesterById[$selectedSemesterID]['semester_Name']
		: '';

	$faculty = one(
		$pdo,
		"SELECT f.faculty_ID, f.Specialty, f.faculty_Type,
		        u.first_Name, u.middle_Name, u.last_Name
		 FROM Faculty f
		 JOIN User u ON u.user_ID = f.faculty_ID
		 WHERE f.faculty_ID = ? AND u.user_Type = 'Faculty'",
		[$facultyID]
	);

	if ($faculty !== null) {
		$departments = faculty_departments($pdo, $facultyID);
	}

	if ($selectedSemesterID !== null && $faculty !== null) {
		$sections = faculty_teaching_sections($pdo, $facultyID, $selectedSemesterID);
		$workload = faculty_teaching_workload_summary($sections);

		$enrollmentRows = all_rows(
			$pdo,
			"SELECT cs.CRN,
			        COUNT(DISTINCT CASE
			            WHEN e.student_ID IS NOT NULL
			             AND UPPER(TRIM(COALESCE(e.Grade, ''))) NOT IN ('W', 'WD', 'WITHDRAWN')
			            THEN e.student_ID
			        END) AS enrolled_count
			 FROM Course_Section cs
			 LEFT JOIN Enrollment e
			   ON e.CRN = cs.CRN
			  AND e.semester_ID = cs.semester_ID
			 WHERE cs.faculty_ID = ?
			   AND cs.semester_ID = ?
			   AND cs.is_Cancelled = 0
			 GROUP BY cs.CRN",
			[$facultyID, $selectedSemesterID]
		);
		foreach ($enrollmentRows as $row) {
			$enrollmentCountsByCRN[(int) $row['CRN']] = (int) $row['enrolled_count'];
		}

		$studentCountRow = one(
			$pdo,
			"SELECT COUNT(DISTINCT e.student_ID) AS enrolled_students
			 FROM Course_Section cs
			 JOIN Enrollment e
			   ON e.CRN = cs.CRN
			  AND e.semester_ID = cs.semester_ID
			 WHERE cs.faculty_ID = ?
			   AND cs.semester_ID = ?
			   AND cs.is_Cancelled = 0
			   AND UPPER(TRIM(COALESCE(e.Grade, ''))) NOT IN ('W', 'WD', 'WITHDRAWN')",
			[$facultyID, $selectedSemesterID]
		);
		$enrolledStudentCount = (int) ($studentCountRow['enrolled_students'] ?? 0);

		$roomRows = all_rows(
			$pdo,
			"SELECT cs.CRN,
			        CONCAT_WS(' ', NULLIF(lb.Bldg_Name, ''), NULLIF(lr.room_Number, '')) AS lecture_room,
			        CONCAT_WS(' ', NULLIF(lab_building.Bldg_Name, ''), NULLIF(lab_room.room_Number, '')) AS lab_room
			 FROM Course_Section cs
			 LEFT JOIN Lecture lecture ON lecture.lecture_ID = cs.lecture_ID
			 LEFT JOIN Room lr ON lr.room_ID = lecture.lecture_ID
			 LEFT JOIN Building lb ON lb.Bldg_ID = lr.Bldg_ID
			 LEFT JOIN Lab lab ON lab.lab_ID = cs.lab_ID
			 LEFT JOIN Room lab_room ON lab_room.room_ID = lab.lab_ID
			 LEFT JOIN Building lab_building ON lab_building.Bldg_ID = lab_room.Bldg_ID
			 WHERE cs.faculty_ID = ?
			   AND cs.semester_ID = ?
			   AND cs.is_Cancelled = 0",
			[$facultyID, $selectedSemesterID]
		);
		foreach ($roomRows as $row) {
			$locations = [];
			$lectureRoom = trim((string) ($row['lecture_room'] ?? ''));
			$labRoom = trim((string) ($row['lab_room'] ?? ''));
			if ($lectureRoom !== '') {
				$locations[] = 'Lecture: ' . $lectureRoom;
			}
			if ($labRoom !== '') {
				$locations[] = 'Lab: ' . $labRoom;
			}
			$roomsByCRN[(int) $row['CRN']] = $locations
				? implode(' · ', array_unique($locations))
				: 'Not listed';
		}
	}
} catch (Throwable $error) {
	$scheduleError = true;
	error_log('Faculty My Schedule load failed: ' . $error->getMessage());
}

$facultyName = $faculty !== null
	? trim(implode(' ', array_filter([
		(string) ($faculty['first_Name'] ?? ''),
		(string) ($faculty['middle_Name'] ?? ''),
		(string) ($faculty['last_Name'] ?? ''),
	], static fn(string $part): bool => $part !== '')))
	: '';

page_start('My Schedule', 'Faculty', 'schedule.php');
?>
<style>
.content:has(.faculty-my-schedule) > h1 { display: none; }
.faculty-my-schedule {
	--fms-navy: #102b4e;
	--fms-navy-deep: #0b203c;
	--fms-blue: #1d5fae;
	--fms-ink: #172b45;
	--fms-muted: #596b81;
	--fms-border: #d4deeb;
	--fms-wash: #f2f6fb;
	color: var(--fms-ink);
	max-width: 1450px;
	margin: 0 auto;
}
.faculty-my-schedule *,
.faculty-my-schedule *::before,
.faculty-my-schedule *::after { box-sizing: border-box; }
.fms-page-header {
	display: flex;
	align-items: end;
	justify-content: space-between;
	gap: 20px;
	margin: 0 0 22px;
}
.fms-page-header h1 {
	margin: 0 0 5px;
	color: var(--fms-navy);
	font-size: clamp(1.65rem, 2.4vw, 2rem);
	line-height: 1.2;
	letter-spacing: -.025em;
}
.fms-page-header p { margin: 0; color: var(--fms-muted); font-size: .96rem; }
.fms-semester-form { display: flex; align-items: center; gap: 9px; flex: 0 0 auto; }
.fms-semester-form label { color: var(--fms-ink); font-size: .88rem; font-weight: 700; }
.fms-semester-form select {
	min-width: 205px;
	min-height: 42px;
	padding: 8px 34px 8px 12px;
	border: 1px solid #b8c8db;
	border-radius: 8px;
	background: #fff;
	color: var(--fms-ink);
	font: inherit;
}
.fms-summary {
	display: grid;
	grid-template-columns: repeat(4, minmax(0, 1fr));
	gap: 12px;
	margin-bottom: 18px;
}
.fms-card, .fms-panel {
	min-width: 0;
	border: 1px solid var(--fms-border);
	border-radius: 10px;
	background: #fff;
	box-shadow: 0 2px 9px rgba(16, 43, 78, .045);
}
.fms-card {
	min-height: 104px;
	display: flex;
	flex-direction: column;
	justify-content: center;
	padding: 16px 18px;
	border-top: 3px solid var(--fms-blue);
}
.fms-card-label { margin: 0 0 7px; color: var(--fms-muted); font-size: .83rem; font-weight: 700; }
.fms-card-value { margin: 0; color: var(--fms-navy); font-size: 1.55rem; line-height: 1.15; font-weight: 760; font-variant-numeric: tabular-nums; }
.fms-card-note { margin: 5px 0 0; color: var(--fms-muted); font-size: .77rem; }
.fms-content-grid {
	display: grid;
	grid-template-columns: minmax(0, 1.9fr) minmax(260px, .86fr);
	align-items: start;
	gap: 16px;
}
.fms-side-stack { display: grid; gap: 16px; min-width: 0; }
.fms-panel { overflow: hidden; }
.fms-panel-heading {
	margin: 0;
	padding: 13px 16px;
	border-bottom: 1px solid var(--fms-border);
	background: var(--fms-navy);
	color: #fff;
	font-size: 1rem;
	line-height: 1.35;
	font-weight: 700;
}
.faculty-my-schedule .fms-panel-heading { color: #fff; }
.fms-table-scroll { width: 100%; overflow-x: auto; overscroll-behavior-inline: contain; }
.fms-table {
	width: 100%;
	min-width: 790px;
	border-collapse: collapse;
	font-size: .84rem;
}
.fms-table caption {
	position: absolute;
	width: 1px;
	height: 1px;
	padding: 0;
	margin: -1px;
	overflow: hidden;
	clip: rect(0, 0, 0, 0);
	white-space: nowrap;
	border: 0;
}
.fms-table th, .fms-table td { padding: 10px 11px; border-bottom: 1px solid #e4eaf1; text-align: left; vertical-align: middle; }
.fms-table th { background: #edf3f9; color: #243c59; font-size: .73rem; font-weight: 750; white-space: nowrap; }
.fms-table tbody tr:nth-child(even) { background: #fafbfd; }
.fms-table tbody tr:hover { background: #f1f6fc; }
.fms-table td:first-child { color: var(--fms-blue); font-weight: 750; font-variant-numeric: tabular-nums; white-space: nowrap; }
.fms-course-code { display: block; font-weight: 750; color: var(--fms-navy); }
.fms-section-no { display: block; margin-top: 2px; color: var(--fms-muted); font-size: .75rem; }
.fms-schedule, .fms-room { min-width: 115px; line-height: 1.45; }
.fms-roster-link {+	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 36px;
	padding: 6px 10px;
	border: 1px solid #7da5d4;
	border-radius: 7px;
	background: #fff;
	color: #174e8c;
	font-size: .78rem;
	font-weight: 700;
	text-decoration: none;
	white-space: nowrap;
}
.fms-roster-link:hover { background: #edf5ff; border-color: #3977bd; }
.fms-table tfoot td { background: #edf3f9; color: var(--fms-navy); font-weight: 750; }
.fms-table-empty, .fms-error {
	margin: 0;
	padding: 22px 18px;
	color: var(--fms-muted);
	line-height: 1.5;
}
.fms-error { color: #7e2929; background: #fff7f6; }
.fms-info-list { margin: 0; padding: 3px 16px 10px; }
.fms-info-row {
	display: grid;
	grid-template-columns: minmax(86px, .7fr) minmax(0, 1.3fr);
	gap: 10px;
	padding: 10px 0;
	border-bottom: 1px solid #e8edf3;
}
.fms-info-row:last-child { border-bottom: 0; }
.fms-info-row dt { color: var(--fms-muted); font-size: .78rem; font-weight: 700; }
.fms-info-row dd { margin: 0; color: var(--fms-ink); font-size: .84rem; font-weight: 600; overflow-wrap: anywhere; }
.fms-departments { display: flex; flex-wrap: wrap; gap: 5px; }
.fms-department { padding: 3px 7px; border: 1px solid #d8e3ef; border-radius: 5px; background: var(--fms-wash); font-size: .78rem; font-weight: 600; }
.fms-notes { margin: 0; padding: 12px 18px 14px 34px; color: #425770; font-size: .82rem; line-height: 1.5; }
.fms-notes li + li { margin-top: 8px; }
.fms-no-semesters { padding: 18px; color: var(--fms-muted); }
.faculty-my-schedule :is(a, select):focus-visible { outline: 3px solid #75a9e3; outline-offset: 2px; }
@media (max-width: 1050px) {
	.fms-content-grid { grid-template-columns: minmax(0, 1.45fr) minmax(240px, .85fr); }
	.fms-table { min-width: 760px; }
}
@media (max-width: 820px) {
	.fms-page-header { align-items: start; flex-direction: column; }
	.fms-content-grid { grid-template-columns: minmax(0, 1fr); }
	.fms-side-stack { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
}
@media (max-width: 620px) {
	.fms-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
	.fms-card { min-height: 90px; padding: 13px; }
	.fms-card-value { font-size: 1.3rem; }
	.fms-side-stack { grid-template-columns: minmax(0, 1fr); }
	.fms-semester-form { width: 100%; align-items: stretch; flex-direction: column; gap: 6px; }
	.fms-semester-form select { width: 100%; }
}
@media (max-width: 380px) {
	.fms-summary { grid-template-columns: minmax(0, 1fr); }
}
@media (prefers-reduced-motion: reduce) {
	.faculty-my-schedule *, .faculty-my-schedule *::before, .faculty-my-schedule *::after { scroll-behavior: auto !important; transition: none !important; }
}
</style>

<div class="faculty-my-schedule">
	<header class="fms-page-header">
		<div>
			<h1>My Schedule</h1>
			<p>View your assigned courses and teaching schedule.</p>
		</div>
		<?php if ($semesterOptions): ?>
			<form class="fms-semester-form" method="get" action="<?= e(url('faculty/schedule.php')) ?>">
				<label for="fms-semester">Semester</label>
				<select id="fms-semester" name="semester">
					<?php foreach ($semesterOptions as $option): ?>
						<option value="<?= (int) $option['semester_ID'] ?>" <?= (int) $option['semester_ID'] === $selectedSemesterID ? 'selected' : '' ?>>
							<?= e($option['semester_Name']) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</form>
		<?php endif; ?>
	</header>

	<?php if ($scheduleError): ?>
		<section class="fms-panel" role="alert">
			<h2 class="fms-panel-heading">Schedule unavailable</h2>
			<p class="fms-error">Your schedule could not be loaded right now. Refresh the page to try again.</p>
		</section>
	<?php elseif ($selectedSemesterID === null): ?>
		<section class="fms-panel" role="status">
			<h2 class="fms-panel-heading">Semester unavailable</h2>
			<p class="fms-no-semesters">Fall 2026 schedule data is not available.</p>
		</section>
	<?php elseif ($faculty === null): ?>
		<section class="fms-panel" role="alert">
			<h2 class="fms-panel-heading">Faculty information unavailable</h2>
			<p class="fms-error">The Faculty profile for this account could not be found.</p>
		</section>
	<?php else: ?>
		<section class="fms-summary" aria-label="Semester teaching summary">
			<article class="fms-card">
				<p class="fms-card-label">Employment Type</p>
				<p class="fms-card-value"><?= e($faculty['faculty_Type']) ?></p>
				<p class="fms-card-note">Stored Faculty classification</p>
			</article>
			<article class="fms-card">
				<p class="fms-card-label">Teaching Credits</p>
				<p class="fms-card-value"><?= (int) $workload['teaching_credits'] ?></p>
				<p class="fms-card-note"><?= e($selectedSemesterName) ?></p>
			</article>
			<article class="fms-card">
				<p class="fms-card-label">Assigned Sections</p>
				<p class="fms-card-value"><?= (int) $workload['assigned_sections'] ?></p>
				<p class="fms-card-note">Active CRNs</p>
			</article>
			<article class="fms-card">
				<p class="fms-card-label">Enrolled Students</p>
				<p class="fms-card-value"><?= $enrolledStudentCount ?></p>
				<p class="fms-card-note">Distinct students this semester</p>
			</article>
		</section>

		<div class="fms-content-grid">
			<section class="fms-panel" aria-labelledby="fms-sections-heading">
				<h2 class="fms-panel-heading" id="fms-sections-heading">My Assigned Sections — <?= e($selectedSemesterName) ?></h2>
				<?php if ($sections): ?>
					<div class="fms-table-scroll" tabindex="0" aria-label="Scrollable assigned sections table">
						<table class="fms-table">
							<caption>Active sections assigned to <?= e($facultyName) ?> for <?= e($selectedSemesterName) ?></caption>
							<thead>
								<tr>
									<th scope="col">CRN</th>
									<th scope="col">Course Code</th>
									<th scope="col">Course Title</th>
									<th scope="col">Credits</th>
									<th scope="col">Meeting Days / Time</th>
									<th scope="col">Room</th>
									<th scope="col">Enrolled</th>
									<th scope="col">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($sections as $section): ?>
									<?php $crn = (int) $section['CRN']; ?>
									<tr>
										<td><?= $crn ?></td>
										<td><span class="fms-course-code"><?= e($section['course_ID']) ?></span><span class="fms-section-no">Section <?= e($section['section_No']) ?></span></td>
										<td><?= e($section['course_Name']) ?></td>
										<td><?= (int) $section['course_Credits'] ?></td>
										<td class="fms-schedule"><?= e($section['meeting_Schedule'] ?: 'To be announced') ?></td>
										<td class="fms-room"><?= e($roomsByCRN[$crn] ?? 'Not listed') ?></td>
										<td><?= (int) ($enrollmentCountsByCRN[$crn] ?? 0) ?></td>
						<td><a class="fms-roster-link" href="<?= e(url('faculty/rosters.php?term=' . $selectedSemesterID . '&crn=' . $crn)) ?>">View Roster</a></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="3">Semester totals</td>
									<td><?= (int) $workload['teaching_credits'] ?> credits</td>
									<td colspan="2"></td>
									<td><?= $enrolledStudentCount ?> distinct</td>
									<td></td>
								</tr>
							</tfoot>
						</table>
					</div>
				<?php else: ?>
					<p class="fms-table-empty">No active teaching sections assigned for this semester.</p>
				<?php endif; ?>
			</section>

			<div class="fms-side-stack">
				<section class="fms-panel" aria-labelledby="fms-faculty-heading">
					<h2 class="fms-panel-heading" id="fms-faculty-heading">Faculty Information</h2>
					<dl class="fms-info-list">
						<div class="fms-info-row"><dt>Name</dt><dd><?= e($facultyName ?: 'Not listed') ?></dd></div>
						<div class="fms-info-row"><dt>Faculty ID</dt><dd><?= (int) $faculty['faculty_ID'] ?></dd></div>
						<div class="fms-info-row"><dt>Department<?= count($departments) === 1 ? '' : 's' ?></dt><dd>
							<?php if ($departments): ?><span class="fms-departments"><?php foreach ($departments as $department): ?><span class="fms-department"><?= e($department['dept_Name']) ?></span><?php endforeach; ?></span><?php else: ?>Not listed<?php endif; ?>
						</dd></div>
						<div class="fms-info-row"><dt>Specialty</dt><dd><?= e($faculty['Specialty'] ?: 'Not listed') ?></dd></div>
						<div class="fms-info-row"><dt>Employment Type</dt><dd><?= e($faculty['faculty_Type']) ?></dd></div>
					</dl>
				</section>

				<section class="fms-panel" aria-labelledby="fms-notes-heading">
					<h2 class="fms-panel-heading" id="fms-notes-heading">Schedule Notes</h2>
					<ul class="fms-notes">
						<li>Only active sections assigned to your account appear here.</li>
						<li>Teaching credits come from your active course assignments for <?= e($selectedSemesterName) ?>.</li>
						<li>Employment Type is stored separately from semester teaching workload.</li>
					</ul>
				</section>
			</div>
		</div>
	<?php endif; ?>
</div>

<script>
document.getElementById('fms-semester')?.addEventListener('change', function () {
	this.form.submit();
});
</script>

<?php page_end(); ?>

<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];

// Student identity and academic program are always loaded for this session ID.
$transcriptStudent = one(
	$pdo,
	"SELECT u.user_ID, u.first_Name, u.middle_Name, u.last_Name,
			st.student_Type, m.major_Name, gs.Program
	 FROM Student st
	 JOIN User u ON u.user_ID = st.student_ID
	 LEFT JOIN Major m ON m.major_ID = st.major_ID
	 LEFT JOIN Graduate_Student gs ON gs.student_ID = st.student_ID
	 WHERE st.student_ID = ?",
	[$studentID]
);

$transcriptName = trim(implode(' ', array_filter([
	(string) ($transcriptStudent['first_Name'] ?? ''),
	(string) ($transcriptStudent['middle_Name'] ?? ''),
	(string) ($transcriptStudent['last_Name'] ?? ''),
], static fn(string $part): bool => $part !== '')));
$transcriptProgramLabel = ($transcriptStudent['student_Type'] ?? '') === 'Graduate'
	? 'Program'
	: 'Major';
$transcriptProgram = ($transcriptStudent['student_Type'] ?? '') === 'Graduate'
	? (string) ($transcriptStudent['Program'] ?? '')
	: (string) ($transcriptStudent['major_Name'] ?? '');

$transcriptRegistrationSemesters = all_rows(
	$pdo,
	"SELECT semester_ID, semester_Name
	 FROM Semester
	 WHERE semester_Name IN ('Spring 2026', 'Fall 2026', 'Spring 2027')
	 ORDER BY CASE semester_Name
		 WHEN 'Spring 2026' THEN 1
		 WHEN 'Fall 2026' THEN 2
		 WHEN 'Spring 2027' THEN 3
	 END"
);
$transcriptRegistrationSemesterIDs = array_map(
	static fn(array $term): int => (int)$term['semester_ID'],
	$transcriptRegistrationSemesters
);
$transcriptDefaultRegistrationSemesterID = null;
foreach ($transcriptRegistrationSemesters as $term) {
	if ($term['semester_Name'] === 'Fall 2026') {
		$transcriptDefaultRegistrationSemesterID = (int)$term['semester_ID'];
		break;
	}
}
$transcriptRequestedSemesterID = filter_input(INPUT_GET, 'semester', FILTER_VALIDATE_INT);
$transcriptRegistrationSemesterID = $transcriptRequestedSemesterID !== null
	&& $transcriptRequestedSemesterID !== false
	&& in_array($transcriptRequestedSemesterID, $transcriptRegistrationSemesterIDs, true)
	? $transcriptRequestedSemesterID
	: ($transcriptDefaultRegistrationSemesterID ?? ($transcriptRegistrationSemesterIDs[0] ?? null));
$transcriptRegistrationSemesterName = '';
foreach ($transcriptRegistrationSemesters as $term) {
	if ((int)$term['semester_ID'] === (int)$transcriptRegistrationSemesterID) {
		$transcriptRegistrationSemesterName = (string)$term['semester_Name'];
		break;
	}
}
$transcriptRegisteredCourses = $transcriptRegistrationSemesterID !== null
	? student_semester_registered_courses($pdo, $studentID, $transcriptRegistrationSemesterID)
	: [];
$transcriptSemesterStatus = $transcriptRegistrationSemesterID !== null
	? student_semester_enrollment_status($pdo, $studentID, $transcriptRegistrationSemesterID)
	: null;


// Get the student's course history
$courseHistory = all_rows(
	$pdo,
	"SELECT
			Semester.semester_Name,
			Student_History.semester_ID,
			Course.course_ID,
			Course.course_Name,
			Course.course_Credits,
			Student_History.Grade
		FROM Student_History
		JOIN Course
			ON Course.course_ID = Student_History.course_ID
		JOIN Semester
			ON Semester.semester_ID = Student_History.semester_ID
		WHERE Student_History.student_ID = ?
		ORDER BY
			CAST(SUBSTRING_INDEX(Semester.semester_Name, ' ', -1) AS UNSIGNED) DESC,
			CASE WHEN Semester.semester_Name LIKE 'Spring %' THEN 1 ELSE 0 END DESC,
			Course.course_ID",
	[$studentID]
);


// Transcript summary: grade points, credits attempted, credits earned
$gradePoints = 0.0;
$gradedCredits = 0;
$earnedCredits = 0;
$attemptedCredits = 0;

foreach ($courseHistory as $course) {

	$credits = (int) $course['course_Credits'];
	$points = grade_points($course['Grade']);

	$attemptedCredits += $credits;

	if ($points !== null) {

		$gradePoints += $points * $credits;
		$gradedCredits += $credits;

		// Grades D and above earn the credits
		if ($points >= 1.0) {
			$earnedCredits += $credits;
		}
	}
}

$gpa = $gradedCredits > 0
	? round($gradePoints / $gradedCredits, 2)
	: null;


// Courses currently in progress (registered without a final grade yet)
	$currentSemester = current_registration_semester($pdo);

	$currentSemesterName = null;

	if ($currentSemester !== null) {

		$currentSemesterName = (string) one(
			$pdo,
			'SELECT semester_Name FROM Semester WHERE semester_ID = ?',
			[$currentSemester]
		)['semester_Name'];
	}

$inProgress = $currentSemester !== null
	? student_semester_registered_courses($pdo, $studentID, (int) $currentSemester, true)
	: [];

// Semester GPA breakdown
$semesterGPAs = [];
foreach ($courseHistory as $course) {
    $semID = (int) $course['semester_ID'];
    if (!isset($semesterGPAs[$semID])) {
        $semesterGPAs[$semID] = [
            'semester_Name' => $course['semester_Name'],
            'gradePoints' => 0.0,
            'gradedCredits' => 0,
        ];
    }
    $credits = (int) $course['course_Credits'];
    $points = grade_points($course['Grade']);
    if ($points !== null) {
        $semesterGPAs[$semID]['gradePoints'] += $points * $credits;
        $semesterGPAs[$semID]['gradedCredits'] += $credits;
    }
}
foreach ($semesterGPAs as &$sem) {
    $sem['gpa'] = $sem['gradedCredits'] > 0 ? round($sem['gradePoints'] / $sem['gradedCredits'], 2) : null;
}
unset($sem);

// Academic standing based on cumulative GPA; do not imply standing without grades.
$academicStanding = 'N/A';
if ($gpa !== null) {
    if ($gpa >= 3.5) $academicStanding = 'Dean\'s List';
    elseif ($gpa >= 2.0) $academicStanding = 'Good Standing';
    else $academicStanding = 'Academic Probation';
}


page_start(
	'Unofficial Transcript',
	'Student',
	'transcript.php'
);

?>

<style>
.transcript-letterhead {
	margin: 0 0 20px;
	padding: 18px 20px;
	border-bottom: 2px solid currentColor;
	text-align: center;
}
.transcript-letterhead h2 {
	margin: 0;
	font-size: 1.35rem;
	letter-spacing: .045em;
}
.transcript-letterhead p {
	margin: 7px 0 0;
	font-size: .9rem;
}
.transcript-identity {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
	gap: 12px 24px;
	margin-bottom: 18px;
}
.transcript-identity div { display: grid; gap: 4px; }
.transcript-identity strong { font-size: .82rem; }
@page { size: letter portrait; margin: .65in; }
@media print {
	html, body { background: #fff !important; color: #111 !important; }
	.topbar, .sidebar, #admin-sidebar-toggle,
	.transcript-print-controls, .content > h1 { display: none !important; }
	.shell { display: block !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
	.content { width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
	.transcript-document { width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; color: #111 !important; }
	.transcript-document .card,
	.transcript-document .dashboard-stat { break-inside: avoid; box-shadow: none !important; }
	.transcript-document .transcript-registered-courses { break-inside: auto !important; }
	.transcript-document .table-wrap { overflow: visible !important; }
	.transcript-document table { width: 100% !important; border-collapse: collapse !important; font-size: 9pt; }
	.transcript-document th, .transcript-document td { border: 1px solid #777 !important; padding: 5px 7px !important; color: #111 !important; }
	.transcript-document tr { break-inside: avoid; }
}
</style>

<div class="transcript-document">

	<header class="transcript-letterhead">
		<h2>ASHFORD UNIVERSITY — UNOFFICIAL TRANSCRIPT</h2>
		<p>Student academic record · For advising and personal reference</p>
	</header>

	<section class="card transcript-identity" aria-label="Student transcript identity">
		<div><strong>Student name</strong><span><?= e($transcriptName !== '' ? $transcriptName : 'Student record unavailable') ?></span></div>
		<div><strong>Student ID</strong><span><?= e($transcriptStudent['user_ID'] ?? $studentID) ?></span></div>
		<div><strong>Academic level</strong><span><?= e($transcriptStudent['student_Type'] ?? 'Unavailable') ?></span></div>
		<div><strong><?= e($transcriptProgramLabel) ?></strong><span><?= e($transcriptProgram !== '' ? $transcriptProgram : 'Not available') ?></span></div>
	</section>

<section class="dashboard-stats" aria-label="Transcript summary">

	<article class="dashboard-stat card">
		<span class="dashboard-stat-label">Cumulative GPA</span>
		<strong><?= $gpa !== null ? e($gpa) : '—' ?></strong>
		<span class="dashboard-stat-note">Credit-weighted</span>
	</article>

	<article class="dashboard-stat card">
		<span class="dashboard-stat-label">Academic Standing</span>
		<strong><?= e($academicStanding) ?></strong>
		<span class="dashboard-stat-note">Based on cumulative GPA</span>
	</article>

	<article class="dashboard-stat card">
		<span class="dashboard-stat-label">Credits earned</span>
		<strong><?= e($earnedCredits) ?></strong>
		<span class="dashboard-stat-note">Passed coursework</span>
	</article>

	<article class="dashboard-stat card">
		<span class="dashboard-stat-label">Credits attempted</span>
		<strong><?= e($attemptedCredits) ?></strong>
		<span class="dashboard-stat-note">All recorded history</span>
	</article>

	<article class="dashboard-stat card">
		<span class="dashboard-stat-label">In progress</span>
		<strong><?= count($inProgress) ?></strong>
		<span class="dashboard-stat-note"><?= e($currentSemesterName ?? 'this term') ?>, ungraded</span>
	</article>

</section>

<div class="transcript-print-controls" style="margin-bottom:16px;text-align:right">
	<button onclick="window.print()" class="btn">Print / Save as PDF</button>
</div>

<p class="muted">
	View your completed courses and grades. This document is unofficial;
	the registrar's office issues official transcripts.
</p>


<?php if (count($semesterGPAs) > 0): ?>

	<section class="card" style="margin-top:16px;">
		<h2>Semester GPA Breakdown</h2>
		<div class="table-wrap">
			<table>
				<tr>
					<th>Semester</th>
					<th>GPA</th>
					<th>Credits</th>
				</tr>
				<?php foreach ($semesterGPAs as $sem): ?>
					<tr>
						<td><?= e($sem['semester_Name']) ?></td>
						<td><?= $sem['gpa'] !== null ? e($sem['gpa']) : '—' ?></td>
						<td><?= e($sem['gradedCredits']) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</section>

<?php endif; ?>


<?php if (count($courseHistory) > 0): ?>

	<div class="table-wrap">

		<table>

			<tr>
				<th>Semester</th>
				<th>Course</th>
				<th>Title</th>
				<th>Credits</th>
				<th>Grade</th>
			</tr>


			<?php foreach ($courseHistory as $course): ?>

				<tr>

					<td>
						<?= e($course['semester_Name']) ?>
					</td>

					<td>
						<?= e($course['course_ID']) ?>
					</td>

					<td>
						<?= e($course['course_Name']) ?>
					</td>

					<td>
						<?= e($course['course_Credits']) ?>
					</td>

					<td>
						<?= e($course['Grade'] ?? '-') ?>
					</td>

				</tr>

			<?php endforeach; ?>

		</table>

	</div>

<?php else: ?>

	<div class="card">
		<p>No course history is currently available.</p>
	</div>

<?php endif; ?>


<section class="card transcript-registered-courses">
	<p><strong><?= e($transcriptRegistrationSemesterName) ?> enrollment status:</strong>
		<?= e($transcriptSemesterStatus['registered_credits'] ?? 0) ?> registered credits ·
		<?= e($transcriptSemesterStatus['status'] ?? 'Unavailable') ?></p>
	<div class="transcript-print-controls" style="margin-bottom:14px">
		<form method="get" action="transcript.php" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
			<div>
				<label for="transcript-semester"><strong>Registered courses semester</strong></label><br>
				<select id="transcript-semester" name="semester" style="padding:9px;border:1px solid #cbd5e1;border-radius:8px">
					<?php foreach ($transcriptRegistrationSemesters as $term): ?>
						<option value="<?= (int)$term['semester_ID'] ?>" <?= (int)$transcriptRegistrationSemesterID === (int)$term['semester_ID'] ? 'selected' : '' ?>><?= e($term['semester_Name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<button type="submit" class="btn">View Semester</button>
		</form>
	</div>
	<h2><?= e($transcriptRegistrationSemesterName) ?> — Registered Courses</h2>
	<?php if (!$transcriptRegisteredCourses): ?>
		<p>No courses registered for <?= e($transcriptRegistrationSemesterName ?: 'this semester') ?>.</p>
	<?php else: ?>
		<div class="table-wrap">
			<table>
				<thead><tr><th>Course</th><th>Title</th><th>CRN</th><th>Credits</th><th>Status</th></tr></thead>
				<tbody><?php foreach ($transcriptRegisteredCourses as $course): ?>
					<tr>
						<td><?= e($course['course_ID']) ?></td>
						<td><?= e($course['course_Name']) ?></td>
						<td><?= e($course['CRN']) ?></td>
						<td><?= e($course['course_Credits']) ?></td>
						<td><?= e($course['registration_status']) ?></td>
					</tr>
				<?php endforeach; ?></tbody>
			</table>
		</div>
	<?php endif; ?>
</section>


<?php if (count($inProgress) > 0 && $currentSemester !== $transcriptRegistrationSemesterID): ?>

	<section class="card">

		<h2>In progress (<?= e($currentSemesterName ?? 'this term') ?>)</h2>

		<div class="table-wrap">
			<table>
				<tr>
					<th>Course</th>
					<th>Title</th>
					<th>Credits</th>
					<th>CRN</th>
				</tr>
				<?php foreach ($inProgress as $course): ?>
					<tr>
						<td><?= e($course['course_ID']) ?></td>
						<td><?= e($course['course_Name']) ?></td>
						<td><?= e($course['course_Credits']) ?></td>
						<td><?= e($course['CRN']) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>

	</section>

<?php endif; ?>


</div>


<?php

page_end();

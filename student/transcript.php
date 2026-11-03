<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


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
			Student_History.semester_ID DESC,
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

$inProgress = all_rows(
	$pdo,
	"SELECT
			Course.course_ID,
			Course.course_Name,
			Course.course_Credits,
			Course_Section.CRN
		FROM Enrollment
		JOIN Course_Section
			ON Course_Section.CRN = Enrollment.CRN
		JOIN Course
			ON Course.course_ID = Course_Section.course_ID
		WHERE Enrollment.student_ID = ?
			AND Enrollment.semester_ID = ?
			AND Enrollment.Grade IS NULL
		ORDER BY Course.course_ID",
	[$studentID, $currentSemester]
);


page_start(
	'Unofficial Transcript',
	'Student',
	'transcript.php'
);

?>


<section class="dashboard-stats" aria-label="Transcript summary">

	<article class="dashboard-stat card">
		<span class="dashboard-stat-label">Cumulative GPA</span>
		<strong><?= $gpa !== null ? e($gpa) : '—' ?></strong>
		<span class="dashboard-stat-note">Credit-weighted</span>
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


<p class="muted">
	View your completed courses and grades. This document is unofficial;
	the registrar's office issues official transcripts.
</p>


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


<?php if (count($inProgress) > 0): ?>

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


<?php

page_end();

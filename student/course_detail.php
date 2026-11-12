<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];
$crn = (int) ($_GET['crn'] ?? 0);

if ($crn <= 0) {
	header('Location: registration.php');
	exit;
}

// Load the section with full course, instructor, room, and meeting-time detail
$section = one(
	$pdo,
	"SELECT
		cs.CRN,
		cs.section_No,
		cs.course_ID,
		course.course_Name,
		course.course_Credits,
		course.course_Type,
		course.course_DSC,
		d.dept_Name,
		cs.faculty_ID,
		CONCAT(u.first_Name, ' ', u.last_Name) AS faculty_Name,
		f.faculty_Type,
		f.Specialty,
		cs.time_Slot_ID,
		cs.lecture_ID,
		cs.lab_ID,
		cs.available_Seats,
		cs.is_Cancelled
	FROM Course_Section cs
	JOIN Course course ON course.course_ID = cs.course_ID
	JOIN Department d ON d.dept_ID = course.dept_ID
	JOIN User u ON u.user_ID = cs.faculty_ID
	JOIN Faculty f ON f.faculty_ID = cs.faculty_ID
	WHERE cs.CRN = ?",
	[$crn]
);

if (!$section || (int) $section['is_Cancelled'] === 1) {
	header('Location: registration.php');
	exit;
}

// Prerequisites for this course
$prerequisites = all_rows(
	$pdo,
	"SELECT cp.prerequisite_course_ID, pre.course_Name, cp.min_Grade_Req
	FROM Course_Prerequisite cp
	JOIN Course pre ON pre.course_ID = cp.prerequisite_course_ID
	WHERE cp.course_ID = ?
	ORDER BY pre.course_ID",
	[$section['course_ID']]
);

// Student's grade for each prerequisite (best recorded attempt)
$prereqStatus = [];

foreach ($prerequisites as $prereq) {
	$best = one(
		$pdo,
		"SELECT Grade
		FROM Student_History
		WHERE student_ID = ?
			AND course_ID = ?
			AND Grade IS NOT NULL
		ORDER BY FIELD(Grade,
			'A', 'A-', 'B+', 'B', 'B-',
			'C+', 'C', 'C-', 'D+', 'D', 'F')
		LIMIT 1",
		[$studentID, $prereq['prerequisite_course_ID']]
	);

	$prereqStatus[$prereq['prerequisite_course_ID']] = [
		'course_ID' => $prereq['prerequisite_course_ID'],
		'course_Name' => (string) $prereq['course_Name'],
		'min_Grade_Req' => (string) $prereq['min_Grade_Req'],
		'student_Grade' => $best ? (string) $best['Grade'] : null,
		'met' => $best !== null && grade_meets(
			(string) $best['Grade'],
			(string) $prereq['min_Grade_Req']
		),
	];
}

// Meeting time details
$dayMap = [];

foreach (
	all_rows(
		$pdo,
		"SELECT tsd.time_Slot_ID, dy.day_Of_Week
		FROM Time_Slot_Day tsd
		JOIN Day dy ON dy.day_ID = tsd.day_ID
		WHERE tsd.time_Slot_ID = ?",
		[(int) $section['time_Slot_ID']]
	) as $row
) {
	$dayMap[] = (string) $row['day_Of_Week'];
}

$periodMap = [];

foreach (
	all_rows(
		$pdo,
		"SELECT tsp.time_Slot_ID,
				MIN(p.start_Time) AS start_Time,
				MAX(p.end_Time) AS end_Time
		FROM Time_Slot_Period tsp
		JOIN Period p ON p.period_ID = tsp.period_ID
		WHERE tsp.time_Slot_ID = ?
		GROUP BY tsp.time_Slot_ID",
		[(int) $section['time_Slot_ID']]
	) as $row
) {
	$periodMap = [
		'start_Time' => (string) $row['start_Time'],
		'end_Time' => (string) $row['end_Time'],
	];
}

// Room detail
$roomLabel = section_room_label(
	$pdo,
	$section['lecture_ID'] !== null ? (int) $section['lecture_ID'] : null,
	$section['lab_ID'] !== null ? (int) $section['lab_ID'] : null
);

// Student registration status for this section
$alreadyRegistered = (bool) one(
	$pdo,
	"SELECT 1
	FROM Enrollment
	WHERE student_ID = ? AND CRN = ?",
	[$studentID, $crn]
);

// Student already passed this course
$alreadyPassed = (bool) one(
	$pdo,
	"SELECT 1
	FROM Student_History
	WHERE student_ID = ?
		AND course_ID = ?
		AND Grade IN (
			'A', 'A-', 'B+', 'B', 'B-',
			'C+', 'C', 'C-', 'D+', 'D'
		)
	LIMIT 1",
	[$studentID, $section['course_ID']]
);

// Registration eligibility
[$canRegister, $registerMessage] = registration_check($pdo, $studentID, $crn);

page_start(
	'Course Detail - ' . e($section['course_Name']),
	'Student',
	'course_detail.php'
);

?>

<h2><?= e($section['course_Name']) ?></h2>

<p class="muted">
	<?= e($section['course_ID']) ?> &bull;
	<?= e($section['dept_Name']) ?> &bull;
	<?= e((int) $section['course_Credits']) ?> credits &bull;
	<?= e($section['course_Type']) ?>
</p>

<div class="card">

	<h3>Course Description</h3>
	<p><?= e($section['course_DSC']) ?></p>

</div>

<div class="card grid-2">

	<div>
		<h3>Instructor</h3>
		<p><strong><?= e($section['faculty_Name']) ?></strong></p>
		<p><?= e($section['faculty_Type']) ?> faculty</p>
		<p><?= e($section['Specialty']) ?></p>
	</div>

	<div>
		<h3>Scheduled Class Time</h3>
		<p>
			<?php
			$label = time_slot_label($pdo, (int) $section['time_Slot_ID']);
			echo e($label);
			?>
		</p>
		<p>
			<strong>Location:</strong>
			<?= e($roomLabel) ?>
		</p>
		<p>
			<strong>Available Seats:</strong>
			<?php
			$seats = (int) $section['available_Seats'];
			if ($seats > 0) {
				echo e($seats) . ' open';
			} else {
				echo '<span class="badge">Full</span>';
			}
			?>
		</p>
		<p>
			<strong>Section:</strong>
			<?= e($section['section_No']) ?> &bull;
			<strong>CRN:</strong>
			<?= e($section['CRN']) ?>
		</p>
	</div>

</div>

<div class="card">

	<h3>Prerequisites</h3>

	<?php if (count($prerequisites) > 0): ?>

		<table>
			<thead>
				<tr>
					<th>Course</th>
					<th>Title</th>
					<th>Minimum Grade</th>
					<th>Your Grade</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($prereqStatus as $prereq): ?>
					<tr>
						<td><?= e($prereq['course_ID']) ?></td>
						<td><?= e($prereq['course_Name']) ?></td>
						<td><?= e($prereq['min_Grade_Req']) ?></td>
						<td><?= $prereq['student_Grade'] ?: '<span class="muted">Not taken</span>' ?></td>
						<td>
							<?php if ($prereq['met']): ?>
								<span style="color: green;">&#10004; Met</span>
							<?php else: ?>
								<span style="color: red;">&#10007; Not met</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	<?php else: ?>

		<p class="muted">No prerequisites required.</p>

	<?php endif; ?>

</div>

<div class="card">

	<h3>Registration</h3>

	<?php if ($alreadyRegistered): ?>

		<p>
			<strong>You are already registered for this section.</strong>
		</p>

	<?php elseif ($alreadyPassed): ?>

		<p>
			<strong>You have already passed this course.</strong>
		</p>

	<?php elseif (!$canRegister): ?>

		<p>
			<strong>Not eligible to register:</strong>
			<?= e($registerMessage) ?>
		</p>

	<?php elseif ((int) $section['available_Seats'] <= 0): ?>

		<p>
			<strong>This section is full.</strong>
		</p>

	<?php else: ?>

		<form method="post" action="registration_actions.php">

			<?= csrf_field() ?>

			<input type="hidden" name="crn" value="<?= e($crn) ?>">

			<button type="submit" class="btn">
				Register for CRN <?= e($crn) ?>
			</button>

		</form>

	<?php endif; ?>

</div>

<p>
	<a class="btn light" href="registration.php">&larr; Back to Registration</a>
</p>

<?php

page_end();

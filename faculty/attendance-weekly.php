<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];

// Get the sections taught by this faculty member
$sections = all_rows(
	$pdo,
	"SELECT
		cs.CRN,
		c.course_ID,
		c.course_Name,
		s.semester_Name
	FROM Course_Section cs
	JOIN Course c ON c.course_ID = cs.course_ID
	JOIN Semester s ON s.semester_ID = cs.semester_ID
	WHERE cs.faculty_ID = ?
		AND cs.is_Cancelled = 0
	ORDER BY s.semester_ID DESC, c.course_ID",
	[$facultyID]
);

// Selected CRN
$crn = (int) ($_GET['crn'] ?? ($sections[0]['CRN'] ?? 0));

// Selected week (default to current week)
$selectedWeek = $_GET['week'] ?? date('Y-m-d');

// Validate the week date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedWeek) || !checkdate(
	(int) substr($selectedWeek, 5, 2),
	(int) substr($selectedWeek, 8, 2),
	(int) substr($selectedWeek, 0, 4)
)) {
	$selectedWeek = date('Y-m-d');
}

// Get the Monday of the selected week
$weekStart = new DateTimeImmutable($selectedWeek);
$weekStart = $weekStart->modify('Monday this week');
$weekStartStr = $weekStart->format('Y-m-d');

// Section info
$sectionInfo = null;
if ($crn > 0) {
	$sectionInfo = one(
		$pdo,
		"SELECT cs.CRN, c.course_ID, c.course_Name, c.course_Credits,
				s.semester_Name
		FROM Course_Section cs
		JOIN Course c ON c.course_ID = cs.course_ID
		JOIN Semester s ON s.semester_ID = cs.semester_ID
		WHERE cs.CRN = ? AND cs.faculty_ID = ?",
		[$crn, $facultyID]
	);
}

// Week navigation
$weekStartObj = new DateTimeImmutable($selectedWeek);
$weekStartObj = $weekStartObj->modify('Monday this week');
$weekStartStr = $weekStartObj->format('Y-m-d');
$weekEndObj = clone $weekStartObj;
$weekEndObj = $weekEndObj->modify('Sunday this week');
$weekEndStr = $weekEndObj->format('Y-m-d');
$prevWeekObj = clone $weekStartObj;
$prevWeekObj = $prevWeekObj->modify('last Monday');
$prevWeek = $prevWeekObj->format('Y-m-d');
$nextWeekObj = clone $weekStartObj;
$nextWeekObj = $nextWeekObj->modify('next Monday');
$nextWeek = $nextWeekObj->format('Y-m-d');

// Get enrolled students
$roster = [];
if ($sectionInfo) {
	$roster = all_rows(
		$pdo,
		"SELECT
			Enrollment.student_ID,
			User.first_Name,
			User.last_Name
		FROM Enrollment
		JOIN User ON User.user_ID = Enrollment.student_ID
		WHERE Enrollment.CRN = ?
		ORDER BY User.last_Name, User.first_Name",
		[$crn]
	);
}

// Get attendance for the week
$weeklyAttendance = [];
if ($sectionInfo && !empty($roster)) {
	$studentIDs = array_map(
		static fn(array $row): int => (int) $row['student_ID'],
		$roster
	);
	$placeholders = implode(',', array_fill(0, count($studentIDs), '?'));

	$weeklyAttendance = all_rows(
		$pdo,
		"SELECT
			student_ID,
			attendance_Date,
			Present_Absent
		FROM Attendance
		WHERE CRN = ?
			AND student_ID IN (" . $placeholders . ")
			AND attendance_Date >= ?
			AND attendance_Date <= ?
		",
		array_merge([$crn, $weekStartStr, $weekEndStr], $studentIDs)
	);
}

// Build attendance lookup: [student_ID][date] = Present_Absent
$attendanceLookup = [];
foreach ($weeklyAttendance as $row) {
	$attendanceLookup[(int) $row['student_ID']][$row['attendance_Date']] = $row['Present_Absent'];
}

// Days of the week
$days = [];
for ($i = 0; $i < 7; $i++) {
	$dayDateObj = clone $weekStartObj;
	$dayDateObj = $dayDateObj->modify("+$i days");
	$dayDate = $dayDateObj->format('Y-m-d');
	$dayNameObj = clone $weekStartObj;
	$dayNameObj = $dayNameObj->modify("+$i days");
	$dayName = $dayNameObj->format('D');
	$days[] = [
		'date' => $dayDate,
		'name' => $dayName,
		'isToday' => $dayDate === date('Y-m-d'),
	];
}

page_start(
	'Weekly Attendance',
	'Faculty',
	'attendance-weekly.php'
);

flash();

?>

<section class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Weekly Attendance</p>
			<h2><?= e($sectionInfo['course_ID'] . ' ' . $sectionInfo['course_Name'] ?? 'Select a Course') ?></h2>
		</div>
	</div>

	<?php if (!$sectionInfo): ?>

		<p class="muted">No course sections found.</p>

	<?php else: ?>

		<form method="get" style="margin-top: 12px;">
			<?= csrf_field() ?>
			<input type="hidden" name="crn" value="<?= e($crn) ?>">

			<div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
				<div>
					<label for="week_select">Week Starting</label>
					<input
						type="date"
						name="week"
						id="week_select"
						value="<?= e($weekStartStr) ?>"
						max="<?= e(date('Y-m-d')) ?>"
					>
				</div>
				<div style="align-self:end;">
					<button type="submit" class="btn">View Week</button>
				</div>
			</div>
		</form>

		<div style="margin-top: 12px;">
			<a
				href="attendance-weekly.php?crn=<?= e($crn) ?>&week=<?= e($prevWeek) ?>"
				class="btn light small"
			>
				Previous Week
			</a>
			<span class="muted" style="margin:0 12px;">
				<?= e($weekStartStr) ?> to <?= e($weekEndStr) ?>
			</span>
			<a
				href="attendance-weekly.php?crn=<?= e($crn) ?>&week=<?= e($nextWeek) ?>"
				class="btn light small"
			>
				Next Week
			</a>
		</div>

		<?php if (count($roster) > 0): ?>

			<form method="post" action="attendance-weekly_actions.php" style="margin-top: 18px;">
				<?= csrf_field() ?>
				<input type="hidden" name="crn" value="<?= e($crn) ?>">
				<input type="hidden" name="week_start" value="<?= e($weekStartStr) ?>">

				<table class="weekly-grid">
					<tr>
						<th>Student ID</th>
						<th>Name</th>
						<?php foreach ($days as $day): ?>
							<th style="width:60px; text-align:center;">
								<?= e($day['name']) ?> <?= e(substr($day['date'], 5)) ?>
								<?php if ($day['isToday']): ?>
									<span style="font-size:10px; color:#d6a800;">Today</span>
								<?php endif; ?>
							</th>
						<?php endforeach; ?>
						<th style="width:80px; text-align:center;">Weekly %</th>
					</tr>

					<?php foreach ($roster as $student): ?>

						<tr>
							<td><?= e($student['student_ID']) ?></td>
							<td><?= e($student['first_Name'] . ' ' . $student['last_Name']) ?></td>

							<?php $presentCount = 0; $recordedCount = 0; ?>
							<?php foreach ($days as $day): ?>

								<td style="text-align:center;">
									<?php
									$val = $attendanceLookup[$student['student_ID']][$day['date']] ?? null;
									$isPresent = $val === 'Present';
									$isAbsent = $val === 'Absent';
									if ($val !== null) {
										$recordedCount++;
										if ($isPresent) {
											$presentCount++;
										}
									}
									?>
									<button
										type="button"
										class="attend-cell<?= $isPresent ? ' present' : '' ?><?= $isAbsent ? ' absent' : '' ?>"
										data-student="<?= e($student['student_ID']) ?>"
										data-date="<?= e($day['date']) ?>"
									>
										<?= $isPresent ? 'P' : ($isAbsent ? 'A' : '-') ?>
									</button>
									<input
										type="hidden"
										name="attendance[<?= e($student['student_ID']) ?>][<?= e($day['date']) ?>]"
										value="<?= e($val ?? '') ?>"
									>
								</td>

							<?php endforeach; ?>

							<td style="text-align:center; font-weight:600;">
								<?php if ($recordedCount > 0): ?>
									<?= e(round(($presentCount / $recordedCount) * 100)) ?>%
								<?php else: ?>
									-
								<?php endif; ?>
							</td>
						</tr>

					<?php endforeach; ?>

				</table>

				<div style="margin-top: 16px; display:flex; gap:10px;">
					<button type="submit" class="btn">Save Weekly Attendance</button>
					<a href="attendance-weekly.php?crn=<?= e($crn) ?>" class="btn light">Cancel</a>
				</div>
			</form>

		<?php else: ?>

			<div class="card">
				<p class="muted">No students enrolled in this section.</p>
			</div>

		<?php endif; ?>

	<?php endif; ?>

</section>

<?php

page_end();

?>

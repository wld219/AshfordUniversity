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
		s.semester_Name,
		s.semester_Start
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

// Selected date (default today)
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Validate the date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate) || !checkdate(
	(int) substr($selectedDate, 5, 2),
	(int) substr($selectedDate, 8, 2),
	(int) substr($selectedDate, 0, 4)
)) {
	$selectedDate = date('Y-m-d');
}

// Section info
$sectionInfo = null;
if ($crn > 0) {
	$sectionInfo = one(
		$pdo,
		"SELECT cs.CRN, c.course_ID, c.course_Name, c.course_Credits,
				s.semester_Name, s.semester_Start, cs.time_Slot_ID
		FROM Course_Section cs
		JOIN Course c ON c.course_ID = cs.course_ID
		JOIN Semester s ON s.semester_ID = cs.semester_ID
		WHERE cs.CRN = ? AND cs.faculty_ID = ?",
		[$crn, $facultyID]
	);
}

// Check if attendance is allowed for this date
$attendanceAllowed = false;
$attendanceMessage = '';
if ($sectionInfo) {
	[$attendanceAllowed, $attendanceMessage] = attendance_date_allowed(
		$pdo,
		$crn,
		$selectedDate
	);
}

// Get enrolled students with their existing attendance for the selected date
$roster = [];
if ($sectionInfo) {
	$roster = all_rows(
		$pdo,
		"SELECT
			Enrollment.student_ID,
			User.first_Name,
			User.last_Name,
			Attendance.Present_Absent
		FROM Enrollment
		JOIN User ON User.user_ID = Enrollment.student_ID
		LEFT JOIN Attendance
			ON Attendance.student_ID = Enrollment.student_ID
			AND Attendance.CRN = ?
			AND Attendance.attendance_Date = ?
		WHERE Enrollment.CRN = ?
		ORDER BY User.last_Name, User.first_Name",
		[$crn, $selectedDate, $crn]
	);
}

// Calendar navigation: previous/next month
$viewDate = new DateTimeImmutable($selectedDate);
$prevMonth = $viewDate->modify('first day of last month')->format('Y-m-d');
$nextMonth = $viewDate->modify('first day of next month')->format('Y-m-d');

// Reset to selected date for display
$viewDate = new DateTimeImmutable($selectedDate);
$monthName = $viewDate->format('F Y');
$firstDay = $viewDate->modify('first day of this month');
$daysInMonth = (int) $firstDay->format('t');
$startWeekday = (int) $firstDay->format('N'); // 1=Monday, 7=Sunday

// Build calendar weeks
$weeks = [];
$day = 1;
for ($w = 0; $w < 6; $w++) {
	$week = [];
	for ($d = 1; $d <= 7; $d++) {
		if (($w === 0 && $d < $startWeekday) || $day > $daysInMonth) {
			$week[] = null;
		} else {
			$calDate = $viewDate->modify('first day of this month')
				->modify("+$day days")->format('Y-m-d');
			$week[] = [
				'day' => $day,
				'date' => $calDate,
				'isToday' => $calDate === date('Y-m-d'),
				'isSelected' => $calDate === $selectedDate,
				'hasAttendance' => false,
			];
			$day++;
		}
	}
	$weeks[] = $week;
	if ($day > $daysInMonth) {
		break;
	}
}

// Check which calendar dates already have attendance
$attendanceDates = [];
if ($sectionInfo) {
	$attendanceDates = all_rows(
		$pdo,
		"SELECT DISTINCT attendance_Date FROM Attendance
		WHERE CRN = ?",
		[$crn]
	);
}
$attendanceDateSet = [];
foreach ($attendanceDates as $row) {
	$attendanceDateSet[(string) $row['attendance_Date']] = true;
}

// Mark calendar dates that have attendance
foreach ($weeks as &$week) {
	foreach ($week as &$dayCell) {
		if ($dayCell !== null && isset($attendanceDateSet[$dayCell['date']])) {
			$dayCell['hasAttendance'] = true;
		}
	}
}
unset($week);

page_start(
	'Attendance Calendar',
	'Faculty',
	'attendance-calendar.php'
);

flash();

?>

<section class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Attendance Calendar</p>
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
					<label for="date_select">Date</label>
					<input
						type="date"
						name="date"
						id="date_select"
						value="<?= e($selectedDate) ?>"
						max="<?= e(date('Y-m-d')) ?>"
					>
				</div>
				<div style="align-self:end;">
					<button type="submit" class="btn">View Date</button>
				</div>
			</div>

			<p class="muted">
				<?= e($attendanceMessage) ?>
			</p>
		</form>

		<?php if ($attendanceAllowed): ?>

			<form method="post" action="attendance-calendar_actions.php" style="margin-top: 18px;">
				<?= csrf_field() ?>
				<input type="hidden" name="crn" value="<?= e($crn) ?>">
				<input type="hidden" name="attendance_date" value="<?= e($selectedDate) ?>">

				<table>
					<tr>
						<th>Student ID</th>
						<th>Name</th>
						<th>Attendance</th>
					</tr>

					<?php if (count($roster) > 0): ?>

						<?php foreach ($roster as $student): ?>

							<tr>
								<td><?= e($student['student_ID']) ?></td>
								<td><?= e($student['first_Name'] . ' ' . $student['last_Name']) ?></td>
								<td>
									<label>
										<input
											type="radio"
											name="attendance[<?= e($student['student_ID']) ?>]"
											value="Present"
											<?= ($student['Present_Absent'] ?? '') === 'Present' ? 'checked' : '' ?>
										>
										Present
									</label>
									<label style="margin-left:12px;">
										<input
											type="radio"
											name="attendance[<?= e($student['student_ID']) ?>]"
											value="Absent"
											<?= ($student['Present_Absent'] ?? '') === 'Absent' ? 'checked' : '' ?>
										>
										Absent
									</label>
									<label style="margin-left:12px;">
										<input
											type="radio"
											name="attendance[<?= e($student['student_ID']) ?>]"
											value=""
											<?= !isset($student['Present_Absent']) ? 'checked' : '' ?>
										>
										Not Recorded
									</label>
								</td>
							</tr>

						<?php endforeach; ?>

					<?php else: ?>

						<tr>
							<td colspan="3">No students enrolled in this section.</td>
						</tr>

					<?php endif; ?>

				</table>

				<div style="margin-top: 16px; display:flex; gap:10px;">
					<button type="submit" class="btn">Save Attendance</button>
					<a href="attendance-calendar.php?crn=<?= e($crn) ?>" class="btn light">Cancel</a>
				</div>
			</form>

		<?php else: ?>

			<div class="card">
				<p class="muted"><?= e($attendanceMessage) ?></p>
			</div>

		<?php endif; ?>

	<?php endif; ?>

</section>

<section class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Calendar</p>
			<h2><?= e($monthName) ?></h2>
		</div>
	</div>

	<div style="margin-top: 12px; display:flex; gap:12px; align-items:center;">
		<a
			href="attendance-calendar.php?crn=<?= e($crn) ?>&date=<?= e($prevMonth) ?>"
			class="btn light small"
		>
			Previous Month
		</a>
		<a
			href="attendance-calendar.php?crn=<?= e($crn) ?>&date=<?= e($nextMonth) ?>"
			class="btn light small"
		>
			Next Month
		</a>
	</div>

	<table style="margin-top: 12px; width:100%; border-collapse:collapse;">
		<tr>
			<th style="padding:8px; border:1px solid #ddd; text-align:center;">Mon</th>
			<th style="padding:8px; border:1px solid #ddd; text-align:center;">Tue</th>
			<th style="padding:8px; border:1px solid #ddd; text-align:center;">Wed</th>
			<th style="padding:8px; border:1px solid #ddd; text-align:center;">Thu</th>
			<th style="padding:8px; border:1px solid #ddd; text-align:center;">Fri</th>
			<th style="padding:8px; border:1px solid #ddd; text-align:center;">Sat</th>
			<th style="padding:8px; border:1px solid #ddd; text-align:center;">Sun</th>
		</tr>

		<?php foreach ($weeks as $week): ?>

			<tr>
				<?php foreach ($week as $dayCell): ?>

					<td style="padding:8px; border:1px solid #ddd; text-align:center; vertical-align:top; height:80px;">
						<?php if ($dayCell !== null): ?>

							<div style="font-size:12px; font-weight:600;">
								<?= e($dayCell['day']) ?>
							</div>

							<div style="font-size:10px; color:#666;">
								<?= e(substr($dayCell['date'], 5)) ?>
							</div>

							<?php if ($dayCell['isToday']): ?>
								<div style="font-size:10px; color:#0a132c;">Today</div>
							<?php endif; ?>

							<?php if ($dayCell['isSelected']): ?>
								<div style="font-size:10px; color:#d6a800;">Selected</div>
							<?php endif; ?>

							<?php if ($dayCell['hasAttendance']): ?>
								<div style="font-size:10px; color:#245b2b;">Attendance Recorded</div>
							<?php endif; ?>

						<?php endif; ?>
					</td>

				<?php endforeach; ?>
			</tr>

		<?php endforeach; ?>

	</table>

</section>

<?php

page_end();

?>

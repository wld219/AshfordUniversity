<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_once __DIR__ . '/attendance-window.php';
require_once __DIR__ . '/attendance-support.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];

$semesterOptions = faculty_attendance_semesters($pdo);
$semesterId = faculty_attendance_selected_semester(
	$semesterOptions,
	$_GET['semester_ID'] ?? null
);
$semesterName = '';
foreach ($semesterOptions as $option) {
	if ((int) $option['semester_ID'] === $semesterId) {
		$semesterName = (string) $option['semester_Name'];
		break;
	}
}
$sections = faculty_attendance_sections($pdo, $facultyID, $semesterId);

// Selected CRN
$hasRequestedCrn = array_key_exists('crn', $_GET);
$requestedCrn = filter_var($_GET['crn'] ?? null, FILTER_VALIDATE_INT);
$crn = $requestedCrn !== false && $requestedCrn !== null
	? (int) $requestedCrn
	: 0;
if (!$hasRequestedCrn && $sections) {
	$crn = (int) $sections[0]['CRN'];
}

// Selected date (default today)
$selectedDate = is_string($_GET['date'] ?? null)
	? trim($_GET['date'])
	: date('Y-m-d');

// Validate the date format
if (!faculty_attendance_date_is_valid($selectedDate)) {
	$selectedDate = date('Y-m-d');
}

// Section info
$sectionInfo = faculty_attendance_section($pdo, $facultyID, $semesterId, $crn);
if (!$sectionInfo) {
	$crn = 0;
}

// Check if attendance is allowed for this date
$attendanceAllowed = false;
$attendanceMessage = '';
if ($sectionInfo) {
	[$dateAllowed, $attendanceMessage] = attendance_date_allowed(
		$pdo,
		$crn,
		$selectedDate
	);
	if ($dateAllowed && faculty_attendance_date_is_today($selectedDate)) {
		[$attendanceAllowed, $attendanceMessage] = attendance_window_status($pdo, $crn);
	} elseif ($dateAllowed) {
		$attendanceMessage = 'Past attendance is read-only. Attendance cannot be completed for a date other than today.';
	}
}

// Get enrolled students with their existing attendance for the selected date
$roster = [];
if ($sectionInfo) {
	$sectionInfo['faculty_ID'] = $facultyID;
	$roster = faculty_attendance_roster($pdo, $sectionInfo, $selectedDate);
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
		WHERE CRN = ? AND attendance_Date <= CURDATE()",
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
			<h2><?= $sectionInfo ? e($sectionInfo['course_ID'] . ' ' . $sectionInfo['course_Name']) : 'Select a Course' ?></h2>
		</div>
	</div>

	<?php if (!$sections): ?>

		<form method="get" style="margin-top: 12px;">
			<label for="attendance-semester-empty">Semester</label>
			<select name="semester_ID" id="attendance-semester-empty" onchange="this.form.submit()">
				<?php foreach ($semesterOptions as $option): ?>
					<option value="<?= e($option['semester_ID']) ?>" <?= (int)$option['semester_ID'] === $semesterId ? 'selected' : '' ?>><?= e($option['semester_Name']) ?></option>
				<?php endforeach; ?>
			</select>
			<noscript><button type="submit" class="btn">View Semester</button></noscript>
		</form>
		<p class="muted">No active course sections found for <?= e($semesterName ?: 'this semester') ?>.</p>
	<?php elseif (!$sectionInfo): ?>

		<form method="get" style="margin-top: 12px;">
			<label for="attendance-semester-unselected">Semester</label>
			<select name="semester_ID" id="attendance-semester-unselected" onchange="this.form.submit()">
				<?php foreach ($semesterOptions as $option): ?>
					<option value="<?= e($option['semester_ID']) ?>" <?= (int)$option['semester_ID'] === $semesterId ? 'selected' : '' ?>><?= e($option['semester_Name']) ?></option>
				<?php endforeach; ?>
			</select>
			<label for="attendance-crn-unselected">Course section</label>
			<select name="crn" id="attendance-crn-unselected">
				<option value="">Select a section</option>
				<?php foreach ($sections as $item): ?>
					<option value="<?= e($item['CRN']) ?>"><?= e($item['course_ID'] . ' — ' . $item['course_Name'] . ' (CRN ' . $item['CRN'] . ')') ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="btn">View Section</button>
		</form>
		<p class="muted">Select a section assigned to your Faculty account. The requested CRN is not available for this semester.</p>

	<?php else: ?>

		<form method="get" style="margin-top: 12px;">
			<div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
				<div>
					<label for="attendance-semester">Semester</label>
					<select name="semester_ID" id="attendance-semester" onchange="this.form.submit()">
						<?php foreach ($semesterOptions as $option): ?>
							<option value="<?= e($option['semester_ID']) ?>" <?= (int)$option['semester_ID'] === $semesterId ? 'selected' : '' ?>><?= e($option['semester_Name']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label for="attendance-crn">Course section</label>
					<select name="crn" id="attendance-crn" onchange="this.form.submit()">
						<?php foreach ($sections as $item): ?>
							<option value="<?= e($item['CRN']) ?>" <?= (int)$item['CRN'] === $crn ? 'selected' : '' ?>><?= e($item['course_ID'] . ' — ' . $item['course_Name'] . ' (CRN ' . $item['CRN'] . ')') ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

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
				<input type="hidden" name="semester_ID" value="<?= e($semesterId) ?>">
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
										Leave Unchanged
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
					<a href="attendance-calendar.php?semester_ID=<?= e($semesterId) ?>&crn=<?= e($crn) ?>" class="btn light">Cancel</a>
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
				href="attendance-calendar.php?semester_ID=<?= e($semesterId) ?>&crn=<?= e($crn) ?>&date=<?= e($prevMonth) ?>"
			class="btn light small"
		>
			Previous Month
		</a>
		<a
			href="attendance-calendar.php?semester_ID=<?= e($semesterId) ?>&crn=<?= e($crn) ?>&date=<?= e($nextMonth) ?>"
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

<?php /* ASHFORD_ALL_ENHANCEMENTS_V2 */ ?>
<link rel="stylesheet" href="<?= e(url('assets/ashford-enhancements/enhancements.css')) ?>">
<script defer src="<?= e(url('assets/ashford-enhancements/enhancements.js')) ?>"></script>

<?php

page_end();

?>

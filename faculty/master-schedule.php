<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);


// Semester filter (defaults to the most recent semester)
$semesters = all_rows(
	$pdo,
	"SELECT semester_ID, semester_Name FROM Semester WHERE semester_Name IN ('Fall 2026', 'Spring 2027') ORDER BY semester_ID DESC"
);

$semesterID = get_int('semester')
	?? (int) (one(
		$pdo,
		"SELECT semester_ID FROM Semester WHERE semester_Name IN ('Fall 2026', 'Spring 2027') ORDER BY semester_ID DESC LIMIT 1"
	)['semester_ID'] ?? 0);

// Load section rows the same way as the admin master schedule so both
// views stay identical (course, section, faculty, days, time, room,
// capacity, seats, enrolled, status).
$rows = $semesterID > 0
	? schedule_section_rows($pdo, $semesterID)
	: [];

page_start(
	'Master Schedule',
	'Faculty',
	'master-schedule.php'
);

?>

<div class="card">

	<form method="get" class="filters">

		<div>
			<label for="semester">Semester</label>
			<select name="semester" id="semester">
				<?php foreach ($semesters as $semester): ?>
					<option
						value="<?= e($semester['semester_ID']) ?>"
						<?= $semesterID === (int) $semester['semester_ID'] ? 'selected' : '' ?>
					>
						<?= e($semester['semester_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">View semester</button>
		</div>

	</form>

</div>

<?php if (count($rows) > 0): ?>

	<div class="table-wrap">

		<table>

			<tr>
				<th>CRN</th>
				<th>Course</th>
				<th>Section</th>
				<th>Faculty</th>
				<th>Days</th>
				<th>Time</th>
				<th>Room</th>
				<th>Capacity</th>
				<th>Seats</th>
				<th>Enrolled</th>
				<th>Status</th>
			</tr>

			<?php foreach ($rows as $row): ?>

				<?php
				$rowErrors = validate_schedule_row($row);
				$status = count($rowErrors) === 0
					? 'Scheduled'
					: 'Attention';
				?>

				<tr>
					<td><?= e((string) $row['CRN']) ?></td>
					<td><?= e((string) $row['course_ID']) ?> - <?= e((string) $row['course_Name']) ?></td>
					<td><?= e((string) $row['section_No']) ?></td>
					<td><?= e((string) $row['faculty_Name']) ?></td>
					<td><?= e(implode(', ', array_map(
						static fn(string $day): string => substr($day, 0, 3),
						$row['days']
					))) ?></td>
					<td>
						<?= e(
							$row['start_Time'] !== null && $row['end_Time'] !== null
								? substr((string) $row['start_Time'], 0, 5)
									. '-' . substr((string) $row['end_Time'], 0, 5)
								: 'TBA'
						) ?>
					</td>
					<td><?= e((string) ($row['room_Label'] ?? 'Unassigned')) ?></td>
					<td><?= e((string) $row['capacity']) ?></td>
					<td><?= e((string) $row['available_Seats']) ?></td>
					<td><?= e((string) $row['enrolled']) ?></td>
					<td><?= e($status) ?></td>
				</tr>

			<?php endforeach; ?>

		</table>

	</div>

	<p class="muted">
		Showing <?= count($rows) ?> of <?= count($rows) ?> sections.
	</p>

<?php else: ?>

	<div class="card">
		<p>No active sections are scheduled for the selected semester.</p>
	</div>

<?php endif; ?>


<?php

page_end();

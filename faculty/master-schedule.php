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
	'SELECT semester_ID, semester_Name FROM Semester WHERE semester_ID IN (1, 2) ORDER BY semester_ID DESC'
);

$semesterID = get_int('semester')
	?? (int) (one(
		$pdo,
		'SELECT semester_ID FROM Semester ORDER BY semester_ID DESC LIMIT 1'
	)['semester_ID'] ?? 0);


// Get all active course sections for the selected semester
$sections = all_rows(
	$pdo,
	"SELECT
			Course_Section.CRN,
			Course.course_ID,
			Course.course_Name,
			Course_Section.section_No,
			Semester.semester_Name,
			Course_Section.available_Seats,
			Course_Section.time_Slot_ID,
			Course_Section.lecture_ID,
			Course_Section.lab_ID,
			User.first_Name,
			User.last_Name,
			(SELECT GROUP_CONCAT(dy.day_Of_Week ORDER BY dy.day_ID SEPARATOR ', ')
			 FROM Time_Slot_Day tsd
			 JOIN Day dy ON dy.day_ID = tsd.day_ID
			 WHERE tsd.time_Slot_ID = Course_Section.time_Slot_ID) AS meeting_days,
			(SELECT CONCAT(MIN(p.start_Time), ' - ', MAX(p.end_Time))
			 FROM Time_Slot_Period tsp
			 JOIN Period p ON p.period_ID = tsp.period_ID
			 WHERE tsp.time_Slot_ID = Course_Section.time_Slot_ID) AS meeting_time
		FROM Course_Section
		JOIN Course
			ON Course.course_ID = Course_Section.course_ID
		JOIN Semester
			ON Semester.semester_ID = Course_Section.semester_ID
		JOIN User
			ON User.user_ID = Course_Section.faculty_ID
		WHERE Course_Section.semester_ID = ?
			AND Course_Section.is_Cancelled = 0
		ORDER BY
			Course.course_ID,
			Course_Section.section_No",
		[$semesterID]
	);


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


<div class="card">

	<label for="scheduleSearch">
		<strong>Search Schedule</strong>
	</label>

	<input
		type="text"
		id="scheduleSearch"
		placeholder="Search by course, faculty, or section"
	>

</div>


<?php if (count($sections) > 0): ?>

	<div class="table-wrap">

		<table id="scheduleTable">

			<thead>
				<tr>
					<th>CRN</th>
					<th>Course</th>
					<th>Title</th>
					<th>Section</th>
					<th>Faculty</th>
					<th>Days</th>
					<th>Time</th>
					<th>Location</th>
					<th>Available Seats</th>
				</tr>
			</thead>

			<tbody>

				<?php foreach ($sections as $section): ?>

					<tr>

						<td>
							<?= e($section['CRN']) ?>
						</td>

						<td>
							<?= e($section['course_ID']) ?>
						</td>

						<td>
							<?= e($section['course_Name']) ?>
						</td>

						<td>
							<?= e($section['section_No']) ?>
						</td>

						<td>
							<?= e(
								$section['first_Name'] . ' ' .
								$section['last_Name']
							) ?>
						</td>

						<td><?= e($section['meeting_days'] ?? '') ?></td>
						<td><?= e($section['meeting_time'] ?? '') ?></td>

						<td>
							<?= e(section_room_label(
								$pdo,
								$section['lecture_ID'] !== null
									? (int) $section['lecture_ID']
									: null,
								$section['lab_ID'] !== null
									? (int) $section['lab_ID']
									: null
							)) ?>
						</td>

						<td>
							<?= e($section['available_Seats']) ?>
						</td>

					</tr>

				<?php endforeach; ?>

			</tbody>

		</table>

	</div>

	<p class="muted">
		<?= count($sections) ?> active section(s) in the selected semester.
	</p>

<?php else: ?>

	<div class="card">
		<p>No active sections are scheduled for the selected semester.</p>
	</div>

<?php endif; ?>


<script>

const searchBox = document.getElementById('scheduleSearch');
const scheduleRows = document.querySelectorAll(
	'#scheduleTable tbody tr'
);

searchBox.addEventListener('input', function () {

	const searchText = searchBox.value.toLowerCase();

	scheduleRows.forEach(function (row) {

		const rowText = row.innerText.toLowerCase();

		if (rowText.includes(searchText)) {
			row.style.display = '';
		} else {
			row.style.display = 'none';
		}

	});
});

</script>


<?php

page_end();

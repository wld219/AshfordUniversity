<?php

declare(strict_types=1);

// Student master schedule view: filters and results table.
// Uses $pdo, $semesters, $departments, $semesterID, and the filter results.

?>

<div class="card">

	<h2>Master Course Schedule</h2>

	<p class="muted">
		Browse sections by semester, department, meeting day, or course. To
		register, use the Course Registration page.
	</p>

	<form method="get" class="filters" style="margin-top: 18px;">

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

		<div>
			<label for="dept">Department</label>
			<select name="dept" id="dept">
				<option value="">All</option>
				<?php foreach ($departments as $department): ?>
					<option
						value="<?= e($department['dept_ID']) ?>"
						<?= $filterDepartment === (int) $department['dept_ID'] ? 'selected' : '' ?>
					>
						<?= e($department['dept_Name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="day">Meeting day</label>
			<select name="day" id="day">
				<option value="">All</option>
				<?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day): ?>
					<option value="<?= e($day) ?>" <?= $filterDay === $day ? 'selected' : '' ?>>
						<?= e($day) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div>
			<label for="course">Course ID or name</label>
			<input
				type="search"
				id="course"
				name="course"
				value="<?= e($filterCourse) ?>"
			>
		</div>

		<div style="align-self: end;">
			<button type="submit" class="btn">Search</button>
			<a class="btn light" href="master-schedule.php">Clear</a>
		</div>

	</form>

</div>


<?php if (count($sections) > 0): ?>

	<div class="table-wrap">

		<table>

			<tr>
				<th>CRN</th>
				<th>Course</th>
				<th>Section</th>
				<th>Department</th>
				<th>Instructor</th>
				<th>Meeting time</th>
				<th>Location</th>
				<th>Credits</th>
				<th>Level</th>
				<th>Seats</th>
			</tr>

			<?php foreach ($sections as $section): ?>

				<tr>
					<td><?= e($section['CRN']) ?></td>
					<td><?= e($section['course_ID']) ?> - <?= e($section['course_Name']) ?></td>
					<td><?= e($section['section_No']) ?></td>
					<td><?= e($section['dept_Name']) ?></td>
					<td><?= e($section['instructor']) ?></td>
					<td>
						<?= e(
							substr((string) time_slot_label($pdo, (int) $section['time_Slot_ID']), 0, 60)
						) ?>
					</td>
					<td>
						<?= e(section_room_label(
							$pdo,
							$section['lecture_ID'] !== null ? (int) $section['lecture_ID'] : null,
							$section['lab_ID'] !== null ? (int) $section['lab_ID'] : null
						)) ?>
					</td>
					<td><?= e($section['course_Credits']) ?></td>
					<td><?= e($section['course_Type']) ?></td>
					<td>
						<?php if ((int) $section['available_Seats'] > 0): ?>
							<?= e($section['available_Seats']) ?> open
						<?php else: ?>
							<span class="badge">Full</span>
						<?php endif; ?>
					</td>
				</tr>

			<?php endforeach; ?>

		</table>

	</div>

	<p class="muted"><?= count($sections) ?> section(s) found.</p>

<?php else: ?>

	<div class="card">
		<p>No sections match the selected filters.</p>
	</div>

<?php endif; ?>

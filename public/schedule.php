<?php

declare(strict_types=1);

require_once __DIR__ . '/layout_public.php';
require_once __DIR__ . '/../includes/functions.php';

// Only semesters that actually have schedulable sections are offered, so
// the dropdown reflects real data. Sorted by name, which puts the terms in
// chronological order (Fall 2020 through Spring 2027); the semester_IDs
// themselves are not in chronological order.
[$semesters, $semestersError] = public_load(static fn(): array => all_rows(
	$pdo,
	"SELECT s.semester_ID, s.semester_Name
	FROM Semester s
	JOIN Course_Section cs
		ON cs.semester_ID = s.semester_ID AND cs.is_Cancelled = 0
	GROUP BY s.semester_ID, s.semester_Name
	ORDER BY s.semester_Name ASC"
));

$semesterIDs = array_map(
	static fn(array $row): int => (int) $row['semester_ID'],
	$semesters
);

[$departments, $departmentsError] = public_load(static fn(): array => all_rows(
	$pdo,
	'SELECT dept_ID, dept_Name FROM Department ORDER BY dept_Name'
));

$semesterID = get_int('semester');
$deptID = get_int('dept');

// Default to the newest term that has sections. The dropdown is the
// authority, so an unknown or absent value falls back rather than
// showing an empty term.
if ($semesterID === null || !in_array($semesterID, $semesterIDs, true)) {
	$semesterID = $semesterIDs === []
		? 0
		: (int) end($semesterIDs);
}

$sql = "SELECT
			cs.CRN,
			cs.section_No,
			cs.time_Slot_ID,
			cs.lecture_ID,
			cs.lab_ID,
			cs.available_Seats,
			c.course_ID,
			c.course_Name,
			c.course_Credits,
			c.course_Type,
			d.dept_Name,
			CONCAT(u.first_Name, ' ', u.last_Name) AS instructor,
			COALESCE(l.avail_Seats, b.no_Of_Workstations, 0) AS capacity
		FROM Course_Section cs
		JOIN Course c ON c.course_ID = cs.course_ID
		JOIN Department d ON d.dept_ID = c.dept_ID
		JOIN User u ON u.user_ID = cs.faculty_ID
		LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
		LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID
		WHERE cs.semester_ID = ?
		AND cs.is_Cancelled = 0";

$params = [$semesterID];

if ($deptID !== null) {
	$sql .= ' AND c.dept_ID = ?';
	$params[] = $deptID;
}

$sql .= ' ORDER BY d.dept_Name, c.course_ID, cs.section_No';

[$sections, $sectionsError] = public_load(
	static fn(): array => all_rows($pdo, $sql, $params)
);

[$selectedSemester, $selectedSemesterError] = public_load(
	static fn(): array => [$semesterID === 0 ? null : one(
		$pdo,
		'SELECT semester_Name FROM Semester WHERE semester_ID = ?',
		[$semesterID]
	)]
);

$semesterName = $selectedSemester[0]['semester_Name'] ?? 'Unknown semester';

public_page_start('Master Schedule');

?>

<?php if ($semestersError !== '' || $sectionsError !== '' || $selectedSemesterError !== ''): ?>

	<div class="alert error" role="alert">
		The master schedule could not be loaded. Please try again in a few
		minutes.
	</div>

<?php else: ?>

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

			<div>
				<label for="dept">Department</label>
				<select name="dept" id="dept">
					<option value="">All departments</option>
					<?php foreach ($departments as $department): ?>
						<option
							value="<?= e($department['dept_ID']) ?>"
							<?= $deptID === (int) $department['dept_ID'] ? 'selected' : '' ?>
						>
							<?= e($department['dept_Name']) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div style="align-self: end;">
				<button type="submit" class="btn">View schedule</button>
			</div>
		</form>
	</div>

	<?php if (count($sections) > 0): ?>

		<div class="card">
			<label class="public-toggle" for="open-only">
				<input type="checkbox" id="open-only" data-open-only>
				Show only sections with open seats
			</label>
			<p class="muted" id="schedule-count" role="status" data-total-sections="<?= count($sections) ?>">
				Showing <?= count($sections) ?> of <?= count($sections) ?> section(s).
			</p>
		</div>

		<div class="table-wrap">
			<table id="schedule-table">
				<caption class="visually-hidden">
					Master course schedule for <?= e($semesterName) ?> with
					meeting time, location, instructor, and open seats.
				</caption>
				<thead>
					<tr>
						<th scope="col">CRN</th>
						<th scope="col">Course</th>
						<th scope="col">Title</th>
						<th scope="col">Section</th>
						<th scope="col">Instructor</th>
						<th scope="col">Meeting time</th>
						<th scope="col">Location</th>
						<th scope="col">Credits</th>
						<th scope="col">Level</th>
						<th scope="col">Seats</th>
					</tr>
				</thead>
				<tbody>

					<?php foreach ($sections as $section): ?>

						<tr data-open="<?= (int) $section['available_Seats'] > 0 ? '1' : '0' ?>">
							<td><?= e($section['CRN']) ?></td>
							<td><?= e($section['course_ID']) ?></td>
							<td><?= e($section['course_Name']) ?></td>
							<td><?= e($section['section_No']) ?></td>
							<td><?= e($section['instructor']) ?></td>
							<td><?= e(time_slot_label($pdo, (int) $section['time_Slot_ID'])) ?></td>
							<td><?= e(section_room_label($pdo, $section['lecture_ID'] !== null ? (int) $section['lecture_ID'] : null, $section['lab_ID'] !== null ? (int) $section['lab_ID'] : null)) ?></td>
							<td><?= e($section['course_Credits']) ?></td>
							<td><?= e($section['course_Type']) ?></td>
							<td>
								<?php if ((int) $section['available_Seats'] > 0): ?>
									<?= e($section['available_Seats']) ?> of
									<?= e((int) $section['capacity']) ?> open
								<?php else: ?>
									<span class="badge">Full</span>
								<?php endif; ?>
							</td>
						</tr>

					<?php endforeach; ?>

				</tbody>
			</table>
		</div>

		<p class="muted"><?= count($sections) ?> section(s) scheduled.</p>

	<?php else: ?>

		<div class="card">
			<p>No sections are scheduled for the selected filters.</p>
		</div>

	<?php endif; ?>

<?php endif; ?>

<?php

public_page_end();

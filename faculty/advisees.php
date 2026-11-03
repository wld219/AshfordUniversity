<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

// Get the ID of the faculty member who is logged in
$facultyID = (int) $_SESSION['user_id'];

// Get all students assigned to this faculty member
$advisees = all_rows(
	$pdo,
	'SELECT
		Advisor.student_ID,
		User.first_Name,
		User.last_Name,
		Student.student_Type,
		Student.student_Year,
		Advisor.date_Of_Appnt,
		Major.major_Name,
		Department.dept_Name
	FROM Advisor
	JOIN User ON User.user_ID = Advisor.student_ID
	JOIN Student ON Student.student_ID = Advisor.student_ID
	LEFT JOIN Major ON Major.major_ID = Student.major_ID
	LEFT JOIN Department ON Department.dept_ID = Major.dept_ID
	WHERE Advisor.faculty_ID = ?
	ORDER BY User.last_Name, User.first_Name',
	[$facultyID]
);

// Current advisee load for limit display
$adviseeLoad = count($advisees);

// Students available to assign (no advisor, same department as faculty)
$facultyDepts = all_rows(
	$pdo,
	'SELECT dept_ID FROM Faculty_Department WHERE faculty_ID = ?',
	[$facultyID]
);

$deptIDs = array_map(
	static fn(array $row): int => (int) $row['dept_ID'],
	$facultyDepts
);

$availableStudents = [];

if (!empty($deptIDs)) {
	$placeholders = implode(',', array_fill(0, count($deptIDs), '?'));

	$availableStudents = all_rows(
		$pdo,
		'SELECT
			Student.student_ID,
			User.first_Name,
			User.last_Name,
			Major.major_Name,
			Department.dept_Name
		FROM Student
		JOIN User ON User.user_ID = Student.student_ID
		JOIN Major ON Major.major_ID = Student.major_ID
		JOIN Department ON Department.dept_ID = Major.dept_ID
		WHERE Major.dept_ID IN (' . $placeholders . ')
		  AND NOT EXISTS (
			  SELECT 1 FROM Advisor WHERE Advisor.student_ID = Student.student_ID
		  )
		ORDER BY User.last_Name, User.first_Name',
		$deptIDs
	);
}

// Other full-time faculty in a shared department (for swap)
$swapCandidates = all_rows(
	$pdo,
	'SELECT
		f.faculty_ID,
		CONCAT(u.first_Name, \' \', u.last_Name) AS name,
		COUNT(a.student_ID) AS advisee_count
	FROM Faculty f
	JOIN User u ON u.user_ID = f.faculty_ID
	JOIN Faculty_Department fd1 ON fd1.faculty_ID = f.faculty_ID
	JOIN Faculty_Department fd2 ON fd2.dept_ID = fd1.dept_ID AND fd2.faculty_ID = ?
	LEFT JOIN Advisor a ON a.faculty_ID = f.faculty_ID
	WHERE f.faculty_ID <> ?
	  AND f.faculty_Type = \'Full-Time\'
	GROUP BY f.faculty_ID
	HAVING COUNT(a.student_ID) < 15
	ORDER BY name',
	[$facultyID, $facultyID]
);

page_start(
	'Advisees',
	'Faculty',
	'advisees.php'
);

?>

<div class="table-wrap">

	<table>

		<tr>
			<th>Student ID</th>
			<th>Name</th>
			<th>Level</th>
			<th>Year</th>
			<th>Major</th>
			<th>Dept</th>
			<th>Appointment Date</th>
			<th>Action</th>
		</tr>

		<?php foreach ($advisees as $student): ?>

			<tr>

				<td><?= e($student['student_ID']) ?></td>

				<td><?= e($student['first_Name'] . ' ' . $student['last_Name']) ?></td>

				<td><?= e($student['student_Type']) ?></td>

				<td><?= e($student['student_Year']) ?></td>

				<td><?= e($student['major_Name'] ?? 'Undeclared') ?></td>

				<td><?= e($student['dept_Name'] ?? '—') ?></td>

				<td><?= e($student['date_Of_Appnt']) ?></td>

				<td>

					<form method="post" style="display:inline;">
						<?= csrf_field() ?>
						<input type="hidden" name="action" value="remove_advisee">
						<input type="hidden" name="student_id" value="<?= e($student['student_ID']) ?>">
						<button type="submit" class="btn danger small"
							onclick="return confirm('Remove this advisee?');">
							Remove
						</button>
					</form>

				</td>

			</tr>

		<?php endforeach; ?>

	</table>

</div>

<section class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Advising Load</p>
			<h2><?= $adviseeLoad ?> of 15 advisees</h2>
		</div>
	</div>

	<?php if ($adviseeLoad >= 15): ?>

		<p class="muted">You have reached the maximum of 15 advisees.</p>

	<?php else: ?>

		<?php if (count($availableStudents) > 0): ?>

			<form method="post" style="margin-top: 12px;">
				<?= csrf_field() ?>
				<input type="hidden" name="action" value="assign_advisee">

				<div style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
					<div>
						<label for="student_id">Assign New Advisee</label>
						<select name="student_id" id="student_id" required>
							<option value="">Select student</option>
							<?php foreach ($availableStudents as $s): ?>
								<option value="<?= e($s['student_ID']) ?>">
									<?= e($s['first_Name'] . ' ' . $s['last_Name']) ?>
									(<?= e($s['major_Name']) ?>)
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div style="align-self:end;">
						<button type="submit" class="btn">Assign</button>
					</div>
				</div>
			</form>

		<?php else: ?>

			<p class="muted">No unassigned students are available in your department(s).</p>

		<?php endif; ?>

	<?php endif; ?>

</section>

<?php if (count($swapCandidates) > 0): ?>

	<section class="card">

		<div class="dashboard-section-heading">
			<div>
				<p class="dashboard-eyebrow">Swap Advisees</p>
				<h2>Swap with Another Faculty Member</h2>
			</div>
		</div>

		<p class="muted">
			Swap one or more advisees with another full-time faculty member in
			your department. Both faculty members must have capacity (under 15
			advisees each).
		</p>

		<form method="get">
			<?= csrf_field() ?>

			<div style="display:flex; gap:10px; align-items:end; flex-wrap:wrap; margin-top: 12px;">
				<div style="min-width:200px;">
					<label for="swap_faculty">Select Faculty Member</label>
					<select name="faculty_id" id="swap_faculty" required>
						<option value="">Select faculty</option>
						<?php foreach ($swapCandidates as $c): ?>
							<option value="<?= e($c['faculty_ID']) ?>">
								<?= e($c['name']) ?>
								(<?= e($c['advisee_count']) ?> of 15 advisees)
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div style="align-self:end;">
					<button type="submit" class="btn">Choose Advisees to Swap</button>
				</div>
			</div>
		</form>

	</section>

<?php else: ?>

	<section class="card">
		<p class="muted">No other full-time faculty in your department have capacity for an advisee swap.</p>
	</section>

<?php endif;

page_end();

?>

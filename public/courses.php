<?php

declare(strict_types=1);

require_once __DIR__ . '/layout_public.php';
require_once __DIR__ . '/../includes/functions.php';

[$departments, $departmentsError] = public_load(static fn(): array => all_rows(
	$pdo,
	'SELECT dept_ID, dept_Name FROM Department ORDER BY dept_Name'
));

$deptID = get_int('dept');
$search = get_str('q', 100);

$sql = "SELECT
		c.course_ID,
		c.course_Name,
		c.course_Credits,
		c.course_Type,
		c.course_DSC,
		d.dept_Name
	FROM Course c
	JOIN Department d ON d.dept_ID = c.dept_ID
	WHERE 1 = 1";

$params = [];

if ($deptID !== null) {
	$sql .= ' AND c.dept_ID = ?';
	$params[] = $deptID;
}

if ($search !== '') {
	$sql .= ' AND (c.course_Name LIKE ? OR CAST(c.course_ID AS CHAR) LIKE ?)';
	$params[] = '%' . $search . '%';
	$params[] = '%' . $search . '%';
}

$sql .= ' ORDER BY d.dept_Name, c.course_ID';

[$courses, $coursesError] = public_load(static fn(): array => all_rows($pdo, $sql, $params));

// Prerequisites for every course shown (course_ID => labels)
[$prerequisiteRows, $prerequisitesError] = public_load(
	static fn(): array => count($courses) === 0 ? [] : all_rows(
		$pdo,
		"SELECT cp.course_ID,
				cp.min_Grade_Req,
				pre.course_ID AS prereq_ID,
				pre.course_Name AS prereq_Name
		FROM Course_Prerequisite cp
		JOIN Course pre ON pre.course_ID = cp.prerequisite_course_ID
		WHERE cp.course_ID IN (
			" . implode(',', array_fill(0, count($courses), '?')) . "
		)
		ORDER BY cp.course_ID, pre.course_ID",
		array_column($courses, 'course_ID')
	)
);

$prerequisiteMap = [];

foreach ($prerequisiteRows as $prerequisite) {
	$prerequisiteMap[(int) $prerequisite['course_ID']][] =
		$prerequisite['prereq_ID'] . ' ' . $prerequisite['prereq_Name']
		. ' (min grade ' . $prerequisite['min_Grade_Req'] . ')';
}

public_page_start('Courses & Prerequisites');

?>

<h1>Course Catalog</h1>

<p class="muted">
	Search the Ashford University catalog. Prerequisites and minimum grades
	are listed for each course.
</p>

<div class="card">
	<form method="get" class="filters">
		<div>
			<label for="q">Search</label>
			<input
				type="search"
				id="q"
				name="q"
				value="<?= e($search) ?>"
				placeholder="Course name or number"
			>
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
			<button type="submit" class="btn">Search</button>
			<a class="btn light" href="courses.php">Clear</a>
		</div>
	</form>
</div>

<?php if ($coursesError !== ''): ?>

	<div class="alert error"><?= e($coursesError) ?></div>

<?php elseif (count($courses) === 0): ?>

	<div class="card">
		<p>No courses match the selected filters.</p>
	</div>

<?php else: ?>

	<div class="table-wrap">
		<table>
			<caption class="visually-hidden">
				Ashford University course catalog with credits, level,
				prerequisites, and descriptions.
			</caption>
			<thead>
				<tr>
					<th scope="col">Course</th>
					<th scope="col">Title</th>
					<th scope="col">Department</th>
					<th scope="col">Credits</th>
					<th scope="col">Level</th>
					<th scope="col">Prerequisites</th>
					<th scope="col">Description</th>
				</tr>
			</thead>
			<tbody>

				<?php foreach ($courses as $course): ?>

					<tr>
						<td><?= e($course['course_ID']) ?></td>
						<td><?= e($course['course_Name']) ?></td>
						<td><?= e($course['dept_Name']) ?></td>
						<td><?= e($course['course_Credits']) ?></td>
						<td><?= e($course['course_Type']) ?></td>
						<td>
							<?php if ($prerequisitesError !== ''): ?>
								<span class="muted">Unavailable</span>
							<?php elseif (!empty($prerequisiteMap[(int) $course['course_ID']])): ?>
								<ul class="public-requirement-list">
									<?php foreach ($prerequisiteMap[(int) $course['course_ID']] as $label): ?>
										<li><?= e($label) ?></li>
									<?php endforeach; ?>
								</ul>
							<?php else: ?>
								<span class="muted">None</span>
							<?php endif; ?>
						</td>
						<td><?= e($course['course_DSC']) ?></td>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>
	</div>

	<p class="muted"><?= count($courses) ?> course(s) found.</p>

<?php endif; ?>

<?php

public_page_end();

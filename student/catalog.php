<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);


// Get all courses and their departments
$sql = "SELECT
			Course.course_ID,
			Course.course_Name,
			Course.course_Credits,
			Course.course_Type,
			Department.dept_Name
		FROM Course
		JOIN Department
			ON Department.dept_ID = Course.dept_ID
		ORDER BY
			Department.dept_Name,
			Course.course_ID";

$courses = all_rows(
	$pdo,
	$sql
);


// Prerequisites per course (course_ID => labels)
$prerequisiteMap = [];

if (count($courses) > 0) {

	$ids = array_column($courses, 'course_ID');

	$placeholders = implode(',', array_fill(0, count($ids), '?'));

	$prerequisites = all_rows(
		$pdo,
		"SELECT cp.course_ID,
				cp.min_Grade_Req,
				pre.course_ID AS prereq_ID,
				pre.course_Name AS prereq_Name
		FROM Course_Prerequisite cp
		JOIN Course pre ON pre.course_ID = cp.prerequisite_course_ID
		WHERE cp.course_ID IN ($placeholders)
		ORDER BY cp.course_ID, pre.course_ID",
		$ids
	);

	foreach ($prerequisites as $prerequisite) {
		$prerequisiteMap[(int) $prerequisite['course_ID']][] =
			$prerequisite['prereq_ID'] . ' ' . $prerequisite['prereq_Name']
			. ' (min grade ' . $prerequisite['min_Grade_Req'] . ')';
	}
}


page_start(
	'University Catalog',
	'Student',
	'catalog.php'
);

?>


<p class="muted">
	View university courses, departments, credits, academic levels, and
	prerequisites.
</p>


<div class="table-wrap">

	<table>

		<tr>
			<th>Course</th>
			<th>Title</th>
			<th>Department</th>
			<th>Credits</th>
			<th>Level</th>
			<th>Prerequisites</th>
		</tr>


		<?php foreach ($courses as $course): ?>

			<tr>

				<td>
					<?= e($course['course_ID']) ?>
				</td>

				<td>
					<?= e($course['course_Name']) ?>
				</td>

				<td>
					<?= e($course['dept_Name']) ?>
				</td>

				<td>
					<?= e($course['course_Credits']) ?>
				</td>

				<td>
					<?= e($course['course_Type']) ?>
				</td>

				<td>
					<?php if (!empty($prerequisiteMap[(int) $course['course_ID']])): ?>
						<ul style="margin: 0; padding-left: 18px;">
							<?php foreach ($prerequisiteMap[(int) $course['course_ID']] as $label): ?>
								<li>
									<?= e($label) ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else: ?>
						<span class="muted">None</span>
					<?php endif; ?>
				</td>

			</tr>

		<?php endforeach; ?>

	</table>

</div>


<?php

page_end();

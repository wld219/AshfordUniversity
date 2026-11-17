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

// Course offering history: which semesters each course has been offered
$offeringHistory = [];
if (count($courses) > 0) {
    $ids = array_column($courses, 'course_ID');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $offerings = all_rows(
        $pdo,
        "SELECT
                c.course_ID,
                s.semester_Name,
                COUNT(cs.CRN) AS sections_offered
            FROM Course c
            JOIN Course_Section cs ON cs.course_ID = c.course_ID
            JOIN Semester s ON s.semester_ID = cs.semester_ID
            WHERE c.course_ID IN ($placeholders)
            GROUP BY c.course_ID, s.semester_ID, s.semester_Name
            ORDER BY s.semester_ID DESC",
        $ids
    );
    foreach ($offerings as $offering) {
        $offeringHistory[(string) $offering['course_ID']][] = $offering;
    }
}

// Search filter
$search = trim((string) ($_GET['search'] ?? ''));
$search = substr($search, 0, 100);
if ($search !== '') {
    $searchLower = strtolower($search);
    $courses = array_values(
        array_filter(
            $courses,
            function(array $row) use ($searchLower) {
                return stripos((string) $row['course_ID'], $searchLower) !== false
                    || stripos((string) $row['course_Name'], $searchLower) !== false
                    || stripos((string) $row['dept_Name'], $searchLower) !== false;
            }
        )
    );
}


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
		$prerequisiteMap[(string) $prerequisite['course_ID']][] =
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

<?php if ($search !== ''): ?>
    <p class="muted" style="margin-top:8px">
        Showing results for "<span style="font-weight:600"><?= e($search) ?></span>"
        <a href="catalog.php" style="margin-left:8px">Clear search</a>
    </p>
<?php endif; ?>

<form method="get" action="catalog.php" style="margin-bottom:16px;display:flex;gap:8px;align-items:center">
    <label for="catalogSearch" style="margin:0;font-weight:600">Search courses:</label>
    <input
        type="text"
        id="catalogSearch"
        name="search"
        value="<?= e($search) ?>"
        placeholder="Course ID, title, or department"
        style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;min-width:240px"
    >
    <button type="submit" class="btn">Search</button>
</form>

<div class="table-wrap">

	<table>

		<tr>
			<th>Course</th>
			<th>Title</th>
			<th>Department</th>
			<th>Credits</th>
			<th>Level</th>
			<th>Offered In</th>
			<th>Prerequisites</th>
		</tr>


		<?php foreach ($courses as $course): $offerings = $offeringHistory[(string) $course['course_ID']] ?? [];

			$offeringNames = array_map(
				static fn($o) => (string) $o['semester_Name'],
				$offerings
			);
		?>

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
					<?php if (!empty($offeringNames)): ?>
						<?= e(implode(', ', $offeringNames)) ?>
					<?php else: ?>
						<span class="muted">Not offered</span>
					<?php endif; ?>
				</td>

				<td>
					<?php if (!empty($prerequisiteMap[(string) $course['course_ID']])): ?>
						<ul style="margin: 0; padding-left: 18px;">
							<?php foreach ($prerequisiteMap[(string) $course['course_ID']] as $label): ?>
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

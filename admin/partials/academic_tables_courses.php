<?php

declare(strict_types=1);

// Courses and prerequisites tables.

?>

<section class="card">
	<h2>Courses</h2>
	<div class="table-wrap">
		<table>
			<tr><th>ID</th><th>Course</th><th>Department</th><th>Credits</th><th>Level</th><th>Actions</th></tr>
			<?php foreach ($courses as $course): ?>
				<tr>
					<td><?= e($course['course_ID']) ?></td>
					<td><?= e($course['course_Name']) ?></td>
					<td><?= e($course['dept_Name']) ?></td>
					<td><?= e($course['course_Credits']) ?></td>
					<td><?= e($course['course_Type']) ?></td>
					<td>
						<?php if ($isFullAccess): ?>
							<a class="btn small" href="academic.php?edit_course=<?= e($course['course_ID']) ?>">Edit</a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
</section>


<section class="card">
	<h2>Prerequisites</h2>
	<div class="table-wrap">
		<table>
			<tr><th>Course</th><th>Requires</th><th>Minimum grade</th><th>Actions</th></tr>
			<?php foreach ($prerequisites as $prerequisite): ?>
				<tr>
					<td><?= e($prerequisite['course_ID']) ?> - <?= e($prerequisite['course_Name']) ?></td>
					<td><?= e($prerequisite['prerequisite_course_ID']) ?> - <?= e($prerequisite['prereq_Name']) ?></td>
					<td><?= e($prerequisite['min_Grade_Req']) ?></td>
					<td>
						<?php if ($isFullAccess): ?>
							<form method="post" style="display: inline-block;">
								<?= csrf_field() ?>
								<input type="hidden" name="action" value="remove_prerequisite">
								<input type="hidden" name="course_id" value="<?= e($prerequisite['course_ID']) ?>">
								<input type="hidden" name="prereq_id" value="<?= e($prerequisite['prerequisite_course_ID']) ?>">
								<button type="submit" class="btn danger small">Remove</button>
							</form>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
</section>

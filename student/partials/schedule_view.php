<?php

declare(strict_types=1);

// My Schedule view: registered courses and drop buttons.

?>

<p class="muted">
	View your registered courses or drop a course.
</p>


<?php if (count($schedule) > 0): ?>

	<div class="table-wrap">

		<table>

			<tr>
				<th>Course</th>
				<th>Course Name</th>
				<th>CRN</th>
				<th>Professor</th>
				<th>Credits</th>
				<th>Semester</th>
				<th>Action</th>
			</tr>


			<?php foreach ($schedule as $course): ?>

				<tr>

					<td>
						<?= e($course['course_ID']) ?>
					</td>

					<td>
						<?= e($course['course_Name']) ?>
					</td>

					<td>
						<?= e($course['CRN']) ?>
					</td>

					<td>
						<?= e(
							$course['first_Name'] . ' ' .
							$course['last_Name']
						) ?>
					</td>

					<td>
						<?= e($course['course_Credits']) ?>
					</td>

					<td>
						<?= e($course['semester_Name']) ?>
					</td>

					<td>

						<form method="post">

							<?= csrf_field() ?>

							<button
								type="submit"
								class="btn danger small"
								name="drop"
								value="<?= e($course['CRN']) ?>"
								data-confirm="Drop this course?"
							>
								Drop
							</button>

						</form>

					</td>

				</tr>

			<?php endforeach; ?>

		</table>

	</div>

<?php else: ?>

	<div class="card">
		<p>You are not currently registered for any courses.</p>
	</div>

<?php endif; ?>

<?php

declare(strict_types=1);

// Rosters view: course selector, attendance window notice, roster table.

?>

<div class="card">

	<form method="get">

		<label for="crn">
			<strong>Select Course</strong>
		</label>

		<select
			name="crn"
			id="crn"
			onchange="this.form.submit()"
		>

			<?php foreach ($sections as $section): ?>

				<option
					value="<?= e($section['CRN']) ?>"
					<?= $crn === (int) $section['CRN']
						? 'selected'
						: '' ?>
				>

					<?= e($section['course_ID']) ?>
					-
					<?= e($section['course_Name']) ?>
					(CRN <?= e($section['CRN']) ?>)

				</option>

			<?php endforeach; ?>

		</select>

	</form>

</div>


<?php if ($crn > 0): ?>

	<div class="alert <?= $attendanceOpenNow ? 'success' : 'error' ?>">
		<?= e($attendanceNotice) ?>

		<?php if ($attendanceOpenNow): ?>
			Attendance is being recorded for <?= e(date('Y-m-d')) ?>.
		<?php endif; ?>
	</div>

<?php endif; ?>


<?php if (count($roster) > 0): ?>

	<div class="table-wrap">

		<table>

			<tr>
				<th>Student</th>
				<th>Grade</th>
				<th>Attendance Today</th>
				<th>Action</th>
			</tr>


			<?php foreach ($roster as $student): ?>

				<?php
				$savedAttendance =
					$student['Present_Absent'] ?? 'Present';
				?>

				<tr>
					<td>

						<?= e($student['student_ID']) ?>
						-
						<?= e(
							$student['first_Name'] . ' ' .
							$student['last_Name']
						) ?>

					</td>


					<td>

						<form
							method="post"
							id="student-<?= e($student['student_ID']) ?>"
						>

							<?= csrf_field() ?>

							<input
								type="hidden"
								name="student_id"
								value="<?= e($student['student_ID']) ?>"
							>

							<input
								type="text"
								name="grade"
								value="<?= e($student['Grade'] ?? '') ?>"
								style="width: 90px;"
							>

					</td>


					<td>

							<select name="attendance">

								<option
									value="Present"
									<?= $savedAttendance === 'Present'
										? 'selected'
										: '' ?>
								>
									Present
								</option>

								<option
									value="Absent"
									<?= $savedAttendance === 'Absent'
										? 'selected'
										: '' ?>
								>
									Absent
								</option>

							</select>

					</td>


					<td>

							<button
								type="submit"
								class="btn small"
							>
								Save
							</button>

						</form>
					</td>

				</tr>

			<?php endforeach; ?>

		</table>

	</div>


<?php else: ?>

	<div class="card">

		<p>
			No students are currently enrolled in this course.
		</p>

	</div>

<?php endif; ?>

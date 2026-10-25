<?php

declare(strict_types=1);

// Sections table.

?>

<div class="table-wrap">

	<table>
		<tr>
			<th>CRN</th>
			<th>Course</th>
			<th>Section</th>
			<th>Instructor</th>
			<th>Meeting time</th>
			<th>Location</th>
			<th>Semester</th>
			<th>Enrolled</th>
			<th>Seats</th>
			<?php if ($isFullAccess): ?>
				<th>Actions</th>
			<?php endif; ?>
		</tr>

		<?php foreach ($sections as $section): ?>

			<tr>
				<td>
					<?= e($section['CRN']) ?>
					<?php if ((int) $section['is_Cancelled'] === 1): ?>
						<br><span class="badge">Cancelled</span>
					<?php endif; ?>
				</td>
				<td><?= e($section['course_ID']) ?> - <?= e($section['course_Name']) ?></td>
				<td><?= e($section['section_No']) ?></td>
				<td><?= e($section['instructor']) ?></td>
				<td><?= e(time_slot_label($pdo, (int) $section['time_Slot_ID'])) ?></td>
				<td>
					<?= e(section_room_label(
						$pdo,
						$section['lecture_ID'] !== null ? (int) $section['lecture_ID'] : null,
						$section['lab_ID'] !== null ? (int) $section['lab_ID'] : null
					)) ?>
				</td>
				<td><?= e($section['semester_Name']) ?></td>
				<td><?= e((int) $section['enrolled']) ?></td>
				<td><?= e((int) $section['available_Seats']) ?></td>

				<?php if ($isFullAccess): ?>

					<td>
						<a class="btn small" href="sections.php?edit=<?= e($section['CRN']) ?>">
							Edit
						</a>

						<?php if ((int) $section['is_Cancelled'] === 0): ?>

							<form
								method="post"
								style="display: inline-block;"
								onsubmit="return confirm('Cancel this section? Enrolled students will be notified by email.');"
							>
								<?= csrf_field() ?>
								<input type="hidden" name="action" value="cancel">
								<input type="hidden" name="crn" value="<?= e($section['CRN']) ?>">
								<input
									type="hidden"
									name="reason"
									value="Cancelled by administration"
								>
								<button type="submit" class="btn danger small">
									Cancel section
								</button>
							</form>

						<?php endif; ?>
					</td>

				<?php endif; ?>
			</tr>

		<?php endforeach; ?>

	</table>

</div>


<p class="muted">
	Showing up to 100 sections. Cancellation is only allowed before the
	semester starts and while fewer than 10 students are enrolled.
</p>

<?php

declare(strict_types=1);

// Section table with per-row status and pagination.

?>

<div class="table-wrap">

	<table>
		<tr>
			<th>CRN</th>
			<th>Course</th>
			<th>Section</th>
			<th>Department</th>
			<th>Faculty</th>
			<th>Days</th>
			<th>Time</th>
			<th>Room</th>
			<th>Capacity</th>
			<th>Seats</th>
			<th>Enrolled</th>
			<th>Status</th>
			<?php if ($isFullAccess && $schedule !== null): ?>
				<th>Action</th>
			<?php endif; ?>
		</tr>

		<?php if (count($pageRows) === 0): ?>

			<tr>
				<td colspan="13">No sections match the selected filters.</td>
			</tr>

		<?php else: ?>

			<?php foreach ($pageRows as $row): ?>

				<?php

				$rowErrors = validate_schedule_row($row);
				$status = count($rowErrors) === 0
					? '<span class="badge">Scheduled</span>'
					: '<span class="badge">Attention</span>';

				?>

				<tr>
					<td><?= e((string) $row['CRN']) ?></td>
					<td><?= e((string) $row['course_ID']) ?> - <?= e((string) $row['course_Name']) ?></td>
					<td><?= e((string) $row['section_No']) ?></td>
					<td><?= e((string) $row['dept_Name']) ?></td>
					<td><?= e((string) $row['faculty_Name']) ?></td>
					<td><?= e(implode(', ', array_map(
						static fn(string $day): string => substr($day, 0, 3),
						$row['days']
					))) ?></td>
					<td>
						<?= e(
							$row['start_Time'] !== null && $row['end_Time'] !== null
								? substr((string) $row['start_Time'], 0, 5)
									. '-' . substr((string) $row['end_Time'], 0, 5)
								: 'TBA'
						) ?>
					</td>
					<td><?= e((string) ($row['room_Label'] ?? 'Unassigned')) ?></td>
					<td><?= e((string) $row['capacity']) ?></td>
					<td><?= e((string) $row['available_Seats']) ?></td>
					<td><?= e((string) $row['enrolled']) ?></td>
					<td><?= $status ?></td>

					<?php if ($isFullAccess && $schedule !== null): ?>
						<td>
							<a
								class="btn small"
								href="master-schedule.php?semester=<?= e((string) $semesterID) ?>&edit=<?= e((string) $row['CRN']) ?>"
							>
								Edit
							</a>
						</td>
					<?php endif; ?>
				</tr>

			<?php endforeach; ?>

		<?php endif; ?>

	</table>

</div>


<div class="card">

	<p class="muted">
		Showing <?= count($pageRows) ?> of <?= $totalRows ?> sections
		(page <?= $page ?> of <?= $totalPages ?>).
	</p>

	<?php if ($page > 1): ?>
		<a class="btn light" href="master-schedule.php?semester=<?= e((string) $semesterID) ?>&page=<?= $page - 1 ?>">
			Previous
		</a>
	<?php endif; ?>

	<?php if ($page < $totalPages): ?>
		<a class="btn" href="master-schedule.php?semester=<?= e((string) $semesterID) ?>&page=<?= $page + 1 ?>">
			Next
		</a>
	<?php endif; ?>

</div>

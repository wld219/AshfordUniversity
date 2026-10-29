<?php

declare(strict_types=1);

// Section table with per-row status and pagination.

?>

<style>.as-schedule-table{width:100%;border-collapse:collapse;font-size:13px}.as-schedule-table th,.as-schedule-table td{padding:8px 7px;vertical-align:top;white-space:normal}.as-schedule-table th{white-space:nowrap}.as-schedule-table .as-numeric{text-align:center;white-space:nowrap}.as-schedule-table .as-room{min-width:140px}.as-schedule-table .as-name{min-width:165px}</style><div class="table-wrap">

	<table class="as-schedule-table">
		<tr>
			<th>CRN</th>
			<th>Course ID</th>
            <th>Course Name</th>
            <th>Credits</th>
			<th>Section</th>
			<th>Instructor</th>
			<th>Days</th>
			<th>Time</th>
			<th>Building / Room</th>
			<th>Capacity</th>
			<th>Enrolled</th>
			<th>Available Seats</th>
			<th>Mode</th>
<th>Status</th>
			<?php if ($isFullAccess && $schedule !== null): ?>
				<th>Action</th>
			<?php endif; ?>
		</tr>

		<?php if (count($pageRows) === 0): ?>

			<tr>
				<td colspan="<?= ($isFullAccess && $schedule !== null) ? 15 : 14 ?>">No sections match the selected filters.</td>
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
					<td><?= e((string) $row['course_ID']) ?></td>
                    <td class="as-name"><?= e((string) $row['course_Name']) ?></td>
                    <td><?= e((string) ($row['course_Credits'] ?? '—')) ?></td>
					<td><?= e((string) $row['section_No']) ?></td>
					
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
					<td class="as-room"><?= e((string) ($row['room_Label'] ?? 'Unassigned')) ?></td>
					<td class="as-numeric">10</td>
					<td class="as-numeric"><?= e((string) $row['enrolled']) ?></td>
                    <td class="as-numeric"><?= e((string) max(0, 10 - (int)$row['enrolled'])) ?></td><td><?= e((string)($row['instruction_Mode'] ?? 'In-Person')) ?></td>
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
		<a class="btn light" href="?<?= e(http_build_query(array_merge($_GET, ['semester'=>$semesterID, 'page'=>$page-1]))) ?>">
			Previous
		</a>
	<?php endif; ?>

	<?php if ($page < $totalPages): ?>
		<a class="btn" href="?<?= e(http_build_query(array_merge($_GET, ['semester'=>$semesterID, 'page'=>$page+1]))) ?>">
			Next
		</a>
	<?php endif; ?>

</div>

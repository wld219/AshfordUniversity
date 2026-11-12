<?php

declare(strict_types=1);

// Section table with per-row status and pagination.

?>
<div id="ashford-schedule-results">

<!-- AIU-layout-v1: table presentation only; edit permissions retained -->
<style>
.as-schedule-table {
    width: 100%;
    min-width: 1600px;
    border-collapse: collapse;
    font-size: 13px;
}
.as-schedule-table th,
.as-schedule-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #ddd;
    vertical-align: middle;
    text-align: left;
}
.as-schedule-table th {
    background: #f4f4f4;
    font-weight: 600;
    white-space: nowrap;
}
.as-schedule-table tbody tr:hover {
    background: #f8f8f8;
}
.as-schedule-table .as-numeric {
    text-align: center;
    white-space: nowrap;
}
.as-schedule-table .as-room {
    min-width: 80px;
    white-space: nowrap;
}
.as-schedule-table .as-name {
    min-width: 180px;
}
.as-schedule-table td:nth-child(1),
.as-schedule-table td:nth-child(2),
.as-schedule-table td:nth-child(5),
.as-schedule-table td:nth-child(8),
.as-schedule-table td:nth-child(9) {
    white-space: nowrap;
}

/* Hide Department column */
.as-schedule-table th:nth-child(13),
.as-schedule-table td:nth-child(13) {
    display: none !important;
}

</style>
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

<div class="table-wrap" style="overflow-x:auto">
<table class="as-schedule-table">
<thead><tr><th>CRN</th><th>Course ID</th><th>Credits</th><th>Course Name</th><th>Section ID</th><th>Instructor</th><th>Room</th><th>Days</th><th>Meeting Times</th><th>Minimum Seats</th><th>Seat Capacity</th><th>Actual Enrollment</th><th>Department</th><th>Mode</th><th>Status</th><?php if ($isFullAccess && $schedule !== null): ?><th>Action</th><?php endif; ?></tr></thead>
<tbody>
<?php if (count($pageRows) === 0): ?>
<tr><td colspan="<?= ($isFullAccess && $schedule !== null) ? 16 : 15 ?>">No sections match the selected filters.</td></tr>
<?php else: foreach ($pageRows as $row):
    $rowErrors = validate_schedule_row($row);
    $status = count($rowErrors) === 0 ? '<span class="badge">Scheduled</span>' : '<span class="badge">Attention</span>';
    $minimum = 10;
    $capacity = $row['capacity'] ?? $row['SeatsCapacity'] ?? $row['seats_Capacity'] ?? null;
    $deptCodes = [
    'Computer Science' => 'CS',
    'Business' => 'BUS',
    'Mathematics' => 'MAT',
    'Natural Sciences' => 'SCI',
    'Engineering' => 'ENG',
    'English' => 'ENGL',
    'Social Sciences' => 'SOC',
    'Education' => 'EDU',
    'Health Sciences' => 'HSC',
    'Arts and Media' => 'AR',
];
    $deptName = (string)($row['dept_Name'] ?? $row['department_Name'] ?? '');
    $code = $deptCodes[$deptName] ?? '';
    $courseID = $code . (string)$row['course_ID'];
    $startTime = $row['start_Time'] ?? null;
    $endTime = $row['end_Time'] ?? null;

    $meeting = ($startTime && $endTime)
        ? date('g:i A', strtotime((string)$startTime)) .
          ' - ' .
          date('g:i A', strtotime((string)$endTime))
        : 'TBA';
?>
<tr>
<td><?= e((string)$row['CRN']) ?></td>
<td><button type="button" class="ash-course-prereq-link" data-course-prereq="<?= e((string)$row['course_ID']) ?>" aria-label="View prerequisites for <?= e((string)$row['course_ID']) ?>"><?= e((string)$row['course_ID']) ?></button></td>
<td><?= e((string)($row['course_Credits'] ?? '—')) ?></td>
<td class="as-name"><?= e((string)$row['course_Name']) ?></td>
<td><?= e(str_pad((string)$row['section_No'],2,'0',STR_PAD_LEFT)) ?></td>
<td><?= e((string)$row['faculty_Name']) ?></td>
<td class="as-room"><?= e((string)($row['room_Label'] ?? 'Unassigned')) ?></td>
<td><?= e(implode(', ',array_map(static fn(string $day):string => substr($day,0,3),$row['days']))) ?></td>
<td><?= e($meeting) ?></td>
<td class="as-numeric"><?= $minimum === null ? '—' : e((string)$minimum) ?></td>
<td class="as-numeric"><?= $capacity === null ? '—' : e((string)$capacity) ?></td>
<td class="as-numeric"><?= e((string)$row['enrolled']) ?></td>
<td><?= e($deptName !== '' ? $deptName : (string)($row['dept_ID'] ?? '—')) ?></td>
<td><?= e((string)($row['instruction_Mode'] ?? 'In-Person')) ?></td>
<td><?= $status ?></td>
<?php if ($isFullAccess && $schedule !== null): ?><td><a class="btn small" href="master-schedule.php?<?= e(http_build_query(['semester'=>$semesterID,'edit'=>$row['CRN']])) ?>">Edit</a></td><?php endif; ?>
</tr>
<?php endforeach; endif; ?>
</tbody></table></div>

</div>

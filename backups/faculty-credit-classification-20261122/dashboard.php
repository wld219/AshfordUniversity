<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_once '../includes/functions/faculty_teaching_load.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];
$faculty = one(
	$pdo,
	"SELECT f.faculty_ID, f.Specialty, f.faculty_Type, u.first_Name, u.middle_Name, u.last_Name
	 FROM Faculty f
	 JOIN User u ON u.user_ID = f.faculty_ID
	 WHERE f.faculty_ID = ? AND u.user_Type = 'Faculty'",
	[$facultyID]
);

page_start('Faculty Overview', 'Faculty', 'dashboard.php');
?>
<style>
.faculty-overview {
	--fo-navy: #102b4e;
	--fo-ink: #172b45;
	--fo-muted: #52657c;
	--fo-border: #d5dfeb;
	--fo-wash: #f5f8fc;
	max-width: 1380px;
	margin: 0 auto;
	padding: clamp(18px, 2.4vw, 30px);
	color: var(--fo-ink);
}
.faculty-overview *,
.faculty-overview *::before,
.faculty-overview *::after { box-sizing: border-box; }
.faculty-overview h2,
.faculty-overview p { margin-top: 0; }
.content > h1 {
	margin: 0 0 18px;
	color: #102b4e;
	font-size: clamp(1.65rem, 2.7vw, 2.15rem);
	line-height: 1.2;
	letter-spacing: -.025em;
}
.faculty-overview .fo-card {
	min-width: 0;
	border: 1px solid var(--fo-border);
	border-radius: 10px;
	background: #fff;
}
.faculty-overview .fo-information {
	display: grid;
	grid-template-columns: minmax(220px, .85fr) minmax(0, 1.6fr);
	gap: 22px;
	align-items: center;
	padding: 20px 22px;
	margin-bottom: 16px;
}
.faculty-overview .fo-identity {
	min-width: 0;
	padding-right: 20px;
	border-right: 1px solid var(--fo-border);
}
.faculty-overview .fo-identity h2 {
	margin: 0 0 8px;
	color: var(--fo-navy);
	font-size: clamp(1.25rem, 2vw, 1.75rem);
	line-height: 1.25;
	font-weight: 700;
	overflow-wrap: anywhere;
}
.faculty-overview .fo-identity p { margin: 0; color: var(--fo-muted); }
.faculty-overview .fo-details {
	display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: 12px 20px;
	margin: 0;
}
.faculty-overview .fo-detail { min-width: 0; }
.faculty-overview .fo-detail dt {
	margin-bottom: 3px;
	color: var(--fo-muted);
	font-size: .82rem;
	font-weight: 600;
}
.faculty-overview .fo-detail dd {
	margin: 0;
	color: var(--fo-ink);
	font-weight: 650;
	line-height: 1.4;
	overflow-wrap: anywhere;
}
.faculty-overview .fo-departments { display: flex; flex-wrap: wrap; gap: 6px; }
.faculty-overview .fo-department {
	display: inline-block;
	max-width: 100%;
	padding: 3px 8px;
	border: 1px solid #d8e2ef;
	border-radius: 5px;
	background: var(--fo-wash);
	font-size: .9rem;
	font-weight: 600;
	white-space: normal;
}
.faculty-overview .fo-status {
	display: inline-block;
	padding: 4px 9px;
	border-radius: 5px;
	background: #e8f2fb;
	color: #17446e;
	font-size: .9rem;
	font-weight: 700;
}
.faculty-overview .fo-summary {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 14px;
	margin-bottom: 18px;
}
.faculty-overview .fo-metric {
	min-height: 94px;
	padding: 15px 18px;
	border-top: 3px solid var(--fo-navy);
}
.faculty-overview .fo-metric h2 {
	margin: 0 0 8px;
	color: var(--fo-muted);
	font-size: .9rem;
	font-weight: 650;
}
.faculty-overview .fo-metric p {
	margin: 0;
	color: var(--fo-navy);
	font-size: 1.8rem;
	font-weight: 750;
	font-variant-numeric: tabular-nums;
	line-height: 1;
}
.faculty-overview .fo-schedule { overflow: hidden; }
.faculty-overview .fo-schedule-heading {
	padding: 13px 18px;
	background: var(--fo-navy);
	color: #fff;
}
.faculty-overview .fo-schedule-heading h2 {
	margin: 0;
	font-size: 1.12rem;
	line-height: 1.35;
	font-weight: 700;
}
.faculty-overview .fo-table-scroll {
	width: 100%;
	overflow-x: auto;
	overscroll-behavior-inline: contain;
}
.faculty-overview table {
	width: 100%;
	min-width: 850px;
	border-collapse: collapse;
	font-size: .91rem;
}
.faculty-overview caption.sr-only {
	position: absolute;
	width: 1px;
	height: 1px;
	padding: 0;
	margin: -1px;
	overflow: hidden;
	clip: rect(0, 0, 0, 0);
	white-space: nowrap;
	border: 0;
}
.faculty-overview th,
.faculty-overview td {
	padding: 11px 12px;
	border-bottom: 1px solid #e5ebf2;
	text-align: left;
	vertical-align: top;
}
.faculty-overview th {
	background: #edf3f9;
	color: #263d58;
	font-size: .82rem;
	font-weight: 700;
	white-space: nowrap;
}
.faculty-overview tbody tr:last-child td { border-bottom: 0; }
.faculty-overview tbody tr:nth-child(even) { background: #fafbfd; }
.faculty-overview td.fo-number { font-variant-numeric: tabular-nums; white-space: nowrap; }
.faculty-overview .fo-empty,
.faculty-overview .fo-error {
	margin: 0;
	padding: 20px;
	color: var(--fo-muted);
	line-height: 1.5;
}
.faculty-overview .fo-error {
	border: 1px solid #e8c1bc;
	border-radius: 8px;
	background: #fff7f5;
	color: #762c25;
}
.faculty-overview .fo-table-scroll:focus-visible {
	outline: 3px solid #2463a0;
	outline-offset: -3px;
}
@media (max-width: 767px) {
	.faculty-overview { padding: 16px; }
	.faculty-overview .fo-information { grid-template-columns: minmax(0, 1fr); gap: 16px; padding: 17px; }
	.faculty-overview .fo-identity { padding: 0 0 14px; border-right: 0; border-bottom: 1px solid var(--fo-border); }
	.faculty-overview .fo-details { gap: 11px 14px; }
	.faculty-overview .fo-summary { grid-template-columns: minmax(0, 1fr); gap: 9px; }
	.faculty-overview .fo-metric { min-height: 76px; padding: 12px 15px; }
	.faculty-overview .fo-metric p { font-size: 1.55rem; }
}
@media (max-width: 420px) {
	.faculty-overview .fo-details { grid-template-columns: minmax(0, 1fr); }
	.faculty-overview .fo-schedule-heading { padding: 12px 14px; }
	.faculty-overview .fo-schedule-heading h2 { font-size: 1rem; }
}
@media (prefers-reduced-motion: reduce) {
	.faculty-overview *, .faculty-overview *::before, .faculty-overview *::after { scroll-behavior: auto !important; }
}
</style>

<section class="faculty-overview" aria-label="Faculty overview information">
	<?php if (!$faculty): ?>
		<div class="fo-error" role="alert">Faculty profile information is unavailable for this account. Contact an administrator for help.</div>
	<?php else: ?>
		<?php
		$loadError = false;
		$semester = null;
		$departments = [];
		$sections = [];
		$workload = ['assigned_sections' => 0, 'teaching_credits' => 0, 'status' => 'Part-Time (PT)'];
		$enrolledStudentCount = 0;
		$enrollmentCountByCRN = [];
		$meetingByTimeSlot = [];

		try {
			$semester = one($pdo, 'SELECT semester_ID, semester_Name FROM Semester WHERE semester_Name = ? LIMIT 1', ['Fall 2026']);
			$departments = faculty_departments($pdo, $facultyID);
			if ($semester) {
				$semesterID = (int) $semester['semester_ID'];
				$sections = faculty_teaching_sections($pdo, $facultyID, $semesterID);
				$workload = faculty_teaching_workload_summary($sections);

				$studentCount = one(
					$pdo,
					' SELECT COUNT(DISTINCT e.student_ID) AS total
					  FROM Course_Section cs
					  JOIN Enrollment e ON e.CRN = cs.CRN AND e.semester_ID = cs.semester_ID
					  WHERE cs.faculty_ID = ? AND cs.semester_ID = ? AND cs.is_Cancelled = 0',
					[$facultyID, $semesterID]
				);
				$enrolledStudentCount = (int) ($studentCount['total'] ?? 0);

				if ($sections) {
					$crns = array_values(array_unique(array_map(static fn(array $section): int => (int) $section['CRN'], $sections)));
					$slots = array_values(array_unique(array_map(static fn(array $section): int => (int) $section['time_Slot_ID'], $sections)));
					$crnMarks = implode(',', array_fill(0, count($crns), '?'));
					$counts = all_rows(
						$pdo,
						" SELECT cs.CRN, COUNT(DISTINCT e.student_ID) AS total
						  FROM Course_Section cs
						  LEFT JOIN Enrollment e ON e.CRN = cs.CRN AND e.semester_ID = cs.semester_ID
						  WHERE cs.faculty_ID = ? AND cs.semester_ID = ? AND cs.is_Cancelled = 0
						    AND cs.CRN IN ($crnMarks)
						  GROUP BY cs.CRN",
						array_merge([$facultyID, $semesterID], $crns)
					);
					foreach ($counts as $row) {
						$enrollmentCountByCRN[(int) $row['CRN']] = (int) $row['total'];
					}

					$slotMarks = implode(',', array_fill(0, count($slots), '?'));
					$meetingRows = all_rows(
						$pdo,
						" SELECT tsd.time_Slot_ID, d.day_ID, d.day_Of_Week,
						        p.start_Time, p.end_Time
						  FROM Time_Slot_Day tsd
						  JOIN Day d ON d.day_ID = tsd.day_ID
						  JOIN Time_Slot_Period tsp ON tsp.time_Slot_ID = tsd.time_Slot_ID
						  JOIN Period p ON p.period_ID = tsp.period_ID
						  WHERE tsd.time_Slot_ID IN ($slotMarks)
						  ORDER BY tsd.time_Slot_ID, d.day_ID, p.start_Time, p.end_Time",
						$slots
					);

					$rowsBySlot = [];
					foreach ($meetingRows as $meetingRow) {
						$slotID = (int) $meetingRow['time_Slot_ID'];
						$rowsBySlot[$slotID][] = $meetingRow;
					}
					foreach ($slots as $slotID) {
						$slotRows = $rowsBySlot[$slotID] ?? [];
						$days = [];
						$periodsByRange = [];
						foreach ($slotRows as $meetingRow) {
							$days[(int) $meetingRow['day_ID']] = (string) $meetingRow['day_Of_Week'];
							$start = (string) $meetingRow['start_Time'];
							$end = (string) $meetingRow['end_Time'];
							$periodsByRange[$start . '|' . $end] = ['start' => $start, 'end' => $end];
						}
						ksort($days);
						$periods = array_values($periodsByRange);
						usort($periods, static fn(array $left, array $right): int => strcmp($left['start'], $right['start']));
						$intervals = [];
						foreach ($periods as $period) {
							$last = count($intervals) - 1;
							if ($last >= 0 && $intervals[$last]['end'] === $period['start']) {
								$intervals[$last]['end'] = $period['end'];
							} else {
								$intervals[] = $period;
							}
						}
						$meetingByTimeSlot[$slotID] = [
							'days' => $days ? implode(', ', array_values($days)) : 'To be announced',
							'times' => $intervals
								? implode('; ', array_map(static fn(array $interval): string => format_time_range($interval['start'], $interval['end']), $intervals))
								: 'To be announced',
						];
					}
				}
			}
		} catch (Throwable $error) {
			$loadError = true;
			error_log('Faculty Overview data could not be loaded.');
		}

		$facultyName = trim(implode(' ', array_filter([
			(string) ($faculty['first_Name'] ?? ''),
			(string) ($faculty['middle_Name'] ?? ''),
			(string) ($faculty['last_Name'] ?? ''),
		], static fn(string $part): bool => $part !== '')));
		?>

		<?php if ($loadError): ?>
			<div class="fo-error" role="alert">Faculty Overview data could not be loaded. Refresh the page or contact support if the problem continues.</div>
		<?php else: ?>
			<section class="fo-card fo-information" aria-labelledby="fo-information-title">
				<div class="fo-identity">
					<h2 id="fo-information-title"><?= e($facultyName !== '' ? $facultyName : 'Name not recorded') ?></h2>
					<p>Faculty ID: <strong><?= (int) $faculty['faculty_ID'] ?></strong></p>
				</div>
				<dl class="fo-details">
					<div class="fo-detail">
						<dt>Department affiliations</dt>
						<dd>
							<?php if ($departments): ?>
								<span class="fo-departments">
									<?php foreach ($departments as $department): ?>
										<span class="fo-department"><?= e($department['dept_Name']) ?></span>
									<?php endforeach; ?>
								</span>
							<?php else: ?>
								Not recorded
							<?php endif; ?>
						</dd>
					</div>
					<div class="fo-detail">
						<dt>Specialty</dt>
						<dd><?= e(trim((string) $faculty['Specialty']) !== '' ? $faculty['Specialty'] : 'Not recorded') ?></dd>
					</div>
					<div class="fo-detail">
						<dt>Employment Type</dt>
						<dd><span class="fo-status"><?= e($faculty['faculty_Type']) ?></span></dd>
					</div>
					<div class="fo-detail">
						<dt>Current semester</dt>
						<dd><?= e($semester['semester_Name'] ?? 'Fall 2026 unavailable') ?></dd>
					</div>
				</dl>
			</section>

			<section class="fo-summary" aria-label="Fall 2026 teaching summary">
				<article class="fo-card fo-metric">
					<h2>Teaching Credits</h2>
					<p><?= (int) $workload['teaching_credits'] ?></p>
				</article>
				<article class="fo-card fo-metric">
					<h2>Assigned Sections</h2>
					<p><?= (int) $workload['assigned_sections'] ?></p>
				</article>
				<article class="fo-card fo-metric">
					<h2>Enrolled Students</h2>
					<p><?= $semester ? $enrolledStudentCount : 0 ?></p>
				</article>
			</section>

			<section class="fo-card fo-schedule" aria-labelledby="fo-schedule-title">
				<header class="fo-schedule-heading">
					<h2 id="fo-schedule-title">My Teaching Schedule — Fall 2026</h2>
				</header>
				<?php if ($sections): ?>
					<div class="fo-table-scroll" role="region" aria-label="Fall 2026 teaching schedule; scroll horizontally to view all columns" tabindex="0">
						<table>
							<caption class="sr-only">Sections assigned to the authenticated faculty member for Fall 2026</caption>
							<thead>
								<tr>
									<th scope="col">CRN</th>
									<th scope="col">Course Code</th>
									<th scope="col">Section</th>
									<th scope="col">Course Title</th>
									<th scope="col">Meeting Days</th>
									<th scope="col">Meeting Time</th>
									<th scope="col">Enrolled Students</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($sections as $section): ?>
									<?php $meeting = $meetingByTimeSlot[(int) $section['time_Slot_ID']] ?? ['days' => 'To be announced', 'times' => 'To be announced']; ?>
									<tr>
										<td class="fo-number"><?= (int) $section['CRN'] ?></td>
										<td><?= e($section['course_ID']) ?></td>
										<td class="fo-number"><?= e($section['section_No']) ?></td>
										<td><?= e($section['course_Name']) ?></td>
										<td><?= e($meeting['days']) ?></td>
										<td><?= e($meeting['times']) ?></td>
										<td class="fo-number"><?= (int) ($enrollmentCountByCRN[(int) $section['CRN']] ?? 0) ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<p class="fo-empty">No active Fall 2026 sections are assigned to your Faculty account.</p>
				<?php endif; ?>
			</section>
		<?php endif; ?>
	<?php endif; ?>
</section>

<?php page_end(); ?>

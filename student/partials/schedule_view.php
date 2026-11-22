<?php
declare(strict_types=1);
?>

<style>
.student-schedule {
	--schedule-navy: #0b2345;
	--schedule-border: #d8e1ec;
	--schedule-muted: #586a81;
	color: #17263c;
}
.student-schedule, .student-schedule * { box-sizing: border-box; }
.student-schedule .schedule-subtitle { margin: -12px 0 18px; color: var(--schedule-muted); }
.student-schedule .schedule-summary {
	display: grid;
	grid-template-columns: minmax(220px, 1.15fr) minmax(180px, .9fr) minmax(220px, 1fr);
	align-items: center;
	margin-bottom: 18px;
	color: #fff;
	background: var(--schedule-navy);
	border-radius: 10px;
	box-shadow: 0 3px 10px rgba(11,35,69,.12);
}
.student-schedule .summary-item { min-width: 0; padding: 13px 20px; }
.student-schedule .summary-item + .summary-item { border-left: 1px solid rgba(255,255,255,.3); }
.student-schedule .summary-label { display: block; margin-bottom: 6px; color: #d8e4f1; font-size: 13px; font-weight: 600; }
.student-schedule .summary-value { color: #fff; font-size: 21px; font-weight: 750; line-height: 1.25; }
.student-schedule .semester-control { display: flex; align-items: center; gap: 12px; }
.student-schedule .semester-control label { margin: 0; color: #fff; font-weight: 650; }
.student-schedule .semester-control select {
	width: min(100%, 200px);
	min-height: 42px;
	padding: 7px 34px 7px 11px;
	color: #142746;
	background: #fff;
	border: 1px solid #c8d4e3;
	border-radius: 7px;
	font: inherit;
	font-weight: 700;
}
.student-schedule .semester-control select:focus-visible,
.student-schedule .schedule-tab:focus-visible,
.student-schedule .drop-form button:focus-visible { outline: 3px solid #f0c75e; outline-offset: 2px; }
.student-schedule .sr-only {
	position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
	overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
}
.student-schedule .tab-row { display: flex; gap: 10px; padding-bottom: 11px; margin-bottom: 15px; border-bottom: 1px solid #dce4ee; }
.student-schedule .schedule-tab {
	min-height: 44px;
	padding: 9px 18px;
	color: #192c49;
	background: #f0f4f8;
	border: 1px solid #d5deea;
	border-radius: 8px;
	font: inherit;
	font-weight: 700;
	cursor: pointer;
}
.student-schedule .schedule-tab[aria-selected="true"] { color: #fff; background: var(--schedule-navy); border-color: var(--schedule-navy); }
.student-schedule .schedule-panel[hidden] { display: none !important; }
.student-schedule .weekly-layout { display: grid; grid-template-columns: minmax(0, 1.42fr) minmax(300px, .95fr); gap: 16px; align-items: stretch; }
.student-schedule .schedule-card { min-width: 0; overflow: hidden; background: #fff; border: 1px solid var(--schedule-border); border-radius: 10px; box-shadow: 0 2px 8px rgba(11,35,69,.05); }
.student-schedule .weekly-layout .schedule-card { display: flex; flex-direction: column; height: 100%; }
.student-schedule .schedule-card-title { margin: 0; padding: 11px 15px; color: #fff; background: var(--schedule-navy); font-size: 17px; line-height: 1.3; }
.student-schedule .calendar-scroll, .student-schedule .table-scroll { max-width: 100%; overflow: auto; }
.student-schedule .weekly-layout .table-scroll { flex: 1 1 auto; }
.student-schedule .calendar-scroll { padding: 0 0 8px; }
.student-schedule .calendar-grid {
	display: grid;
	grid-template-columns: 76px repeat(var(--day-count), minmax(125px, 1fr));
	min-width: var(--calendar-min-width);
	font-size: 13px;
}
.student-schedule .calendar-corner, .student-schedule .calendar-day-title { min-height: 42px; padding: 11px 6px; text-align: center; color: #142746; background: #eff3f8; border: 1px solid #dfe7f0; font-weight: 750; }
.student-schedule .calendar-time-axis { position: relative; min-height: var(--calendar-height); background: #f7f9fc; border-right: 1px solid #dfe7f0; }
.student-schedule .calendar-time-mark { position: absolute; right: 7px; transform: translateY(-50%); color: #40536d; font-size: 11px; white-space: nowrap; }
.student-schedule .calendar-day-column {
	position: relative;
	min-height: var(--calendar-height);
	background-color: #fff;
	background-image: repeating-linear-gradient(to bottom, transparent 0, transparent 59px, #e4eaf2 59px, #e4eaf2 60px);
	border-right: 1px solid #dfe7f0;
}
.student-schedule .calendar-event {
	position: absolute;
	min-width: 0;
	padding: 6px 6px 5px;
	border: 1px solid var(--event-border);
	border-left: 3px solid var(--event-border);
	border-radius: 6px;
	background: var(--event-bg);
	color: #14243c;
	font-size: 11px;
	line-height: 1.3;
	overflow: hidden;
	word-break: break-word;
}
.student-schedule .event-code { display: block; font-size: 12px; font-weight: 800; }
.student-schedule .event-section, .student-schedule .event-time { display: block; margin-top: 2px; }
.student-schedule .calendar-event[data-color="0"] { --event-bg:#e4f3fb; --event-border:#63b7dc; }
.student-schedule .calendar-event[data-color="1"] { --event-bg:#f2eafb; --event-border:#a77bd0; }
.student-schedule .calendar-event[data-color="2"] { --event-bg:#e8f4e7; --event-border:#7db874; }
.student-schedule .calendar-event[data-color="3"] { --event-bg:#fff3d5; --event-border:#d7ae43; }
.student-schedule .calendar-event[data-color="4"] { --event-bg:#fbe9e9; --event-border:#d68181; }
.student-schedule .calendar-event[data-color="5"] { --event-bg:#e9edf7; --event-border:#8295bd; }
.student-schedule .course-table { width: 100%; border-collapse: collapse; }
.student-schedule .course-table th, .student-schedule .course-table td { padding: 10px 11px; border-bottom: 1px solid #e4eaf2; text-align: left; vertical-align: top; }
.student-schedule .course-table th { color: #152a48; background: #eff3f8; font-size: 12px; font-weight: 750; white-space: nowrap; }
.student-schedule .course-table td { color: #263953; font-size: 13px; overflow-wrap: anywhere; }
.student-schedule .course-table tbody tr:last-child td { border-bottom: 0; }
.student-schedule .course-code { color: #123c70; font-weight: 800; white-space: nowrap; }
.student-schedule .credit-total { margin: 12px; padding: 11px; color: #152746; background: #e8f1fa; border-radius: 7px; text-align: center; font-weight: 750; }
.student-schedule .list-table { min-width: 1050px; }
.student-schedule .list-table td { line-height: 1.5; }
.student-schedule .drop-actions { display: flex; flex-wrap: wrap; gap: 6px; min-width: 145px; }
.student-schedule .drop-form { margin: 0; }
.student-schedule .drop-form button { padding: 5px 8px; color: #8b2634; background: #fff; border: 1px solid #dcaeb4; border-radius: 5px; font: inherit; font-size: 12px; font-weight: 700; cursor: pointer; }
.student-schedule .grade-recorded { color: #586a81; font-size: 12px; }
.student-schedule .empty-state { margin: 0; padding: 24px 18px; color: #40536d; background: #f6f8fb; line-height: 1.5; }
.student-schedule .unscheduled-note { margin: 12px; padding: 11px 13px; color: #574510; background: #fff7df; border: 1px solid #eedb9d; border-radius: 7px; font-size: 13px; }
.student-schedule .location-tba { color: #65758a; }
@media (max-width: 880px) {
	.student-schedule .weekly-layout { grid-template-columns: 1fr; }
}
@media (max-width: 900px) {
	.student-schedule .schedule-summary { grid-template-columns: 1fr; }
	.student-schedule .summary-item + .summary-item { border-top: 1px solid rgba(255,255,255,.3); border-left: 0; }
	.student-schedule .summary-item { padding: 11px 15px; }
	.student-schedule .semester-control { justify-content: space-between; }
	.student-schedule .semester-control select { flex: 0 1 220px; }
}
@media (max-width: 440px) {
	.student-schedule .tab-row { gap: 7px; }
	.student-schedule .schedule-tab { flex: 1; padding-inline: 8px; font-size: 14px; }
	.student-schedule .semester-control { align-items: flex-start; flex-direction: column; gap: 7px; }
	.student-schedule .semester-control select { width: 100%; max-width: none; }
	.student-schedule .calendar-grid { grid-template-columns: 68px repeat(var(--day-count), minmax(118px, 1fr)); min-width: var(--calendar-mobile-min-width); }
}
</style>

<div class="student-schedule" data-selected-semester="<?= (int) ($semesterID ?? 0) ?>">
	<p class="schedule-subtitle">View your enrolled courses and weekly class schedule.</p>

	<section class="schedule-summary" aria-label="Semester enrollment summary">
		<div class="summary-item">
			<span class="summary-label">Semester</span>
			<form class="semester-control" method="get" action="schedule.php">
				<label for="schedule-semester">View semester</label>
				<select id="schedule-semester" name="semester">
					<?php foreach ($availableSemesters as $term): ?>
					<option value="<?= (int) $term['semester_ID'] ?>" <?= (int) $semesterID === (int) $term['semester_ID'] ? 'selected' : '' ?>><?= e($term['semester_Name']) ?></option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="sr-only">Apply semester</button>
			</form>
		</div>
		<div class="summary-item">
			<span class="summary-label">Registered Credits</span>
			<strong class="summary-value"><?= e((string) $registeredCredits) ?></strong>
		</div>
		<div class="summary-item">
			<span class="summary-label">Semester Enrollment Status</span>
			<strong class="summary-value"><?= e($enrollmentStatus) ?></strong>
		</div>
	</section>

	<div class="tab-row" role="tablist" aria-label="Schedule view">
		<button type="button" class="schedule-tab" id="weekly-tab" role="tab" aria-controls="weekly-panel" aria-selected="true" tabindex="0">Weekly Schedule</button>
		<button type="button" class="schedule-tab" id="list-tab" role="tab" aria-controls="list-panel" aria-selected="false" tabindex="-1">List View</button>
	</div>

	<section class="schedule-panel" id="weekly-panel" role="tabpanel" aria-labelledby="weekly-tab">
		<div class="weekly-layout">
			<article class="schedule-card">
				<h2 class="schedule-card-title">Weekly Class Schedule — <?= e($semesterName) ?></h2>
				<?php if ($schedule === []): ?>
					<p class="empty-state" role="status">No courses registered for <?= e($semesterName) ?>.</p>
				<?php elseif ($allEventTimes === []): ?>
					<p class="empty-state" role="status">No meeting times are listed for your registered courses in <?= e($semesterName) ?>.</p>
				<?php else: ?>
				<div class="calendar-scroll" role="region" aria-label="Weekly class calendar; scroll horizontally to view all days" tabindex="0">
					<div class="calendar-grid" style="--day-count:<?= count($calendarDays) ?>;--calendar-height:<?= (int) $calendarHeight ?>px;--calendar-min-width:<?= 76 + count($calendarDays) * 125 ?>px;--calendar-mobile-min-width:<?= 68 + count($calendarDays) * 118 ?>px">
						<div class="calendar-corner">Time</div>
						<?php foreach ($calendarDays as $day): ?><div class="calendar-day-title"><?= e(substr($day, 0, 3)) ?></div><?php endforeach; ?>
						<div class="calendar-time-axis">
							<?php foreach ($hourMarks as $mark): ?><span class="calendar-time-mark" style="top:<?= (int) $mark['offset'] ?>px"><?= e($mark['label']) ?></span><?php endforeach; ?>
						</div>
						<?php foreach ($calendarDays as $day): ?>
						<div class="calendar-day-column" aria-label="<?= e($day) ?>">
							<?php foreach ($dayEvents[$day] ?? [] as $event):
								$eventTop = $event['start_minutes'] - $calendarStartMinute;
								$eventHeight = max(52, $event['end_minutes'] - $event['start_minutes']);
								$laneWidth = 100 / max(1, $event['lane_count']);
								$eventLeft = $event['lane'] * $laneWidth;
								$eventSectionLabel = count($event['sections']) > 1 ? 'Sections ' . implode(', ', $event['sections']) : 'Section ' . ($event['sections'][0] ?? '—');
								$eventLocation = implode('; ', $event['locations']);
							?>
							<div class="calendar-event" role="group" aria-label="<?= e($event['course_ID'] . ' — ' . $event['course_Name'] . ', ' . $eventSectionLabel . ', ' . format_time_range($event['start_Time'], $event['end_Time']) . ', ' . $eventLocation) ?>" data-color="<?= (int) $event['color'] ?>" style="top:<?= (int) $eventTop ?>px;height:<?= (int) $eventHeight ?>px;left:calc(<?= number_format($eventLeft, 4, '.', '') ?>% + 2px);width:calc(<?= number_format($laneWidth, 4, '.', '') ?>% - 4px)" title="<?= e($event['course_ID'] . ' — ' . $event['course_Name'] . ', ' . $eventSectionLabel . ', ' . format_time_range($event['start_Time'], $event['end_Time'])) ?>">
								<strong class="event-code"><?= e($event['course_ID']) ?></strong>
								<span class="event-section"><?= e($eventSectionLabel) ?></span>
								<span class="event-time"><?= e(format_time_range($event['start_Time'], $event['end_Time'])) ?></span>
							</div>
							<?php endforeach; ?>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php if ($unscheduledCourseCodes !== []): ?>
					<p class="unscheduled-note">Meeting time not listed for: <?= e(implode(', ', $unscheduledCourseCodes)) ?>. The database does not identify these as online or asynchronous courses.</p>
				<?php endif; ?>
				<?php endif; ?>
			</article>

			<article class="schedule-card">
				<h2 class="schedule-card-title">My Enrolled Courses — <?= e($semesterName) ?></h2>
				<?php if ($courseGroups === []): ?>
					<p class="empty-state" role="status">No courses registered for <?= e($semesterName) ?>.</p>
				<?php else: ?>
				<div class="table-scroll">
					<table class="course-table">
						<thead><tr><th scope="col">Course</th><th scope="col">Title</th><th scope="col">Section</th><th scope="col">Credits</th><th scope="col">Instructor</th></tr></thead>
						<tbody><?php foreach ($courseGroups as $course): ?>
						<tr><td class="course-code"><?= e($course['course_ID']) ?></td><td><?= e($course['course_Name']) ?></td><td><?= e($course['section_Display']) ?></td><td><?= e($course['credits_Display']) ?></td><td><?= e($course['instructor_Display']) ?></td></tr>
						<?php endforeach; ?></tbody>
					</table>
				</div>
				<p class="credit-total">Total Registered Credits: <?= e((string) $registeredCredits) ?></p>
				<?php endif; ?>
			</article>
		</div>
	</section>

	<section class="schedule-panel" id="list-panel" role="tabpanel" aria-labelledby="list-tab" hidden>
		<article class="schedule-card">
			<h2 class="schedule-card-title">Enrolled Courses — <?= e($semesterName) ?></h2>
			<?php if ($courseGroups === []): ?>
				<p class="empty-state" role="status">No courses registered for <?= e($semesterName) ?>.</p>
			<?php else: ?>
			<div class="table-scroll" role="region" aria-label="Enrolled course list; scroll horizontally for all columns" tabindex="0">
				<table class="course-table list-table">
					<thead><tr><th scope="col">Course Code</th><th scope="col">Course Title</th><th scope="col">Section</th><th scope="col">Credits</th><th scope="col">Instructor</th><th scope="col">Meeting Days</th><th scope="col">Meeting Times</th><th scope="col">Location</th><th scope="col">Actions</th></tr></thead>
					<tbody><?php foreach ($courseGroups as $course): ?>
					<tr>
						<td class="course-code"><?= e($course['course_ID']) ?></td>
						<td><?= e($course['course_Name']) ?></td>
						<td><?= e($course['section_Display']) ?></td>
						<td><?= e($course['credits_Display']) ?></td>
						<td><?= e($course['instructor_Display']) ?></td>
						<td><?= e($course['meetingDays_Display']) ?></td>
						<td><?= $course['meeting_summaries'] ? e($course['meeting_Display']) : 'TBA' ?></td>
						<td><?= e($course['location_Display']) ?></td>
						<td><div class="drop-actions">
							<?php foreach ($course['registrations'] as $registration): ?>
								<?php if ($registration['grade'] !== null && trim((string) $registration['grade']) !== ''): ?>
									<span class="grade-recorded">Grade recorded — cannot drop Section <?= e($registration['section']) ?></span>
								<?php else: ?>
								<form class="drop-form" method="post" action="registration.php" onsubmit="return confirm('Drop Section <?= e($registration['section']) ?> from <?= e($course['course_ID']) ?>?');">
									<?= csrf_field() ?>
									<input type="hidden" name="registration_action" value="drop">
									<input type="hidden" name="crn" value="<?= (int) $registration['CRN'] ?>">
									<button type="submit">Drop Section <?= e($registration['section']) ?></button>
								</form>
								<?php endif; ?>
							<?php endforeach; ?>
						</div></td>
					</tr>
					<?php endforeach; ?></tbody>
				</table>
			</div>
			<p class="credit-total">Total Registered Credits: <?= e((string) $registeredCredits) ?></p>
			<?php endif; ?>
		</article>
	</section>
</div>

<script>
(() => {
	const root = document.querySelector('.student-schedule');
	if (!root) return;
	const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
	const panels = Array.from(root.querySelectorAll('[role="tabpanel"]'));
	const activate = (tab, moveFocus = false) => {
		tabs.forEach((item) => {
			const selected = item === tab;
			item.setAttribute('aria-selected', String(selected));
			item.tabIndex = selected ? 0 : -1;
		});
		panels.forEach((panel) => {
			panel.hidden = panel.id !== tab.getAttribute('aria-controls');
		});
		if (moveFocus) tab.focus();
	};
	tabs.forEach((tab, index) => {
		tab.addEventListener('click', () => activate(tab));
		tab.addEventListener('keydown', (event) => {
			let next = null;
			if (event.key === 'ArrowRight' || event.key === 'ArrowDown') next = tabs[(index + 1) % tabs.length];
			if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') next = tabs[(index + tabs.length - 1) % tabs.length];
			if (event.key === 'Home') next = tabs[0];
			if (event.key === 'End') next = tabs[tabs.length - 1];
			if (next) { event.preventDefault(); activate(next, true); }
		});
	});
	const semesterForm = root.querySelector('.semester-control');
	const semesterSelect = root.querySelector('#schedule-semester');
	if (semesterForm && semesterSelect) semesterSelect.addEventListener('change', () => semesterForm.requestSubmit());
})();
</script>

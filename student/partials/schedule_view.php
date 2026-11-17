<?php

declare(strict_types=1);

// My Schedule view: registered courses, grades, meeting times, and weekly calendar.
?>

<style>
/* ASHFORD_STUDENT_SCHEDULE_V2 */
.ash-schedule .calendar-grid {
    display: grid;
    grid-template-columns: 120px repeat(5, 1fr);
    gap: 4px;
    border: 1px solid #dfe5ef;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 16px;
}
.ash-schedule .calendar-header {
    background: #10244a;
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    text-align: center;
    padding: 10px 4px;
}
.ash-schedule .calendar-cell {
    border: 1px solid #e8eaf1;
    padding: 6px;
    font-size: 12px;
    background: #fff;
    min-height: 60px;
    vertical-align: top;
}
.ash-schedule .calendar-cell h4 {
    margin: 0 0 4px;
    font-size: 13px;
    color: #142345;
}
.ash-schedule .calendar-cell p {
    margin: 0;
    font-size: 11px;
    color: #63718d;
}
.ash-schedule .calendar-cell-time {
    background: #f8f9fd;
    font-weight: 700;
    color: #1e3366;
    text-align: center;
    white-space: nowrap;
}
.ash-schedule .time-label {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    padding: 8px 4px;
    font-size: 11px;
    color: #63718d;
}

.ash-schedule .midterm-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border: 1px solid #d6a800;
    border-radius: 999px;
    background: #fdf6e3;
    color: #8a6d00;
    font-size: 11px;
    font-weight: 800;
    margin-top: 4px;
}
.ash-schedule .grade-final {
    font-weight: 700;
    color: #245b2b;
}
.ash-schedule .grade-pending {
    color: #8a6d00;
    font-style: italic;
}
</style>

<p class="muted">
	View your registered courses or drop a course.
</p>


<?php if (count($schedule) > 0): ?>

	<!-- Weekly Calendar View -->
	<section class="ash-schedule">
		<div class="calendar-grid">
			<div class="calendar-header"></div>
			<?php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']; ?>
			<?php foreach ($days as $day): ?>
				<div class="calendar-header"><?= e($day) ?></div>
			<?php endforeach; ?>

			<?php
			// Build a simple time-slot based calendar
			$periods = [];
			foreach ($schedule as $course) {
				$tsID = (int) ($course['time_Slot_ID'] ?? 0);
                if ($tsID > 0 && isset($timeSlotDetails[$tsID])) {
                    foreach ($timeSlotDetails[$tsID] as $slot) {
                        $periods[] = [
                            'day' => $slot['day_Of_Week'],
                            'start' => $slot['start_Time'],
                            'end' => $slot['end_Time'],
                            'course' => $course,
                        ];
                    }
                }
			}
			// Group by day
			$dayMap = [
				'Monday' => 0, 'Tuesday' => 1, 'Wednesday' => 2,
				'Thursday' => 3, 'Friday' => 4,
			];
			?>
			<?php foreach ($days as $dayIdx => $dayName): ?>
				<?php foreach ($periods as $period): ?>
					<?php if ($period['day'] !== $dayName) continue; ?>
					<div class="calendar-cell">
						<div class="calendar-cell-time"><?= e($period['start']) ?> – <?= e($period['end']) ?></div>
						<h4><?= e($period['course']['course_ID']) ?> (<?= e($period['course']['section_No']) ?>)</h4>
						<p><?= e($period['course']['course_Name']) ?></p>
						<p><?= e($period['course']['faculty_First_Name'] . ' ' . $period['course']['faculty_Last_Name']) ?></p>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</div>

		<p class="muted" style="margin-top:8px">Weekly calendar view of your registered courses.</p>
	</section>

	<div class="table-wrap">

		<table>

			<tr>
				<th>Course</th>
				<th>Course Name</th>
				<th>CRN</th>
				<th>Section</th>
				<th>Professor</th>
				<th>Credits</th>
				<th>Meeting Time</th>
				<th>Semester</th>
				<th>Midterm</th>
				<th>Final</th>
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
						<?= e($course['section_No'] ?? '') ?>
					</td>

					<td>
						<?= e(
							$course['faculty_First_Name'] . ' ' .
							$course['faculty_Last_Name']
						) ?>
					</td>

					<td>
						<?= e($course['course_Credits']) ?>
					</td>

					<td>
						<?= e(time_slot_label($pdo, (int) ($course['time_Slot_ID'] ?? 0))) ?>
					</td>

					<td>
						<?= e($course['semester_Name']) ?>
					</td>

					<td>
						<?php if ($course['midterm_Grade'] !== null): ?>
							<span class="midterm-badge"><?= e($course['midterm_Grade']) ?></span>
						<?php else: ?>
							<span class="grade-pending">—</span>
						<?php endif; ?>
					</td>

					<td>
						<?php if ($course['final_Grade'] !== null && trim((string) $course['final_Grade']) !== ''): ?>
							<span class="grade-final"><?= e($course['final_Grade']) ?></span>
						<?php else: ?>
							<span class="grade-pending">—</span>
						<?php endif; ?>
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

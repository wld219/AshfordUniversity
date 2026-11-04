<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);
require_once __DIR__ . '/attendance-window.php';

$facultyID = (int) $_SESSION['user_id'];
$today = new DateTimeImmutable('today', new DateTimeZone('America/New_York'));
$weekStart = $today->modify('Monday this week');
$weekEnd = $weekStart->modify('+6 days');

$sections = all_rows(
    $pdo,
    "SELECT cs.CRN, cs.course_ID, c.course_Name, s.semester_Name,
            cs.time_Slot_ID
     FROM Course_Section cs
     JOIN Course c ON c.course_ID = cs.course_ID
     JOIN Semester s ON s.semester_ID = cs.semester_ID
     WHERE cs.faculty_ID = ?
       AND cs.is_Cancelled = 0
       AND s.semester_Name = 'Fall 2026'
     ORDER BY c.course_ID, cs.section_No",
    [$facultyID]
);

$selectedCRN = (int) ($_GET['crn'] ?? 0);
$section = null;

foreach ($sections as $item) {
    if ((int) $item['CRN'] === $selectedCRN) {
        $section = $item;
        break;
    }
}

if (!$section && $selectedCRN === 0 && $sections) {
    $section = $sections[0];
}

$crn = $section ? (int) $section['CRN'] : 0;

$attendanceOpen = false;
$attendanceMessage = 'No course selected.';

if ($crn) {
    [$attendanceOpen, $attendanceMessage] =
        attendance_date_allowed(
            $pdo, $crn, $today->format('Y-m-d')
        );
}


$classTime = 'See course schedule';

if ($section) {
    try {
        $timeRows = all_rows(
            $pdo,
            "SELECT p.start_Time, p.end_Time
             FROM Time_Slot_Period tsp
             JOIN Period p ON p.period_ID = tsp.period_ID
             WHERE tsp.time_Slot_ID = ?
             ORDER BY p.start_Time",
            [(int)$section['time_Slot_ID']]
        );

        if ($timeRows) {
            $first = reset($timeRows);
            $last = end($timeRows);
            $classTime =
                date('g:i A', strtotime($first['start_Time'])) .
                ' – ' .
                date('g:i A', strtotime($last['end_Time']));
        }
    } catch (Throwable $e) {
        error_log('Attendance class time: ' . $e->getMessage());
    }
}

$roster = [];
$meetingDays = [];
$attendance = [];

if ($section) {
    $roster = all_rows(
        $pdo,
        "SELECT e.student_ID, u.first_Name, u.last_Name
         FROM Enrollment e
         JOIN User u ON u.user_ID = e.student_ID
         WHERE e.CRN = ?
         ORDER BY u.last_Name, u.first_Name, e.student_ID",
        [$crn]
    );

    $scheduledDays = all_rows(
        $pdo,
        "SELECT DISTINCT d.day_Of_Week
         FROM Time_Slot_Day tsd
         JOIN Day d ON d.day_ID = tsd.day_ID
         WHERE tsd.time_Slot_ID = ?",
        [(int) $section['time_Slot_ID']]
    );

    $dayNames = array_column($scheduledDays, 'day_Of_Week');

    for ($i = 0; $i < 7; $i++) {
        $date = $weekStart->modify("+{$i} days");
        if (
            $date->format('Y-m-d') === $today->format('Y-m-d')
            && in_array($date->format('l'), $dayNames, true)
        ) {
            [$allowed, $reason] = attendance_date_allowed(
                $pdo, $crn, $date->format('Y-m-d')
            );
            $meetingDays[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D, M j'),
                'editable' => $allowed && $attendanceOpen,
                'reason' => $attendanceOpen ? $reason : $attendanceMessage
            ];
        }
    }

    $records = all_rows(
        $pdo,
        "SELECT student_ID, attendance_Date, Present_Absent
         FROM Attendance
         WHERE CRN = ?
           AND student_ID IN (
               SELECT student_ID
               FROM Enrollment
               WHERE CRN = ?
           )
           AND attendance_Date BETWEEN ? AND ?",
        [
            $crn,
            $crn,
            $weekStart->format('Y-m-d'),
            $weekEnd->format('Y-m-d')
        ]
    );

    foreach ($records as $record) {
        $attendance[(int)$record['student_ID']]
                   [$record['attendance_Date']] =
                   $record['Present_Absent'];
    }
}

page_start("Today's Attendance", 'Faculty', 'attendance-weekly.php');
flash();
?>

<style>
.wa-card {
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 24px;
    background: #fff;
    margin-bottom: 20px;
}
.wa-heading { margin: 0 0 6px; }
.wa-muted { color: #64748b; font-size: 14px; }
.wa-controls {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    align-items: end;
    margin: 20px 0;
}
.wa-controls select {
    min-width: 280px;
    max-width: 100%;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
}
.wa-scroll { overflow-x: auto; }
.wa-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 550px;
}
.wa-table th, .wa-table td {
    padding: 13px 12px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
}
.wa-table th { background: #f8fafc; }
.wa-table .wa-center { text-align: center; }
.wa-mark {
    min-width: 95px;
    padding: 9px 12px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #f1f5f9;
    cursor: pointer;
    font-weight: 600;
}
.wa-mark.present {
    background: #dcfce7;
    border-color: #86efac;
    color: #166534;
}
.wa-mark.absent {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #991b1b;
}
.wa-disabled { color: #94a3b8; font-size: 13px; }
.wa-actions {
    margin-top: 20px;
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.wa-today {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px;
    margin: 18px 0;
}
.wa-today-label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 1px;
}
.wa-today-date {
    font-size: 22px;
    font-weight: 700;
    margin-top: 5px;
}
.wa-today-time {
    font-size: 28px;
    font-weight: 700;
    margin-top: 5px;
}
.wa-today-details {
    font-size: 14px;
    color: #475569;
    margin-top: 8px;
}
.wa-checkbox-group {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}
.wa-checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}
.wa-present-label { color: #166534; }
.wa-absent-label { color: #991b1b; }
.wa-present-label input { accent-color: #16a34a; }
.wa-absent-label input { accent-color: #dc2626; }
.wa-status-check {
    width: 17px;
    height: 17px;
    cursor: pointer;
}


/* Apple-inspired attendance interface */
.wa-course-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin: 20px 0;
}
.wa-course-tab {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 135px;
    padding: 12px 18px;
    border: 1px solid #e5e5ea;
    border-radius: 13px;
    background: #f5f5f7;
    color: #1d1d1f;
    text-decoration: none;
}
.wa-course-tab strong {
    font-size: 16px;
    font-weight: 650;
}
.wa-course-tab small {
    font-size: 12px;
    color: #6e6e73;
}
.wa-course-tab:hover {
    background: #e9e9ee;
    text-decoration: none;
}
.wa-course-tab.active {
    background: #e8f1ff;
    border-color: #8ab4f8;
    color: #174ea6;
}
.wa-scroll {
    border: 1px solid #e5e5ea;
    border-radius: 14px;
    overflow-x: auto;
}
.wa-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.wa-table th {
    background: #f5f5f7;
    font-size: 12px;
    color: #6e6e73;
    padding: 14px 16px;
}
.wa-table td {
    padding: 14px 16px;
    height: 56px;
    vertical-align: middle;
}
.wa-table td:first-child {
    font-size: 14px;
    color: #6e6e73;
    font-weight: 550;
}
.wa-table td:nth-child(2) {
    font-size: 16px;
    font-weight: 650;
    color: #1d1d1f;
}
.wa-table tbody tr:hover {
    background: #f8faff;
}
.wa-checkbox-group {
    gap: 12px;
}
.wa-checkbox-label {
    font-size: 14px;
    gap: 7px;
    padding: 7px 10px;
    border-radius: 9px;
}
.wa-present-label {
    background: #eaf8ef;
}
.wa-absent-label {
    background: #fff0f0;
}
.wa-status-check {
    width: 18px;
    height: 18px;
}

</style>

<section class="wa-card">
    <h2 class="wa-heading">Today's Attendance</h2>
    <p class="wa-muted">
        Fall 2026 · Current Week:
        <?= e($weekStart->format('M j')) ?> –
        <?= e($weekEnd->format('M j, Y')) ?>
    </p>


    <div class="wa-today">
        <div class="wa-today-label">TODAY</div>
        <?php if ($section): ?>
            <div class="wa-today-details">
                <?= e($attendanceMessage) ?>
            </div>
        <?php endif; ?>
        <div class="wa-today-date">
            <?= e($today->format('l, F j, Y')) ?>
        </div>
        <div class="wa-today-time" id="wa-live-clock">
            <?= e($today->format('g:i A')) ?>
        </div>
        <div class="wa-today-details">
            Current Week:
            <?= e($weekStart->format('M j')) ?> –
            <?= e($weekEnd->format('M j, Y')) ?>
        </div>
        <?php if ($section): ?>
            <div class="wa-today-details">
                Scheduled Class Time: <?= e($classTime) ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$sections): ?>
        <p class="wa-muted">No Fall 2026 course sections assigned.</p>
    <?php else: ?>
        
<?php if (count($sections) <= 5): ?>
    <nav class="wa-course-tabs" aria-label="Course sections">
        <?php foreach ($sections as $item): ?>
            <a
                class="wa-course-tab <?= (int)$item['CRN'] === $crn ? 'active' : '' ?>"
                href="?crn=<?= e($item['CRN']) ?>"
                <?= (int)$item['CRN'] === $crn ? 'aria-current="page"' : '' ?>
            >
                <strong><?= e($item['course_ID']) ?></strong>
                <small>CRN <?= e($item['CRN']) ?></small>
            </a>
        <?php endforeach; ?>
    </nav>
<?php else: ?>
    <form method="get" class="wa-controls">
            <div>
                <label for="crn"><strong>Course Section</strong></label>
                <select name="crn" id="crn">
                    <?php foreach ($sections as $item): ?>
                        <option value="<?= e($item['CRN']) ?>"
                            <?= (int)$item['CRN'] === $crn ? 'selected' : '' ?>>
                            <?= e($item['course_ID'] . ' — ' .
                                  $item['course_Name'] .
                                  ' (CRN ' . $item['CRN'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">View Course</button>
        </form>
<?php endif; ?>


        <?php if ($section): ?>
            <h3><?= e($section['course_ID'] . ' — ' .
                       $section['course_Name']) ?></h3>
            <p class="wa-muted">
                <?= count($roster) ?> enrolled students ·
                <?= count($meetingDays) ?> scheduled class days today
            </p>

            <?php if (!$meetingDays): ?>
                <p class="wa-muted">No scheduled meeting days found.</p>
            <?php elseif (!$roster): ?>
                <p class="wa-muted">No students enrolled.</p>
            <?php else: ?>
                <form method="post" action="attendance-weekly_actions.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="crn"
                           value="<?= e($crn) ?>">

                    <div class="wa-scroll">
                        <table class="wa-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <?php foreach ($meetingDays as $day): ?>
                                        <th class="wa-center">
                                            <?= e($day['label']) ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roster as $student): ?>
                                    <tr>
                                        <td><?= e($student['student_ID']) ?></td>
                                        <td><?= e($student['first_Name'] . ' ' .
                                                  $student['last_Name']) ?></td>

                                        <?php foreach ($meetingDays as $day): ?>
                                            <?php
                                            $value = $attendance[
                                                (int)$student['student_ID']
                                            ][$day['date']] ?? '';
                                            ?>
                                            <td class="wa-center">
                                                <?php if ($day['editable']): ?>
                                                    <div class="wa-checkbox-group">
                                                        <label class="wa-checkbox-label wa-present-label">
                                                            <input type="checkbox"
                                                                class="wa-status-check"
                                                                data-status="Present"
                                                                <?= $value === 'Present' ? 'checked' : '' ?>>
                                                            <span>Present</span>
                                                        </label>
                                                        <label class="wa-checkbox-label wa-absent-label">
                                                            <input type="checkbox"
                                                                class="wa-status-check"
                                                                data-status="Absent"
                                                                <?= $value === 'Absent' ? 'checked' : '' ?>>
                                                            <span>Absent</span>
                                                        </label>
                                                    </div>
                                                    <input type="hidden"
                                                        class="wa-status-value"
                                                        name="attendance[<?= e($student['student_ID']) ?>][<?= e($day['date']) ?>]"
                                                        value="<?= e($value) ?>">
                                                <?php else: ?>
                                                    <span class="wa-disabled"
                                                        title="<?= e($day['reason']) ?>">
                                                        <?= $value === 'Present' ? 'Present' : ($value === 'Absent' ? 'Absent' : '—') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($attendanceOpen): ?>
                    <div class="wa-actions">
                        <button type="button" class="btn light"
                                id="mark-all-present">
                            Mark All Present
                        </button>
                        <button type="submit" class="btn">
                            Save Attendance
                        </button>
                        <span class="wa-muted" id="save-hint">
                            Select a status, then save.
                        </span>
                    </div>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</section>

<script>
(function () {
    const clock = document.getElementById('wa-live-clock');

    function updateClock() {
        if (!clock) return;
        clock.textContent = new Intl.DateTimeFormat('en-US', {
            timeZone: 'America/New_York',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }).format(new Date());
    }

    updateClock();
    setInterval(updateClock, 30000);

    document.querySelectorAll('.wa-status-check').forEach(box => {
        box.addEventListener('change', () => {
            const group = box.closest('.wa-checkbox-group');
            const hidden = group.nextElementSibling;

            if (box.checked) {
                group.querySelectorAll('.wa-status-check').forEach(other => {
                    if (other !== box) other.checked = false;
                });
                hidden.value = box.dataset.status;
            } else {
                hidden.value = '';
            }

            const hint = document.getElementById('save-hint');
            if (hint) hint.textContent = 'Unsaved changes';
        });
    });

    document.getElementById('mark-all-present')?.addEventListener('click', () => {
        document.querySelectorAll('.wa-checkbox-group').forEach(group => {
            group.querySelector('[data-status="Present"]').checked = true;
            group.querySelector('[data-status="Absent"]').checked = false;
            group.nextElementSibling.value = 'Present';
        });

        const hint = document.getElementById('save-hint');
        if (hint) hint.textContent = 'Unsaved changes';
    });
})();
</script>

<link rel="stylesheet" href="<?= e(url('assets/ashford-enhancements/enhancements.css')) ?>">

<?php page_end(); ?>

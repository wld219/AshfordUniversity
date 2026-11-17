<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);
require_once '../includes/advising_workflow.php';
aw_action($pdo,'Student');

$studentID = (int) $_SESSION['user_id'];

$advisor = one(
    $pdo,
    "SELECT
        a.faculty_ID,
        a.date_Of_Appnt,
        u.first_Name,
        u.last_Name,
        l.user_Email,
        f.faculty_Rank,
        f.Specialty
     FROM Advisor a
     JOIN User u ON u.user_ID = a.faculty_ID
     JOIN Login l ON l.user_ID = u.user_ID
     JOIN Faculty f ON f.faculty_ID = a.faculty_ID
     WHERE a.student_ID = ?
     LIMIT 1",
    [$studentID]
);

$departments = [];

if ($advisor) {
    $departments = all_rows(
        $pdo,
        "SELECT DISTINCT d.dept_Name
         FROM Faculty_Department fd
         JOIN Department d ON d.dept_ID = fd.dept_ID
         WHERE fd.faculty_ID = ?
         ORDER BY d.dept_Name",
        [(string) $advisor['faculty_ID']]
    );
}

$appointments = all_rows(
    $pdo,
    "SELECT appointment_ID, slot_start AS requested_DateTime, status,
            topic AS student_Reason, notes AS advisor_Response
     FROM Appointment
     WHERE student_ID = ?
     ORDER BY created_At DESC, appointment_ID DESC",
    [$studentID]
);

$hasActiveRequest = false;

foreach ($appointments as $appointment) {
    if (in_array($appointment['status'], ['Pending', 'Scheduled'], true)) {
        $hasActiveRequest = true;
        break;
    }
}

// Get advisor's availability windows
$availabilityWindows = [];
if ($advisor) {
    $availabilityWindows = all_rows(
        $pdo,
        "SELECT fa.availability_ID, d.day_Of_Week, fa.start_Time, fa.end_Time
         FROM Faculty_Availability fa
         JOIN Day d ON d.day_ID = fa.day_ID
         WHERE fa.faculty_ID = ?
           AND fa.is_active = TRUE
         ORDER BY d.day_ID, fa.start_Time",
        [(int) $advisor['faculty_ID']]
    );
}

// Get busy slots for the advisor (existing appointments)
$busySlots = [];
if ($advisor) {
    $busySlots = all_rows(
        $pdo,
        "SELECT DATE_FORMAT(slot_start, '%Y-%m-%d') AS appt_date,
                TIME_FORMAT(slot_start, '%H:%i') AS appt_start,
                TIME_FORMAT(slot_end, '%H:%i') AS appt_end
         FROM Appointment
         WHERE faculty_ID = ?
           AND status IN ('Pending', 'Scheduled')",
        [(int) $advisor['faculty_ID']]
    );
}

$awProposals=aw_proposals($pdo,$studentID,'Student');
$awActivity=aw_activity($pdo,$studentID);
page_start('My Advisor', 'Student', 'advisor.php');
?>

<section class="card">
    <h2>Advisor Information</h2>

    <?php if ($advisor): ?>
        <p><strong>Name:</strong>
            <?= e(trim(
                ($advisor['faculty_Rank'] ?? '') . ' ' .
                $advisor['first_Name'] . ' ' .
                $advisor['last_Name']
            )) ?>
        </p>

        <p><strong>Email:</strong>
            <a href="mailto:<?= e($advisor['user_Email']) ?>"><?= e($advisor['user_Email']) ?></a>
        </p>

        <p><strong>Specialty:</strong>
            <?= e($advisor['Specialty'] ?: 'General Academic Advising') ?>
        </p>

        <p><strong>Department(s):</strong>
            <?= e(implode(', ', array_column($departments, 'dept_Name')) ?: 'Not specified') ?>
        </p>

        <p><strong>Advisor Assignment Date:</strong>
            <?= e($advisor['date_Of_Appnt']) ?>
        </p>

        <p style="margin-top:12px">
            <a href="mailto:<?= e($advisor['user_Email']) ?>" class="btn">
                📧 Email Your Advisor
            </a>
        </p>
    <?php else: ?>
        <p>No advisor has been assigned.</p>
        <p class="muted">Contact the Registrar's Office to request an advisor assignment.</p>
    <?php endif; ?>
</section>

<?php if ($advisor): ?>
<section class="card">
    <h2>Request Advising Appointment</h2>

    <?php if ($hasActiveRequest): ?>
        <p style="color:#8a6d00">⏳ You already have a pending or scheduled appointment.</p>
    <?php elseif (empty($availabilityWindows)): ?>
        <p style="color:#a83232">⚠ Your advisor has not set any availability windows yet.</p>
        <p class="muted">Please contact your advisor directly to schedule an appointment.</p>
    <?php else: ?>
        <form method="post" action="advisor_appointment.php">
            <?= csrf_field() ?>

            <div class="date-picker-wrapper">
                <label for="appointment_date">Select Date</label>
                <input
                    type="date"
                    id="appointment_date"
                    name="appointment_date"
                    min="<?= e(date('Y-m-d')) ?>"
                    required
                    onchange="updateTimeSlots()"
                >
                <span id="date-day-hint" class="date-day-hint"></span>
            </div>

            <div>
                <label for="appointment_time">Available Time Slot</label>
                <select
                    id="appointment_time"
                    name="appointment_time"
                    required
                >
                    <option value="">Select a time</option>
                </select>
            </div>

            <div>
                <label for="reason">Reason for Appointment</label>
                <textarea
                    id="reason"
                    name="reason"
                    maxlength="500"
                    rows="4"
                    placeholder="Example: Course registration or degree planning"
                    required
                ></textarea>
            </div>

            <button type="submit" class="btn">
                Request Appointment
            </button>
        </form>

        <div class="availability-info" style="margin-top:16px;padding:14px;background:#f8f9fd;border-radius:10px;border:1px solid #e0e5ef;">
            <h3 style="margin-top:0;font-size:15px;color:#1e3366">Advisor Availability</h3>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;border-bottom:2px solid #dfe5ef;">
                        <th style="padding:8px;">Day</th>
                        <th style="padding:8px;">Available Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availabilityWindows as $win): ?>
                        <tr data-day="<?= e($win['day_Of_Week']) ?>">
                            <td style="padding:8px;border-bottom:1px solid #edf0f6;"><strong><?= e($win['day_Of_Week']) ?></strong></td>
                            <td style="padding:8px;border-bottom:1px solid #edf0f6;">
                                <?= e(date('g:i A', strtotime($win['start_Time']))) ?> – <?= e(date('g:i A', strtotime($win['end_Time']))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <script>
            // Generate time slots based on selected date and advisor availability
            function updateTimeSlots() {
                const dateSelect = document.getElementById('appointment_date');
                const timeSelect = document.getElementById('appointment_time');
                const selectedDate = dateSelect.value;

                if (!selectedDate) {
                    timeSelect.innerHTML = '<option value="">Select a date first</option>';
                    return;
                }

                const [y, m, d] = selectedDate.split('-').map(Number);
                const selectedDay = new Date(y, m - 1, d).getDay();
                // Map JS day (0=Sun, 1=Mon, etc.) to DB day names
                const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const selectedDayName = dayNames[selectedDay];

                // Find matching availability windows for this day
                const availability = <?= json_encode($availabilityWindows) ?>;
                const daySlots = availability.filter(w => w.day_Of_Week === selectedDayName);

                // Get busy slots
                const busySlots = <?= json_encode($busySlots) ?>;
                const busyToday = busySlots.filter(b => b.appt_date === selectedDate);

                timeSelect.innerHTML = '<option value="">Select a time</option>';

                if (daySlots.length === 0) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No availability on this day';
                    opt.disabled = true;
                    timeSelect.appendChild(opt);
                    return;
                }

                daySlots.forEach(win => {
                    const start = parseTime(win.start_Time);
                    const end = parseTime(win.end_Time);

                    for (let t = start; t < end; t += 30) { // 30-minute slots
                        const timeStr = formatTime(t);
                        const isBusy = busyToday.some(b => b.appt_start === timeStr);
                        const opt = document.createElement('option');
                        opt.value = timeStr;
                        opt.textContent = timeStr + (isBusy ? ' (busy)' : '');
                        if (isBusy) opt.disabled = true;
                        timeSelect.appendChild(opt);
                    }
                });
            }

            function parseTime(timeStr) {
                const [h, m] = timeStr.split(':').map(Number);
                return h * 60 + m;
            }

            function formatTime(minutes) {
                const h = Math.floor(minutes / 60);
                const m = minutes % 60;
                return h.toString().padStart(2, '0') + ':' + m.toString().padStart(2, '0');
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                const dateSelect = document.getElementById('appointment_date');
                const dayHint = document.getElementById('date-day-hint');

                function syncAvailabilityUI() {
                    const selectedDate = dateSelect.value;
                    if (!selectedDate) {
                        dayHint.textContent = '';
                        return;
                    }

                    const [y, m, d] = selectedDate.split('-').map(Number);
                    const selectedDay = new Date(y, m - 1, d).getDay();
                    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    const selectedDayName = dayNames[selectedDay];
                    dayHint.textContent = 'Selected: ' + selectedDayName;

                    // Highlight matching availability row
                    const rows = document.querySelectorAll('.availability-info tbody tr[data-day]');
                    let foundMatch = false;
                    rows.forEach(row => {
                        const rowDay = row.getAttribute('data-day');
                        if (rowDay === selectedDayName) {
                            row.classList.add('availability-matched');
                            row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            foundMatch = true;
                        } else {
                            row.classList.remove('availability-matched');
                        }
                    });

                    // Show no-availability indicator
                    if (!foundMatch) {
                        dayHint.textContent += ' — No availability on this day';
                        dayHint.classList.add('no-availability');
                    } else {
                        dayHint.classList.remove('no-availability');
                    }
                }

                if (dateSelect.value) {
                    syncAvailabilityUI();
                }

                dateSelect.addEventListener('change', syncAvailabilityUI);
            });
        </script>
    <?php endif; ?>
</section>
<?php endif; ?>

<section class="card">
    <h2>Advising Appointments</h2>

    <?php if (!$appointments): ?>
        <p>No appointments scheduled.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Requested Date</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Advisor Response</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): $statusClass = $appointment['status'] === 'Scheduled' ? 'status-scheduled' : ($appointment['status'] === 'Pending' ? 'status-pending' : 'status-completed'); ?>
                        <tr>
                            <td><?= e($appointment['appointment_ID']) ?></td>
                            <td><?= e($appointment['requested_DateTime']) ?></td>
                            <td>
                                <span class="status-badge status-<?= e(strtolower($appointment['status'])) ?>">
                                    <?= e($appointment['status']) ?>
                                </span>
                            </td>
                            <td><?= e($appointment['student_Reason'] ?? '—') ?></td>
                            <td><?= e($appointment['advisor_Response'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<style>
/* ASHFORD_STUDENT_ADVISOR_V2 */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}
.status-badge.status-pending {
    background: #fdf6e3;
    color: #8a6d00;
    border: 1px solid #f0e0b3;
}
.status-badge.status-scheduled {
    background: #f0f7f0;
    color: #245b2b;
    border: 1px solid #c6e0c6;
}
.status-badge.status-completed {
    background: #f3f3f3;
    color: #6b6b6b;
    border: 1px solid #d9d9d9;
}

/* Date picker + availability synchronization */
.date-picker-wrapper {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
}

.date-day-hint {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.date-day-hint.no-availability {
    color: #b3261e;
    background: #fdedec;
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #f5c6c7;
    display: inline-block;
}

.availability-info tbody tr.availability-matched {
    background: #eef4ff !important;
    border-left: 4px solid #2563eb;
}

.availability-info tbody tr.availability-matched td {
    border-bottom-color: #bfdbfe !important;
}
</style>

<?php page_end(); ?>

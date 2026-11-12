<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];

// Student's major department
$majorRow = one($pdo, 'SELECT major_ID FROM Student WHERE student_ID = ?', [$studentID]);
$majorID = (int) ($majorRow['major_ID'] ?? 0);
$major = one($pdo, 'SELECT dept_ID, major_Name FROM Major WHERE major_ID = ?', [$majorID]);
$deptID = (int) ($major['dept_ID'] ?? 0);

// Aligned advisors (full-time faculty in the same department) with active availability
$advisors = all_rows(
    $pdo,
    "SELECT f.faculty_ID, CONCAT(u.first_Name, ' ', u.last_Name) AS name, f.faculty_Rank
        FROM Faculty f
        JOIN User u ON u.user_ID = f.faculty_ID
        JOIN Faculty_Department fd ON fd.faculty_ID = f.faculty_ID
        JOIN Faculty_Availability fa ON fa.faculty_ID = f.faculty_ID AND fa.is_active = 1
        WHERE fd.dept_ID = ? AND f.faculty_Type = 'Full-Time'
        GROUP BY f.faculty_ID, u.first_Name, u.last_Name, f.faculty_Rank
        ORDER BY u.last_Name, u.first_Name",
    [$deptID]
);

// Availability windows per advisor, grouped by day
$availabilityByAdvisor = [];
foreach ($advisors as $a) {
    $windows = all_rows(
        $pdo,
        "SELECT fa.availability_ID, d.day_Of_Week, fa.start_Time, fa.end_Time
            FROM Faculty_Availability fa
            JOIN Day d ON d.day_ID = fa.day_ID
            WHERE fa.faculty_ID = ? AND fa.is_active = 1
            ORDER BY d.day_ID, fa.start_Time",
        [$a['faculty_ID']]
    );
    $availabilityByAdvisor[$a['faculty_ID']] = $windows;
}

// My appointments
$appointments = all_rows(
    $pdo,
    "SELECT a.appointment_ID, a.slot_start, a.slot_end, a.topic, a.status, a.reason,
            CONCAT(u.first_Name, ' ', u.last_Name) AS advisor_Name
        FROM Appointment a
        JOIN User u ON u.user_ID = a.faculty_ID
        WHERE a.student_ID = ?
        ORDER BY a.slot_start DESC",
    [$studentID]
);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_verify();
    $action = post_str('action', 20);

    if ($action === 'request') {

        $facultyID = post_int('faculty_id') ?? 0;
        $appointmentDate = post_date('appointment_date');
        $startTime = post_str('start_time', 10);
        $endTime = post_str('end_time', 10);
        $topic = post_str('topic', 255);

        // Validate advisor alignment
        $aligned = one(
            $pdo,
            "SELECT 1 FROM Faculty_Department WHERE faculty_ID = ? AND dept_ID = ?",
            [$facultyID, $deptID]
        );

        if ($facultyID <= 0 || !$aligned) {
            $error = 'Selected advisor is not aligned with your major department.';
        } elseif ($appointmentDate === null) {
            $error = 'Please select a valid date.';
        } elseif ($startTime === '' || $endTime === '') {
            $error = 'Please provide a start and end time.';
        } elseif ($startTime >= $endTime) {
            $error = 'Start time must be before end time.';
        } else {

            $dayOfWeek = (int) date('N', strtotime($appointmentDate)); // 1=Mon ... 7=Sun
            $slotStart = $appointmentDate . ' ' . $startTime;
            $slotEnd = $appointmentDate . ' ' . $endTime;

            // Must be Monday-Friday (school days)
            if ($dayOfWeek < 1 || $dayOfWeek > 5) {
                $error = 'Appointments may only be scheduled Monday through Friday.';
            }

            // Must fall within school hours (08:00-17:00)
            elseif ($startTime < '08:00:00' || $endTime > '17:00:00') {
                $error = 'Appointments must be within school hours (08:00-17:00).';
            }

            // Must fall within an active availability window for this faculty on this day
            elseif ($error === '') {
                $window = one(
                    $pdo,
                    "SELECT 1 FROM Faculty_Availability fa
                        JOIN Day d ON d.day_ID = fa.day_ID
                        WHERE fa.faculty_ID = ?
                          AND d.day_Of_Week = ?
                          AND fa.is_active = 1
                          AND fa.start_Time <= ?
                          AND fa.end_Time >= ?
                        LIMIT 1",
                    [$facultyID, date('l', strtotime($appointmentDate)), $startTime, $endTime]
                );
                if (!$window) {
                    $error = 'The selected time is not within the advisor\'s availability.';
                }
            }

            // Student must not have a conflicting appointment with this faculty
            if ($error === '') {
                $conflict = one(
                    $pdo,
                    "SELECT 1 FROM Appointment
                        WHERE student_ID = ? AND faculty_ID = ?
                          AND status IN ('Pending','Accepted','Completed')
                          AND slot_start < ? AND slot_end > ?
                        LIMIT 1",
                    [$studentID, $facultyID, $slotEnd, $slotStart]
                );
                if ($conflict) {
                    $error = 'You already have an appointment with this advisor that overlaps the selected time.';
                }
            }

            // Faculty must not have a class at this time
            if ($error === '') {
                $classConflict = one(
                    $pdo,
                    "SELECT 1 FROM Course_Section cs
                        JOIN Time_Slot_Day tsd ON tsd.time_Slot_ID = cs.time_Slot_ID
                        JOIN Time_Slot_Period tsp ON tsp.time_Slot_ID = cs.time_Slot_ID
                        JOIN Period p ON p.period_ID = tsp.period_ID
                        JOIN Day d ON d.day_ID = tsd.day_ID
                        WHERE cs.faculty_ID = ?
                          AND cs.is_Cancelled = 0
                          AND d.day_Of_Week = ?
                          AND p.start_Time < ? AND ? < p.end_Time
                        LIMIT 1",
                    [$facultyID, date('l', strtotime($appointmentDate)), $startTime, $endTime]
                );
                if ($classConflict) {
                    $error = 'The advisor has a class at the selected time.';
                }
            }

            if ($error === '') {
                try {
                    $pdo->prepare(
                        "INSERT INTO Appointment (student_ID, faculty_ID, slot_start, slot_end, topic, status)
                         VALUES (?, ?, ?, ?, ?, 'Pending')"
                    )->execute([$studentID, $facultyID, $slotStart, $slotEnd, $topic]);
                    $success = 'Appointment request submitted.';
                    $appointments = all_rows(
                        $pdo,
                        "SELECT a.appointment_ID, a.slot_start, a.slot_end, a.topic, a.status, a.reason,
                                CONCAT(u.first_Name, ' ', u.last_Name) AS advisor_Name
                            FROM Appointment a
                            JOIN User u ON u.user_ID = a.faculty_ID
                            WHERE a.student_ID = ?
                            ORDER BY a.slot_start DESC",
                        [$studentID]
                    );
                } catch (Throwable $e) {
                    error_log('Create appointment failed: ' . $e->getMessage());
                    $error = 'Could not create appointment request.';
                }
            }
        }
    }
}

page_start('Appointments', 'Student', 'appointments.php');

flash();

?>

<div class="card">
    <h2>Request an Appointment</h2>

    <?php if ($error !== ''): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <div class="alert success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if (count($advisors) === 0): ?>
        <p class="muted">No aligned advisors with availability are available for your department.</p>
    <?php else: ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="request">

            <div>
                <label for="faculty_id">Advisor</label>
                <select name="faculty_id" id="faculty_id" required>
                    <option value="">Select advisor</option>
                    <?php foreach ($advisors as $a): ?>
                        <option value="<?= e($a['faculty_ID']) ?>">
                            <?= e($a['name']) ?> (<?= e($a['faculty_Rank']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="muted" style="font-size:12px; margin-top:4px;">Only advisors with active availability appear.</p>
            </div>

            <div>
                <label for="appointment_date">Date</label>
                <input type="date" name="appointment_date" id="appointment_date" required>
            </div>

            <div>
                <label for="start_time">Start time</label>
                <input type="time" name="start_time" id="start_time" required>
            </div>

            <div>
                <label for="end_time">End time</label>
                <input type="time" name="end_time" id="end_time" required>
            </div>

            <div>
                <label for="topic">Topic</label>
                <input type="text" name="topic" id="topic" maxlength="255">
            </div>

            <div style="margin-top: 12px;">
                <button type="submit" class="btn">Request Appointment</button>
            </div>
        </form>

        <?php if (count($availabilityByAdvisor) > 0): ?>
            <div style="margin-top: 24px;">
                <h3>Advisor Availability</h3>
                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>Advisor</th>
                            <th>Day</th>
                            <th>Start</th>
                            <th>End</th>
                        </tr>
                        <?php foreach ($advisors as $a): ?>
                            <?php $windows = $availabilityByAdvisor[$a['faculty_ID']] ?? []; ?>
                            <?php if (count($windows) > 0): ?>
                                <?php foreach ($windows as $w): ?>
                                    <tr>
                                        <td><?= e($a['name']) ?></td>
                                        <td><?= e($w['day_Of_Week']) ?></td>
                                        <td><?= e($w['start_Time']) ?></td>
                                        <td><?= e($w['end_Time']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td><?= e($a['name']) ?></td>
                                    <td colspan="3" class="muted">No availability set</td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="card">
    <h2>My Appointments</h2>

    <?php if (count($appointments) === 0): ?>
        <p class="muted">No appointments yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Advisor</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Topic</th>
                    <th>Status</th>
                    <th>Reason</th>
                </tr>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= e($a['advisor_Name']) ?></td>
                        <td><?= e($a['slot_start']) ?></td>
                        <td><?= e($a['slot_end']) ?></td>
                        <td><?= e($a['topic'] ?? '') ?></td>
                        <td><?= e($a['status']) ?></td>
                        <td><?= e($a['reason'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php page_end();

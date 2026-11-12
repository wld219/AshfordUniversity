<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];

$error = '';
$success = '';

// Faculty's pending/accepted appointments that can be rescheduled
$appointments = all_rows(
    $pdo,
    "SELECT a.appointment_ID, a.student_ID, a.slot_start, a.slot_end, a.status, a.topic, a.reason,
            CONCAT(u.first_Name, ' ', u.last_Name) AS student_Name
        FROM Appointment a
        JOIN User u ON u.user_ID = a.student_ID
        WHERE a.faculty_ID = ?
          AND a.status IN ('Pending','Accepted')
        ORDER BY a.slot_start ASC",
    [$facultyID]
);

// Active availability windows for this faculty (used to validate reschedule targets)
$availability = all_rows(
    $pdo,
    "SELECT fa.availability_ID, d.day_ID, d.day_Of_Week, fa.start_Time, fa.end_Time
        FROM Faculty_Availability fa
        JOIN Day d ON d.day_ID = fa.day_ID
        WHERE fa.faculty_ID = ? AND fa.is_active = 1
        ORDER BY d.day_ID, fa.start_Time",
    [$facultyID]
);

// ---------- Reschedule an appointment ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_verify();
    $action = post_str('action', 20);

    if ($action === 'reschedule') {

        $appointmentID = post_int('appointment_id') ?? 0;
        $appointmentDate = post_date('appointment_date');
        $startTime = post_str('start_time', 10);
        $endTime = post_str('end_time', 10);
        $topic = post_str('topic', 255);

        // Verify the appointment belongs to this faculty and is reschedulable
        $appt = one(
            $pdo,
            "SELECT appointment_ID, student_ID, faculty_ID, status, slot_start, slot_end
                FROM Appointment
                WHERE appointment_ID = ? AND faculty_ID = ?
                LIMIT 1",
            [$appointmentID, $facultyID]
        );

        if (!$appt) {
            $error = 'Appointment not found.';
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

            // Must be Monday-Friday
            if ($dayOfWeek < 1 || $dayOfWeek > 5) {
                $error = 'Appointments may only be scheduled Monday through Friday.';
            }

            // Within school hours
            elseif ($startTime < '08:00:00' || $endTime > '17:00:00') {
                $error = 'Appointments must be within school hours (08:00-17:00).';
            }

            // Within an active availability window for this faculty on this day
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
                    $error = 'The selected time is not within your availability.';
                }
            }

            // No overlap with this faculty's own class sections
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
                    $error = 'You have a class at the selected time.';
                }
            }

            // No overlap with another accepted/pending appointment for this faculty
            if ($error === '') {
                $apptConflict = one(
                    $pdo,
                    "SELECT 1 FROM Appointment
                        WHERE appointment_ID <> ?
                          AND faculty_ID = ?
                          AND status IN ('Pending','Accepted','Completed')
                          AND slot_start < ? AND slot_end > ?
                        LIMIT 1",
                    [$appointmentID, $facultyID, $slotEnd, $slotStart]
                );
                if ($apptConflict) {
                    $error = 'You already have another appointment overlapping the selected time.';
                }
            }

            if ($error === '') {
                try {
                    $pdo->prepare(
                        'UPDATE Appointment
                         SET slot_start = ?, slot_end = ?, topic = ?, updated_At = NOW()
                         WHERE appointment_ID = ? AND faculty_ID = ?'
                    )->execute([$slotStart, $slotEnd, $topic, $appointmentID, $facultyID]);
                    $success = 'Appointment rescheduled.';
                    $appointments = all_rows(
                        $pdo,
                        "SELECT a.appointment_ID, a.student_ID, a.slot_start, a.slot_end, a.status, a.topic, a.reason,
                                CONCAT(u.first_Name, ' ', u.last_Name) AS student_Name
                            FROM Appointment a
                            JOIN User u ON u.user_ID = a.student_ID
                            WHERE a.faculty_ID = ?
                              AND a.status IN ('Pending','Accepted')
                            ORDER BY a.slot_start ASC",
                        [$facultyID]
                    );
                } catch (Throwable $e) {
                    error_log('Reschedule appointment failed: ' . $e->getMessage());
                    $error = 'Could not reschedule the appointment.';
                }
            }
        }
    }
}

page_start('Reschedule Appointments', 'Faculty', 'advisee_swap.php');

flash();

?>

<?php if ($error !== ''): ?>
    <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success !== ''): ?>
    <div class="alert success"><?= e($success) ?></div>
<?php endif; ?>

<div class="card">
    <div class="dashboard-section-heading">
        <div>
            <p class="dashboard-eyebrow">Appointments</p>
            <h2>Reschedule Appointments</h2>
        </div>
    </div>

    <p class="muted" style="margin-top: 10px;">
        Move a pending or accepted advisement appointment to a new time inside
        your availability. Rescheduled appointments keep their current status.
    </p>

    <?php if (count($appointments) === 0): ?>
        <p class="muted">No pending or accepted appointments to reschedule.</p>
    <?php else: ?>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>Student</th>
                    <th>Current Start</th>
                    <th>Current End</th>
                    <th>Topic</th>
                    <th>Status</th>
                    <th>Reschedule</th>
                </tr>

                <?php foreach ($appointments as $a): ?>

                    <tr>
                        <td><?= e($a['student_Name']) ?></td>
                        <td><?= e($a['slot_start']) ?></td>
                        <td><?= e($a['slot_end']) ?></td>
                        <td><?= e($a['topic'] ?? '') ?></td>
                        <td><?= e($a['status']) ?></td>
                        <td>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Reschedule this appointment?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="reschedule">
                                <input type="hidden" name="appointment_id" value="<?= e($a['appointment_ID']) ?>">
                                <input type="hidden" name="topic" value="<?= e($a['topic'] ?? '') ?>">

                                <div style="display:flex; gap:6px; align-items:center;">
                                    <input type="date" name="appointment_date" required>
                                    <input type="time" name="start_time" required>
                                    <input type="time" name="end_time" required>
                                    <button type="submit" class="btn small">Save</button>
                                </div>
                            </form>
                        </td>
                    </tr>

                <?php endforeach; ?>

            </table>
        </div>

    <?php endif; ?>
</div>

<?php if (count($availability) > 0): ?>

    <div class="card" style="margin-top: 24px;">
        <div class="dashboard-section-heading">
            <div>
                <p class="dashboard-eyebrow">Your Availability</p>
                <h2>Active Windows</h2>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>Day</th>
                    <th>Start</th>
                    <th>End</th>
                </tr>

                <?php foreach ($availability as $w): ?>
                    <tr>
                        <td><?= e($w['day_Of_Week']) ?></td>
                        <td><?= e($w['start_Time']) ?></td>
                        <td><?= e($w['end_Time']) ?></td>
                    </tr>
                <?php endforeach; ?>

            </table>
        </div>
    </div>

<?php else: ?>

    <div class="card" style="margin-top: 24px;">
        <p class="muted">You have no active availability windows. Set them at <a href="availability.php">My Availability</a> so appointments can be rescheduled.</p>
    </div>

<?php endif;

page_end();

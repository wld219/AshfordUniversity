<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];

// ---------------------------------------------------------------------------
// Availability window management
// ---------------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avail_action'])) {
    csrf_verify();
    $availAction = (string) $_POST['avail_action'];

    if ($availAction === 'add') {
        $dayID = filter_var($_POST['day_id'] ?? null, FILTER_VALIDATE_INT);
        $startTime = trim((string) ($_POST['start_time'] ?? ''));
        $endTime = trim((string) ($_POST['end_time'] ?? ''));

        if (!$dayID || !$startTime || !$endTime || $startTime >= $endTime) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid availability window.'];
        } else {
            try {
                $pdo->prepare(
                    'INSERT INTO Faculty_Availability (faculty_ID, day_ID, start_Time, end_Time, is_active)
                     VALUES (?, ?, ?, ?, TRUE)'
                )->execute([$facultyID, $dayID, $startTime, $endTime]);
                audit_log($pdo, 'Create', 'Faculty_Availability', (string) $dayID, 'Faculty availability added');
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Availability window added.'];
            } catch (Throwable $e) {
                error_log('Add availability failed: ' . $e->getMessage());
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not add availability window.'];
            }
        }
        header('Location: ' . url('faculty/advising_appointments.php'));
        exit;
    }

    if ($availAction === 'update') {
        $availID = filter_var($_POST['avail_id'] ?? null, FILTER_VALIDATE_INT);
        $dayID = filter_var($_POST['day_id'] ?? null, FILTER_VALIDATE_INT);
        $startTime = trim((string) ($_POST['start_time'] ?? ''));
        $endTime = trim((string) ($_POST['end_time'] ?? ''));
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (!$availID || !$dayID || !$startTime || !$endTime || $startTime >= $endTime) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid availability window.'];
        } else {
            try {
                $pdo->prepare(
                    'UPDATE Faculty_Availability
                     SET day_ID = ?, start_Time = ?, end_Time = ?, is_active = ?, updated_At = NOW()
                     WHERE availability_ID = ? AND faculty_ID = ?'
                )->execute([$dayID, $startTime, $endTime, $isActive, $availID, $facultyID]);
                audit_log($pdo, 'Update', 'Faculty_Availability', (string) $availID, 'Faculty availability updated');
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Availability window updated.'];
            } catch (Throwable $e) {
                error_log('Update availability failed: ' . $e->getMessage());
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not update availability window.'];
            }
        }
        header('Location: ' . url('faculty/advising_appointments.php'));
        exit;
    }

    if ($availAction === 'delete') {
        $availID = filter_var($_POST['avail_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$availID) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid availability window.'];
        } else {
            try {
                $pdo->prepare(
                    'DELETE FROM Faculty_Availability WHERE availability_ID = ? AND faculty_ID = ?'
                )->execute([$availID, $facultyID]);
                audit_log($pdo, 'Delete', 'Faculty_Availability', (string) $availID, 'Faculty availability removed');
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Availability window removed.'];
            } catch (Throwable $e) {
                error_log('Delete availability failed: ' . $e->getMessage());
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not remove availability window.'];
            }
        }
        header('Location: ' . url('faculty/advising_appointments.php'));
        exit;
    }
}

// Handle appointment actions (accept/deny/complete/cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appt_action'])) {
    csrf_verify();
    $apptID = filter_var($_POST['appointment_id'] ?? null, FILTER_VALIDATE_INT);
    $action = (string) $_POST['appt_action'];
    $response = trim((string) ($_POST['response'] ?? ''));

    if (!$apptID) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid appointment.'];
    } else {
        try {
            $pdo->beginTransaction();
            $appt = one(
                $pdo,
                'SELECT * FROM Appointment WHERE appointment_ID = ? AND faculty_ID = ? FOR UPDATE',
                [$apptID, $facultyID]
            );
            if (!$appt) {
                throw new RuntimeException('Appointment not found.');
            }

            if ($action === 'accept') {
                if ($appt['status'] !== 'Pending') {
                    throw new RuntimeException('Only pending appointments can be accepted.');
                }
                $pdo->prepare(
                    'UPDATE Appointment SET status = ?, reason = ? WHERE appointment_ID = ?'
                )->execute(['Accepted', $response ?: null, $apptID]);
            } elseif ($action === 'deny') {
                if ($appt['status'] !== 'Pending') {
                    throw new RuntimeException('Only pending appointments can be denied.');
                }
                $pdo->prepare(
                    'UPDATE Appointment SET status = ?, reason = ? WHERE appointment_ID = ?'
                )->execute(['Denied', $response ?: null, $apptID]);
            } elseif ($action === 'complete') {
                if ($appt['status'] !== 'Accepted') {
                    throw new RuntimeException('Only accepted appointments can be completed.');
                }
                $pdo->prepare(
                    'UPDATE Appointment SET status = ?, reason = ? WHERE appointment_ID = ?'
                )->execute(['Completed', $response ?: null, $apptID]);
            } elseif ($action === 'cancel') {
                if (!in_array($appt['status'], ['Pending', 'Accepted'], true)) {
                    throw new RuntimeException('This appointment cannot be cancelled.');
                }
                $pdo->prepare(
                    'UPDATE Appointment SET status = ? WHERE appointment_ID = ?'
                )->execute(['Cancelled', $apptID]);
            } else {
                throw new RuntimeException('Invalid action.');
            }

            audit_log($pdo, 'Update', 'Appointment', (string) $apptID, "Faculty $action appointment");
            $pdo->commit();
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Appointment $action successfully."];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Appointment action failed: ' . $e->getMessage());
            $_SESSION['flash'] = ['type' => 'error', 'message' => $e instanceof RuntimeException ? $e->getMessage() : 'Unable to update appointment.'];
        }
        header('Location: ' . url('faculty/advising_appointments.php'));
        exit;
    }
}

// Load current availability windows
$availabilityWindows = all_rows(
    $pdo,
    "SELECT fa.availability_ID, fa.day_ID, d.day_Of_Week, fa.start_Time, fa.end_Time, fa.is_active
     FROM Faculty_Availability fa
     JOIN Day d ON d.day_ID = fa.day_ID
     WHERE fa.faculty_ID = ?
     ORDER BY fa.day_ID, fa.start_Time",
    [$facultyID]
) ?? [];

// Load days for the dropdown
$days = all_rows($pdo, 'SELECT day_ID, day_Of_Week FROM Day ORDER BY day_ID') ?? [];

// Load appointments for this faculty member
$appointments = all_rows(
    $pdo,
    "SELECT
        ap.appointment_ID,
        ap.student_ID,
        ap.slot_start AS requested_DateTime,
        ap.status,
        ap.topic AS student_Reason,
        ap.notes AS advisor_Response,
        u.first_Name,
        u.last_Name,
        m.major_Name
     FROM Appointment ap
     JOIN User u ON u.user_ID = ap.student_ID
     JOIN Student s ON s.student_ID = ap.student_ID
     LEFT JOIN Major m ON m.major_ID = s.major_ID
     WHERE ap.faculty_ID = ?
     ORDER BY
         CASE WHEN ap.status = 'Pending' THEN 0 ELSE 1 END,
         ap.slot_start DESC",
    [$facultyID]
) ?? [];

page_start(
    'Advising Appointments',
    'Faculty',
    'advising_appointments.php'
);
?>

<style>
/* ASHFORD_FACULTY_AVAILABILITY_V2 */
.availability-section {
    background: #fff;
    border: 1px solid #dfe5ef;
    border-radius: 13px;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 4px 16px rgba(16,36,74,.045);
}
.availability-section h3 {
    margin: 0 0 16px;
    font-size: 19px;
    color: #142345;
}
.availability-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}
.availability-card {
    background: #f8f9fd;
    border: 1px solid #e0e5ef;
    border-radius: 11px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.availability-card.active {
    border-color: #1e3366;
    background: #f0f5ff;
}
.availability-card.inactive {
    border-color: #d1d5db;
    background: #f7f7f7;
    opacity: 0.85;
}
.availability-day {
    font-weight: 700;
    color: #1e3366;
    font-size: 15px;
}
.availability-times {
    color: #334155;
    font-size: 14px;
    font-family: monospace;
}
.availability-status {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}
.availability-status.active { color: #245b2b; }
.availability-status.inactive { color: #a83232; }
.availability-actions {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}
.availability-actions button {
    flex: 1;
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 7px;
    border: 1px solid #cbd5e1;
    background: white;
    cursor: pointer;
    font-weight: 600;
}
.availability-actions button.edit { color: #1e3366; border-color: #1e3366; }
.availability-actions button.edit:hover { background: #1e3366; color: white; }
.availability-actions button.delete { color: #a83232; border-color: #a83232; }
.availability-actions button.delete:hover { background: #a83232; color: white; }

.availability-form {
    background: #f8f9fd;
    border: 1px solid #e0e5ef;
    border-radius: 13px;
    padding: 20px;
    margin-top: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    align-items: end;
}
.availability-form label {
    display: block;
    font-size: 13px;
    font-weight: 650;
    color: #334155;
    margin-bottom: 6px;
}
.availability-form select,
.availability-form input[type="time"] {
    width: 100%;
    min-height: 42px;
    padding: 9px 11px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: white;
    font-size: 14px;
}
.availability-form .form-actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 10px;
}
</style>

<?php flash(); ?>

<section class="card">
    <h2>Advising Appointments</h2>
    <p>Review appointment requests submitted by your advisees.</p>

    <?php if (!$appointments): ?>
        <p>No appointment requests have been submitted.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Major</th>
                        <th>Requested Time</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Response / Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td>
                                <?= e(
                                    $appointment['first_Name'] . ' ' .
                                    $appointment['last_Name']
                                ) ?>
                                <br>
                                <?= e($appointment['student_ID']) ?>
                            </td>
                            <td><?= e($appointment['major_Name'] ?? 'Undeclared') ?></td>
                            <td><?= e($appointment['requested_DateTime']) ?></td>
                            <td><?= e($appointment['status']) ?></td>
                            <td><?= e($appointment['student_Reason'] ?? '—') ?></td>
                            <td>
                                <?php if ($appointment['status'] === 'Pending'): ?>
                                    <form method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="appt_action" value="accept">
                                        <input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_ID']) ?>">
                                        <label>Response</label>
                                        <textarea name="response" maxlength="500" rows="2" placeholder="Optional response"></textarea>
                                        <button type="submit" class="btn">Accept</button>
                                        <button type="submit" name="appt_action" value="deny" class="btn danger small">Deny</button>
                                    </form>
                                <?php elseif ($appointment['status'] === 'Accepted'): ?>
                                    <form method="post" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="appt_action" value="complete">
                                        <input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_ID']) ?>">
                                        <label>Response</label>
                                        <textarea name="response" maxlength="500" rows="2" placeholder="Notes"></textarea>
                                        <button type="submit" class="btn">Mark Complete</button>
                                    </form>
                                <?php else: ?>
                                    <?= e($appointment['advisor_Response'] ?? '—') ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- Availability Window Management -->
<section class="availability-section">
    <h3>Availability Window</h3>
    <p>Set when you are available for advising appointments. Students can only request appointments during your available times.</p>

    <div class="availability-form">
        <form method="post" action="advising_appointments.php">
            <?= csrf_field() ?>
            <input type="hidden" name="avail_action" value="add">

            <div>
                <label for="avail_day">Day</label>
                <select name="day_id" id="avail_day" required>
                    <option value="">Select day</option>
                    <?php foreach ($days as $day): ?>
                        <option value="<?= e($day['day_ID']) ?>"><?= e($day['day_Of_Week']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="avail_start">Start Time</label>
                <input type="time" name="start_time" id="avail_start" required>
            </div>

            <div>
                <label for="avail_end">End Time</label>
                <input type="time" name="end_time" id="avail_end" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Add Availability</button>
            </div>
        </form>
    </div>

    <?php if ($availabilityWindows): ?>
        <h4 style="margin: 16px 0 8px; font-size: 15px; color: #142345;">Your Availability Windows</h4>
        <div class="availability-grid">
            <?php foreach ($availabilityWindows as $win): $isActive = (int) $win['is_active'] === 1; ?>
                <div class="availability-card <?= $isActive ? 'active' : 'inactive' ?>">
                    <div class="availability-day"><?= e($win['day_Of_Week']) ?></div>
                    <div class="availability-times">
                        <?= date('g:i A', strtotime($win['start_Time'])) ?> – <?= date('g:i A', strtotime($win['end_Time'])) ?>
                    </div>
                    <div class="availability-status <?= $isActive ? 'active' : 'inactive' ?>">
                        <?= $isActive ? 'Active' : 'Inactive' ?>
                    </div>
                    <div class="availability-actions">
                        <form method="post" style="display:inline; margin:0;" onsubmit="return confirm('Toggle this availability window?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="avail_action" value="update">
                            <input type="hidden" name="avail_id" value="<?= e($win['availability_ID']) ?>">
                            <input type="hidden" name="day_id" value="<?= e($win['day_ID']) ?>">
                            <input type="hidden" name="start_time" value="<?= e($win['start_Time']) ?>">
                            <input type="hidden" name="end_time" value="<?= e($win['end_Time']) ?>">
                            <input type="hidden" name="is_active" value="<?= $isActive ? 0 : 1 ?>">
                            <button type="submit" class="btn edit" style="padding:4px 8px;font-size:11px"><?= $isActive ? 'Deactivate' : 'Activate' ?></button>
                        </form>
                        <form method="post" style="display:inline; margin:0;" onsubmit="return confirm('Delete this availability window?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="avail_action" value="delete">
                            <input type="hidden" name="avail_id" value="<?= e($win['availability_ID']) ?>">
                            <button type="submit" class="btn delete" style="padding:4px 8px;font-size:11px">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="muted" style="margin-top:12px">No availability windows set. Add one above to allow students to request appointments.</p>
    <?php endif; ?>
</section>

<?php /* ASHFORD_ALL_ENHANCEMENTS_V2 */ ?>
<link rel="stylesheet" href="<?= e(url('assets/ashford-enhancements/enhancements.css')) ?>">
<script defer src="<?= e(url('assets/ashford-enhancements/enhancements.js')) ?>"></script>

<?php require __DIR__.'/../includes/advising_ui.php'; page_end(); ?>

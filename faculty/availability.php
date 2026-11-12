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
$availability = all_rows(
    $pdo,
    "SELECT fa.availability_ID, fa.day_ID, d.day_Of_Week, fa.start_Time, fa.end_Time, fa.is_active
        FROM Faculty_Availability fa
        JOIN Day d ON d.day_ID = fa.day_ID
        WHERE fa.faculty_ID = ?
        ORDER BY d.day_ID, fa.start_Time",
    [$facultyID]
);

// ---------- Add / Edit availability ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_verify();
    $action = post_str('action', 20);

    if ($action === 'add') {

        $dayID = post_int('day_id') ?? 0;
        $startTime = post_str('start_time', 10);
        $endTime = post_str('end_time', 10);

        if ($dayID < 1 || $dayID > 5) {
            $error = 'Availability may only be set Monday through Friday.';
        } elseif ($startTime === '' || $endTime === '') {
            $error = 'Please provide start and end time.';
        } elseif ($startTime >= $endTime) {
            $error = 'Start time must be before end time.';
        } else {
            try {
                $pdo->prepare(
                    'INSERT INTO Faculty_Availability (faculty_ID, day_ID, start_Time, end_Time)
                     VALUES (?, ?, ?, ?)'
                )->execute([$facultyID, $dayID, $startTime, $endTime]);
                $success = 'Availability added.';
                $availability = all_rows(
                    $pdo,
                    "SELECT fa.availability_ID, fa.day_ID, d.day_Of_Week, fa.start_Time, fa.end_Time, fa.is_active
                        FROM Faculty_Availability fa
                        JOIN Day d ON d.day_ID = fa.day_ID
                        WHERE fa.faculty_ID = ?
                        ORDER BY d.day_ID, fa.start_Time",
                    [$facultyID]
                );
            } catch (Throwable $e) {
                error_log('Add availability failed: ' . $e->getMessage());
                $error = 'Could not add availability. It may overlap a class or another window.';
            }
        }

    } elseif ($action === 'update') {

        $availabilityID = post_int('availability_id') ?? 0;
        $dayID = post_int('day_id') ?? 0;
        $startTime = post_str('start_time', 10);
        $endTime = post_str('end_time', 10);
        $isActive = post_int('is_active') ?? 0;

        $owns = one(
            $pdo,
            'SELECT 1 FROM Faculty_Availability WHERE availability_ID = ? AND faculty_ID = ?',
            [$availabilityID, $facultyID]
        );

        if (!$owns) {
            $error = 'Availability not found.';
        } elseif ($dayID < 1 || $dayID > 5) {
            $error = 'Availability may only be set Monday through Friday.';
        } elseif ($startTime === '' || $endTime === '') {
            $error = 'Please provide start and end time.';
        } elseif ($startTime >= $endTime) {
            $error = 'Start time must be before end time.';
        } else {
            try {
                $pdo->prepare(
                    'UPDATE Faculty_Availability
                     SET day_ID = ?, start_Time = ?, end_Time = ?, is_active = ?, updated_At = NOW()
                     WHERE availability_ID = ? AND faculty_ID = ?'
                )->execute([$dayID, $startTime, $endTime, $isActive, $availabilityID, $facultyID]);
                $success = 'Availability updated.';
                $availability = all_rows(
                    $pdo,
                    "SELECT fa.availability_ID, fa.day_ID, d.day_Of_Week, fa.start_Time, fa.end_Time, fa.is_active
                        FROM Faculty_Availability fa
                        JOIN Day d ON d.day_ID = fa.day_ID
                        WHERE fa.faculty_ID = ?
                        ORDER BY d.day_ID, fa.start_Time",
                    [$facultyID]
                );
            } catch (Throwable $e) {
                error_log('Update availability failed: ' . $e->getMessage());
                $error = 'Could not update availability. It may overlap a class or another window.';
            }
        }

    } elseif ($action === 'delete') {

        $availabilityID = post_int('availability_id') ?? 0;

        $owns = one(
            $pdo,
            'SELECT 1 FROM Faculty_Availability WHERE availability_ID = ? AND faculty_ID = ?',
            [$availabilityID, $facultyID]
        );

        if (!$owns) {
            $error = 'Availability not found.';
        } else {
            try {
                $pdo->prepare(
                    'DELETE FROM Faculty_Availability WHERE availability_ID = ? AND faculty_ID = ?'
                )->execute([$availabilityID, $facultyID]);
                $success = 'Availability removed.';
                $availability = all_rows(
                    $pdo,
                    "SELECT fa.availability_ID, fa.day_ID, d.day_Of_Week, fa.start_Time, fa.end_Time, fa.is_active
                        FROM Faculty_Availability fa
                        JOIN Day d ON d.day_ID = fa.day_ID
                        WHERE fa.faculty_ID = ?
                        ORDER BY d.day_ID, fa.start_Time",
                    [$facultyID]
                );
            } catch (Throwable $e) {
                error_log('Delete availability failed: ' . $e->getMessage());
                $error = 'Could not remove availability.';
            }
        }
    }
}

// Days Monday-Friday for the form
$days = all_rows(
    $pdo,
    'SELECT day_ID, day_Of_Week FROM Day WHERE day_ID BETWEEN 1 AND 5 ORDER BY day_ID'
);

page_start('My Availability', 'Faculty', 'availability.php');

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
            <p class="dashboard-eyebrow">Office Hours</p>
            <h2>My Availability</h2>
        </div>
    </div>

    <p class="muted" style="margin-top: 10px;">
        Set recurring weekly windows when students may book advisement
        appointments. Windows must fall within school hours (08:00-17:00,
        Monday-Friday) and may not overlap your own class sections.
    </p>

    <form method="post" class="form-grid" style="margin-top: 18px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">

        <div>
            <label for="day_id">Day</label>
            <select name="day_id" id="day_id" required>
                <option value="">Select day</option>
                <?php foreach ($days as $day): ?>
                    <option value="<?= e($day['day_ID']) ?>">
                        <?= e($day['day_Of_Week']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="start_time">Start time</label>
            <input type="time" name="start_time" id="start_time" required>
        </div>

        <div>
            <label for="end_time">End time</label>
            <input type="time" name="end_time" id="end_time" required>
        </div>

        <div class="form-actions" style="grid-column: 1 / -1;">
            <button type="submit" class="btn">Add Availability</button>
        </div>
    </form>
</div>

<?php if (count($availability) > 0): ?>

    <div class="table-wrap" style="margin-top: 24px;">
        <table>
            <tr>
                <th>Day</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($availability as $a): ?>

                <tr>
                    <td><?= e($a['day_Of_Week']) ?></td>
                    <td><?= e($a['start_Time']) ?></td>
                    <td><?= e($a['end_Time']) ?></td>
                    <td>
                        <?= (int) $a['is_active'] === 1 ? '<span class="badge">Active</span>' : '<span class="badge" style="background:#e8e8e8; color:#666;">Inactive</span>' ?>
                    </td>
                    <td>
                        <form method="post" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="availability_id" value="<?= e($a['availability_ID']) ?>">
                            <input type="hidden" name="day_id" value="<?= e($a['day_ID']) ?>">
                            <input type="hidden" name="start_time" value="<?= e($a['start_Time']) ?>">
                            <input type="hidden" name="end_time" value="<?= e($a['end_Time']) ?>">
                            <input type="hidden" name="is_active" value="<?= (int) $a['is_active'] === 1 ? 0 : 1 ?>">
                            <button type="submit" class="btn small">
                                <?= (int) $a['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                        <form method="post" style="display:inline; margin-left:6px;" onsubmit="return confirm('Remove this availability window?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="availability_id" value="<?= e($a['availability_ID']) ?>">
                            <button type="submit" class="btn danger small">Remove</button>
                        </form>
                    </td>
                </tr>

            <?php endforeach; ?>

        </table>
    </div>

<?php else: ?>

    <div class="card">
        <p class="muted">You have no availability windows set. Add one above so students can book appointments with you.</p>
    </div>

<?php endif;

page_end();

<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

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
        f.faculty_Rank,
        f.Specialty
     FROM Advisor a
     JOIN User u ON u.user_ID = a.faculty_ID
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
        [(int) $advisor['faculty_ID']]
    );
}

$appointments = all_rows(
    $pdo,
    "SELECT appointment_ID, requested_DateTime, status,
            student_Reason, advisor_Response
     FROM Advising_Appointment
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

        <p><strong>Specialty:</strong>
            <?= e($advisor['Specialty'] ?: 'General Academic Advising') ?>
        </p>

        <p><strong>Department(s):</strong>
            <?= e(implode(', ', array_column($departments, 'dept_Name')) ?: 'Not specified') ?>
        </p>

        <p><strong>Advisor Assignment Date:</strong>
            <?= e($advisor['date_Of_Appnt']) ?>
        </p>
    <?php else: ?>
        <p>No advisor has been assigned.</p>
    <?php endif; ?>
</section>

<?php if ($advisor): ?>
<section class="card">
    <h2>Request Advising Appointment</h2>

    <?php if ($hasActiveRequest): ?>
        <p>You already have a pending or scheduled appointment.</p>
    <?php else: ?>
        <form method="post" action="<?= e(url('student/advisor_appointment.php')) ?>">
            <?= csrf_field() ?>

            <div>
                <label for="requested_datetime">Requested Date and Time</label>
                <input
                    type="datetime-local"
                    id="requested_datetime"
                    name="requested_datetime"
                    min="<?= e(date('Y-m-d\TH:i')) ?>"
                    required
                >
            </div>

            <div>
                <label for="reason">Reason for Appointment</label>
                <textarea
                    id="reason"
                    name="reason"
                    maxlength="500"
                    rows="4"
                    placeholder="Example: Course registration or degree planning"
                ></textarea>
            </div>

            <button type="submit" class="btn">
                Request Appointment
            </button>
        </form>
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
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= e($appointment['appointment_ID']) ?></td>
                            <td><?= e($appointment['requested_DateTime']) ?></td>
                            <td><?= e($appointment['status']) ?></td>
                            <td><?= e($appointment['student_Reason'] ?? '—') ?></td>
                            <td><?= e($appointment['advisor_Response'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php /* ASHFORD_ALL_ENHANCEMENTS_V2 */ ?>
<link rel="stylesheet" href="<?= e(url('assets/ashford-enhancements/enhancements.css')) ?>">
<script defer src="<?= e(url('assets/ashford-enhancements/enhancements.js')) ?>"></script>

<?php require __DIR__.'/../includes/advising_ui.php'; page_end(); ?>

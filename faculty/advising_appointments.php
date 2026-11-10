<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Faculty']);

$facultyID = (int) $_SESSION['user_id'];

require_once '../includes/advising_workflow.php';
aw_action($pdo,'Faculty');

$appointments = all_rows(
    $pdo,
    "SELECT
        ap.appointment_ID,
        ap.student_ID,
        ap.requested_DateTime,
        ap.status,
        ap.student_Reason,
        ap.advisor_Response,
        u.first_Name,
        u.last_Name,
        m.major_Name
     FROM Advising_Appointment ap
     JOIN User u ON u.user_ID = ap.student_ID
     JOIN Student s ON s.student_ID = ap.student_ID
     LEFT JOIN Major m ON m.major_ID = s.major_ID
     WHERE ap.faculty_ID = ?
     ORDER BY
         CASE WHEN ap.status = 'Pending' THEN 0 ELSE 1 END,
         ap.requested_DateTime DESC",
    [$facultyID]
);

$awProposals=aw_proposals($pdo,$facultyID,'Faculty');
$awActivity=aw_activity($pdo,$facultyID);
page_start(
    'Advising Appointments',
    'Faculty',
    'advisees.php'
);
?>

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

                                        <input
                                            type="hidden"
                                            name="appointment_id"
                                            value="<?= e($appointment['appointment_ID']) ?>"
                                        >

                                        <label>Advisor Response</label>
                                        <textarea
                                            name="reason"
                                            maxlength="500"
                                            rows="2"
                                            placeholder="Optional response"
                                        ></textarea>

                                        <button
                                            type="submit"
                                            name="aw_action"
                                            value="accept"
                                            class="btn"
                                        >
                                            Accept
                                        </button>

                                        <button
                                            type="submit"
                                            name="aw_action"
                                            value="deny"
                                            class="btn danger small"
                                        >
                                            Deny
                                        </button>
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

<?php /* ASHFORD_ALL_ENHANCEMENTS_V2 */ ?>
<link rel="stylesheet" href="<?= e(url('assets/ashford-enhancements/enhancements.css')) ?>">
<script defer src="<?= e(url('assets/ashford-enhancements/enhancements.js')) ?>"></script>

<?php require __DIR__.'/../includes/advising_ui.php'; page_end(); ?>

<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];
$pdo = get_pdo();

$holds = all_rows($pdo,
    'SELECT h.hold_ID, h.hold_Type, sh.hold_Date
       FROM Student_Hold sh
       JOIN Hold h ON h.hold_ID = sh.hold_ID
      WHERE sh.student_ID = ?',
    [$studentID]);

page_start('Holds', 'Student', 'student/holds.php');
?>
<h2>Account Holds</h2>
<?php if (count($holds) > 0): ?>
    <table>
        <thead><tr><th>Hold Type</th><th>Date Placed</th></tr></thead>
        <tbody>
            <?php foreach ($holds as $h): ?>
                <tr><td><?= e($h['hold_Type']) ?></td><td><?= e($h['hold_Date']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p>Holds prevent course registration until resolved by the appropriate office.</p>
<?php else: ?>
    <p>No holds on your account.</p>
<?php endif; ?>
<p><a href="/student/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>

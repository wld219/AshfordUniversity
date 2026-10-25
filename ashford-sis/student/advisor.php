<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];
$pdo = get_pdo();

$advisor = one($pdo,
    'SELECT f.faculty_ID, u.first_Name, u.last_Name, u.email, f.faculty_Rank, f.Specialty
       FROM Advisor a
       JOIN Faculty f ON f.faculty_ID = a.faculty_ID
       JOIN User u ON u.user_ID = f.faculty_ID
      WHERE a.student_ID = ?',
    [$studentID]);

page_start('My Advisor', 'Student', 'student/advisor.php');
?>
<h2>Assigned Advisor</h2>
<?php if ($advisor): ?>
    <table>
        <tr><th>Name</th><td><?= e($advisor['first_Name'] . ' ' . $advisor['last_Name']) ?></td></tr>
        <tr><th>Email</th><td><?= e($advisor['email']) ?></td></tr>
        <tr><th>Rank</th><td><?= e($advisor['faculty_Rank']) ?></td></tr>
        <tr><th>Specialty</th><td><?= e($advisor['Specialty']) ?></td></tr>
    </table>
<?php else: ?>
    <p>No advisor assigned.</p>
<?php endif; ?>
<p><a href="/student/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>

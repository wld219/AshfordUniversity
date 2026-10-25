<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/helpers.php';

require_role(['Admin']);

$adminID = (int) $_SESSION['user_id'];
$pdo = get_pdo();
$securityLevel = one($pdo, 'SELECT security_Level FROM Admin WHERE user_ID = ?', [$adminID])['security_Level'] ?? 'Read-Only';
$isFullAccess = $securityLevel === 'Full-Access';

// Master schedule stats
$totalSections = (int) $pdo->query('SELECT COUNT(*) FROM Course_Section')->fetchColumn();
$cancelled = (int) $pdo->query("SELECT COUNT(*) FROM Course_Section WHERE is_Cancelled = 1")->fetchColumn();
$missingFaculty = (int) $pdo->query("SELECT COUNT(*) FROM Course_Section WHERE faculty_ID NOT IN (SELECT faculty_ID FROM Faculty)")->fetchColumn();
$missingRooms = (int) $pdo->query("SELECT COUNT(*) FROM Course_Section WHERE lecture_ID IS NULL AND lab_ID IS NULL")->fetchColumn();

page_start('Master Schedule', 'Admin', 'admin/master-schedule.php');
?>
<h2>Master Schedule Statistics</h2>
<section class="dashboard-stats">
    <div class="card stat"><span class="label">Total Sections</span><strong><?= e($totalSections) ?></strong></div>
    <div class="card stat"><span class="label">Cancelled</span><strong><?= e($cancelled) ?></strong></div>
    <div class="card stat"><span class="label">Missing Faculty</span><strong><?= e($missingFaculty) ?></strong></div>
    <div class="card stat"><span class="label">Missing Rooms</span><strong><?= e($missingRooms) ?></strong></div>
</section>
<p class="muted">Publish is blocked while any issue remains.</p>

<?php if ($isFullAccess): ?>
    <h2>Sections</h2>
    <table>
        <thead><tr><th>CRN</th><th>Course</th><th>Faculty</th><th>Semester</th><th>Status</th></tr></thead>
        <tbody>
            <?php
            $rows = all_rows($pdo,
                'SELECT cs.CRN, c.course_ID, c.course_Name, u.first_Name, u.last_Name, s.semester_Name, cs.is_Cancelled
                   FROM Course_Section cs
                   JOIN Course c ON c.course_ID = cs.course_ID
                   JOIN Faculty f ON f.faculty_ID = cs.faculty_ID
                   JOIN User u ON u.user_ID = f.faculty_ID
                   JOIN Semester s ON s.semester_ID = cs.semester_ID
                  ORDER BY cs.CRN
                  LIMIT 100');
            foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['CRN']) ?></td>
                    <td><?= e($r['course_ID']) ?></td>
                    <td><?= e($r['first_Name'] . ' ' . $r['last_Name']) ?></td>
                    <td><?= e($r['semester_Name']) ?></td>
                    <td><?= e($r['is_Cancelled'] ? 'Cancelled' : 'Active') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="alert alert-error">Read-only access.</p>
<?php endif; ?>
<p><a href="/admin/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>

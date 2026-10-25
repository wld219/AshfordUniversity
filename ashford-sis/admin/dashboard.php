<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/helpers.php';

require_role(['Admin']);

$adminID = (int) $_SESSION['user_id'];
$pdo = get_pdo();

// Admin security level
$securityLevel = one($pdo,
    'SELECT security_Level FROM Admin WHERE user_ID = ?', [$adminID])['security_Level'] ?? 'Read-Only';
$isFullAccess = $securityLevel === 'Full-Access';

// Quick stats
$stats = [
    'students'   => (int) $pdo->query('SELECT COUNT(*) FROM Student')->fetchColumn(),
    'faculty'    => (int) $pdo->query('SELECT COUNT(*) FROM Faculty')->fetchColumn(),
    'courses'    => (int) $pdo->query('SELECT COUNT(*) FROM Course')->fetchColumn(),
    'sections'   => (int) $pdo->query('SELECT COUNT(*) FROM Course_Section')->fetchColumn(),
    'enrollments'=> (int) $pdo->query('SELECT COUNT(*) FROM Enrollment')->fetchColumn(),
];

page_start('Admin Dashboard', 'Admin', 'admin/dashboard.php');
?>
<h2>University Overview</h2>
<section class="dashboard-stats">
    <?php foreach ($stats as $label => $val): ?>
        <div class="card stat"><span class="label"><?= e(str_replace('_', ' ', $label)) ?></span><strong><?= e($val) ?></strong></div>
    <?php endforeach; ?>
</section>
<h2>Quick Actions</h2>
<?php if ($isFullAccess): ?>
    <p><a href="/admin/academic.php" class="btn btn-primary">Academic Management</a></p>
    <p><a href="/admin/master-schedule.php" class="btn btn-primary">Master Schedule</a></p>
    <p><a href="/admin/users.php" class="btn btn-primary">User Management</a></p>
<?php else: ?>
    <p class="alert alert-error">You have read-only access.</p>
<?php endif; ?>
<p><a href="/logout" class="btn btn-outline">Logout</a></p>
<?php page_end(); ?>

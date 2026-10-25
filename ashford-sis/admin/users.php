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

$users = all_rows($pdo,
    'SELECT u.user_ID, u.first_Name, u.last_Name, u.email, u.user_Type, l.lock_var, l.no_Of_Tries
       FROM User u
       JOIN Login l ON l.user_ID = u.user_ID
      ORDER BY u.user_Type, u.last_Name, u.first_Name
      LIMIT 200');

page_start('User Management', 'Admin', 'admin/users.php');
?>
<h2>Users</h2>
<?php if ($isFullAccess): ?>
    <p class="muted">Read-only user listing in this build. Full user CRUD available in Phase 8.</p>
<?php endif; ?>
<?php if (count($users) > 0): ?>
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['user_ID']) ?></td>
                    <td><?= e($u['first_Name'] . ' ' . $u['last_Name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['user_Type']) ?></td>
                    <td><?= e($u['lock_var'] ? 'Locked' : 'Active') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?>
<p><a href="/admin/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>

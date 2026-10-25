<?php

declare(strict_types=1);

require_once '../includes/config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';
require_once '../includes/helpers/validation.php';

require_role(['Student']);

$studentID = (int) $_SESSION['user_id'];
$pdo = get_pdo();
$error = '';
$success = '';

$user = one($pdo, 'SELECT * FROM User WHERE user_ID = ?', [$studentID]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = post_str('action', 20);

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        [$ok, $msg] = change_password($pdo, $studentID, $current, $new);
        if ($ok) { $success = 'Password changed successfully.'; }
        else { $error = $msg; }
    }
}

page_start('Profile', 'Student', 'student/profile.php');
?>
<h2>Profile</h2>
<p><strong>Name:</strong> <?= e($user['first_Name'] . ' ' . $user['last_Name']) ?></p>
<p><strong>Email:</strong> <?= e($user['email']) ?></p>
<p><strong>Role:</strong> Student</p>

<h2>Change Password</h2>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<form method="POST" action="/student/profile.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="change_password">
    <label>Current Password</label><input type="password" name="current_password" required>
    <label>New Password</label><input type="password" name="new_password" required>
    <small class="muted">At least 8 characters with upper, lower, digit, and symbol. Cannot reuse your current or recent passwords.</small>
    <button type="submit" class="btn btn-primary">Change Password</button>
</form>
<p><a href="/student/dashboard" class="btn btn-outline">Back to Dashboard</a></p>
<?php page_end(); ?>

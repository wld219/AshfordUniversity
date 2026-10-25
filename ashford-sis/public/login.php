<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/../includes/auth.php';

init_session();

if (is_logged_in()) {
    $user = current_user();
    $role = $user['login_type'] ?? $user['user_Type'];
    $route = match ($role) {
        'Student'   => '/student',
        'Faculty'   => '/faculty',
        'Admin'     => '/admin',
        'StatStaff' => '/statstaff',
        default     => '/',
    };
    header("Location: $route");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {

        $user = one(
            get_pdo(),
            'SELECT u.user_ID, u.first_Name, u.last_Name, u.user_Type, l.user_Type AS login_type, l.lock_var
               FROM User u
               JOIN Login l ON l.user_ID = u.user_ID
              WHERE l.user_Email = ?',
            [$email]
        );

        if (!$user || $user['lock_var']) {
            $error = 'Invalid email or password.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            // Increment failed tries
            $pdo = get_pdo();
            $pdo->prepare('UPDATE Login SET no_Of_Tries = no_Of_Tries + 1 WHERE user_ID = ?')
                ->execute([$user['user_ID']]);

            $tries = (int) one(
                $pdo,
                'SELECT no_Of_Tries FROM Login WHERE user_ID = ?',
                [$user['user_ID']]
            )['no_Of_Tries'];

            if ($tries >= 5) {
                $pdo->prepare('UPDATE Login SET lock_var = TRUE WHERE user_ID = ?')
                    ->execute([$user['user_ID']]);
            }

            $error = 'Invalid email or password.';
        } else {
            $pdo = get_pdo();
            $pdo->prepare('UPDATE Login SET no_Of_Tries = 0 WHERE user_ID = ?')
                ->execute([$user['user_ID']]);

            sign_in($user['user_ID'], $user['login_type']);

            $route = match ($user['login_type']) {
                'Student'   => '/student',
                'Faculty'   => '/faculty',
                'Admin'     => '/admin',
                'StatStaff' => '/statstaff',
                default     => '/',
            };
            header("Location: $route");
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - Ashford SIS</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="topbar">
        <div class="brand">Ashford SIS</div>
    </header>
    <main class="container">
        <div class="login-card">
            <h2>Sign In</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="/login">
                <?= csrf_field() ?>
                <label>Email</label>
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
                <label>Password</label>
                <input type="password" name="password" required>
                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>
        </div>
    </main>
</body>
</html>

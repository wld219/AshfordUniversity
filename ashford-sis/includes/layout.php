<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php';

// Common page wrapper. Call page_start() at the top and page_end() at the bottom.

function page_start(string $title, string $role, string $page): void
{
    init_session();
    require_role([$role]);

    $user = current_user();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> - Ashford SIS</title>
        <link rel="stylesheet" href="/assets/css/app.css">
    </head>
    <body>
        <header class="topbar">
            <div class="brand">Ashford SIS</div>
            <div class="user-info">
                <?= e($user['first_Name'] . ' ' . $user['last_Name']) ?>
                <span class="role-badge"><?= e($role) ?></span>
                <a href="/logout" class="btn btn-outline">Logout</a>
            </div>
        </header>

        <nav class="main-nav">
            <a href="/student" <?= $role === 'Student' ? 'class="active"' : '' ?>>Student</a>
            <a href="/faculty" <?= $role === 'Faculty' ? 'class="active"' : '' ?>>Faculty</a>
            <a href="/admin" <?= $role === 'Admin' ? 'class="active"' : '' ?>>Admin</a>
        </nav>

        <main class="container">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>
            <h1><?= e($title) ?></h1>
    <?php
}

function page_end(): void
{
    ?>
        </main>
        <footer class="footer">
            &copy; Ashford University SIS - Local XAMPP Deployment
        </footer>
    </body>
    </html>
    <?php
}

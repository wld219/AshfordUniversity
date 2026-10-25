<?php

declare(strict_types=1);

// Front controller for PHP built-in server.
// Routes / to the public landing page; everything else maps to a file.

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Normalise: strip leading slash
$path = ltrim($uri, '/');

// Serve static assets directly
if ($path !== '' && file_exists(__DIR__ . '/' . $path) && !is_dir(__DIR__ . '/' . $path)) {
    return false; // let the built-in server serve the file
}

// Default to the public landing page
if ($path === '' || $path === 'index.php') {
    require __DIR__ . '/public/index.php';
    return;
}

// Map clean URLs to portal entry points
$routes = [
    'login' => 'public/login.php',
    'logout' => 'public/logout.php',
    'student' => 'student/dashboard.php',
    'faculty' => 'faculty/dashboard.php',
    'admin' => 'admin/dashboard.php',
];

$file = $routes[$path] ?? null;

if ($file === null || !file_exists(__DIR__ . '/' . $file)) {
    http_response_code(404);
    require __DIR__ . '/public/404.php';
    return;
}

require __DIR__ . '/' . $file;

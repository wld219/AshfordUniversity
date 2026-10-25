<?php

declare(strict_types=1);

/*
 * Local development router for PHP's built-in web server.
 *
 * Start the server with this router as the last argument:
 *
 *   C:\xampp\php\php.exe -S localhost:8000 -t <project path> router.php
 *
 * Without it, a request for "/" would land on the sign-in page. With it,
 * "/" opens the public landing page. Every other path is served normally.
 *
 * This file is only needed for the built-in server. The Apache deployment
 * in docs/AWS_DEPLOYMENT.md serves public/ as the document root instead.
 */

$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);

// "/" opens the public landing page. "/index.php" still serves the
// sign-in page, which is what every public page links to, so a visitor
// can always sign in.
if ($path === '/' || $path === '') {

	require __DIR__ . '/public/index.php';

	return true;
}

// Anything else: let the built-in server serve it from the document root.
return false;

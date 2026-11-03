<?php

declare(strict_types=1);

// Flash messages and redirects.

// Store a one-time flash message and redirect.
function redirect_with_flash(string $url, string $type, string $message): void
{
	$_SESSION['flash'] = ['type' => $type, 'message' => $message];
	header('Location: ' . $url);
	exit;
}

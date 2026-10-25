<?php

declare(strict_types=1);

// Users view orchestrator.

require_once __DIR__ . '/users_header.php';

if ($isFullAccess) {
	require_once __DIR__ . '/users_form_create.php';
}

if ($editUser && $isFullAccess) {
	require_once __DIR__ . '/users_form_edit.php';
}

require_once __DIR__ . '/users_list.php';

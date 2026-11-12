<?php

declare(strict_types=1);

// Users view orchestrator.

require_once __DIR__ . '/users_header.php';

if ($isFullAccess && !$isReadOnly) {
	require_once __DIR__ . '/users_form_create.php';
}

if ($editUser && $isFullAccess && !$isReadOnly) {
	require_once __DIR__ . '/users_form_edit.php';
}

require_once __DIR__ . '/users_list.php';

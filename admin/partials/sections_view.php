<?php

declare(strict_types=1);

// Sections view orchestrator.

require_once __DIR__ . '/sections_header.php';

if ($isFullAccess) {
	require_once __DIR__ . '/sections_form.php';
}

require_once __DIR__ . '/sections_table.php';

<?php

declare(strict_types=1);

// Master schedule view orchestrator. Variables come from the entry point
// and the data file; partials render each section of the dashboard.

require_once __DIR__ . '/master_schedule_header.php';
require_once __DIR__ . '/master_schedule_stats.php';
require_once __DIR__ . '/master_schedule_filters.php';

if ($editSection !== null && $isFullAccess) {
	require_once __DIR__ . '/master_schedule_edit.php';
}

require_once __DIR__ . '/master_schedule_table.php';
require_once __DIR__ . '/master_schedule_events.php';

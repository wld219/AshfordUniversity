<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['Admin']);

page_start('Approvals Center', 'Admin', 'approvals.php');

require __DIR__ . '/partials/approvals_center_view.php';

page_end();

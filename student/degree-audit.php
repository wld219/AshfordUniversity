<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];

// Page data (declared programs and their audits)
require_once __DIR__ . '/degree_audit_data.php';

page_start(
	'Degree Audit',
	'Student',
	'degree-audit.php'
);

require_once __DIR__ . '/partials/degree_audit_view.php';

page_end();

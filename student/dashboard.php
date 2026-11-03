<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];

// Page data
require_once __DIR__ . '/dashboard_data.php';

page_start(
	'Welcome, ' . ($_SESSION['name'] ?? 'Student'),
	'Student',
	'dashboard.php'
);

flash();

require_once __DIR__ . '/partials/dashboard_view.php';

page_end();

<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];

// Student-only audit data; the Admin audit keeps its separate data path.
require_once __DIR__ . '/degree_audit_student_data.php';

page_start(
	'Degree Audit',
	'Student',
	'degree-audit.php'
);

require_once __DIR__ . '/partials/degree_audit_view.php';

page_end();

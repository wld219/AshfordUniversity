<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];

/*
 * Major and minor selections.
 *
 * Business rules (specification):
 *   - A student may have one or two majors.
 *   - A student may have zero or one minor.
 * Student.major_ID keeps the primary major; Student_Major records every
 * declared major with its choice date.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	csrf_verify();

	require_once __DIR__ . '/programs_actions.php';
}

// Page data
require_once __DIR__ . '/programs_data.php';

page_start(
	'Majors & Minors',
	'Student',
	'programs.php'
);

flash();

require_once __DIR__ . '/partials/programs_view.php';

page_end();

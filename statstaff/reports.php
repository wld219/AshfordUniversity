<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['StatStaff']);

/*
 * Anonymous statistical reports.
 *
 * Read-only. Every query returns aggregates only: no student names, no
 * student IDs, and no individual grade records are displayed or exported.
 */

// ---------------------------------------------------------------------------
// Filters
// ---------------------------------------------------------------------------

$semesters = all_rows(
	$pdo,
	'SELECT semester_ID, semester_Name FROM Semester ORDER BY semester_ID DESC'
);

$departments = all_rows(
	$pdo,
	'SELECT dept_ID, dept_Name FROM Department ORDER BY dept_Name'
);

$report = get_str('report', 30);
$semesterID = get_int('semester') ?? semester_id($pdo, 'Fall 2026');
$deptID = get_int('dept');

$validReports = [
	'course_enrollment',
	'grade_summary',
	'level_totals',
	'section_utilization',
];

if (!in_array($report, $validReports, true)) {
	$report = 'course_enrollment';
}

// ---------------------------------------------------------------------------
// Run the selected report
// ---------------------------------------------------------------------------

$rows = [];
$headers = [];
$title = '';
$description = '';

switch ($report) {

	case 'course_enrollment':
		require_once __DIR__ . '/reports_course_enrollment.php';
		break;

	case 'grade_summary':
		require_once __DIR__ . '/reports_grade_summary.php';
		break;

	case 'level_totals':
		require_once __DIR__ . '/reports_level_totals.php';
		break;

	case 'section_utilization':
		require_once __DIR__ . '/reports_section_utilization.php';
		break;
}

// CSV export of the active report (aggregates only)
if (get_str('export', 10) === 'csv') {

	csv_export(
		$headers,
		$rows,
		'ashford_' . $report . '_' . date('Ymd_His')
	);
}

page_start(
	'Anonymous Reports',
	'StatStaff',
	'reports.php'
);

flash();

require_once __DIR__ . '/partials/reports_view.php';

page_end();

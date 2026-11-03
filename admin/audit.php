<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin']);

/*
 * Audit log viewer. Records are append-only: this page and every other
 * portal action only INSERT into Audit_Log, never UPDATE or DELETE.
 */

$action = get_str('action', 30);
$entityType = get_str('entity', 50);
$page = max(1, get_int('page') ?? 1);
$perPage = 50;

// Listing query
$sql = "SELECT
			a.log_ID,
			a.action,
			a.entity_Type,
			a.entity_ID,
			a.details,
			a.ip_Address,
			a.created_At,
			CONCAT(u.first_Name, ' ', u.last_Name) AS actor_Name,
			a.actor_User_ID
		FROM Audit_Log a
		LEFT JOIN User u ON u.user_ID = a.actor_User_ID
		WHERE 1 = 1";

$params = [];
$countParams = [];

if ($action !== '') {
	$sql .= ' AND a.action = ?';
	$params[] = $action;
	$countParams[] = $action;
}

if ($entityType !== '') {
	$sql .= ' AND a.entity_Type = ?';
	$params[] = $entityType;
	$countParams[] = $entityType;
}

// Totals query, kept separate from the listing query
$totalSql = 'SELECT COUNT(*) AS total FROM Audit_Log a WHERE 1 = 1';

if ($action !== '') {
	$totalSql .= ' AND a.action = ?';
}

if ($entityType !== '') {
	$totalSql .= ' AND a.entity_Type = ?';
}

$total = (int) (one($pdo, $totalSql, $countParams)['total'] ?? 0);

$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql .= ' ORDER BY a.created_At DESC, a.log_ID DESC LIMIT ' . $perPage . ' OFFSET ' . $offset;

$entries = all_rows($pdo, $sql, $params);

$actionFilters = all_rows(
	$pdo,
	'SELECT DISTINCT action FROM Audit_Log ORDER BY action'
);

$entityFilters = all_rows(
	$pdo,
	'SELECT DISTINCT entity_Type FROM Audit_Log ORDER BY entity_Type'
);

page_start(
	'Audit Log',
	'Admin',
	'audit.php'
);

require_once __DIR__ . '/partials/audit_view.php';

page_end();

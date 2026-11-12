<?php

declare(strict_types=1);

// Data for the users page: search results, edit target, form options.

// Search
$search = get_str('search', 100);
$roleFilter = get_str('role', 20);

if (!in_array($roleFilter, ['Student', 'Faculty', 'Admin', 'StatStaff'], true)) {
	$roleFilter = '';
}

if ($search !== '' || $roleFilter !== '') {

	$searchValue = '%' . $search . '%';
	$params = [];

	$whereParts = [];
	if ($search !== '') {
		$whereParts[] = '(CAST(u.user_ID AS CHAR) LIKE ? OR u.first_Name LIKE ? OR u.last_Name LIKE ?)';
		$params[] = $searchValue;
		$params[] = $searchValue;
		$params[] = $searchValue;
	}
	if ($roleFilter !== '') {
		$whereParts[] = 'u.user_Type = ?';
		$params[] = $roleFilter;
	}

	$whereSql = implode(' AND ', $whereParts);

	$sql = "SELECT
				u.user_ID,
				u.first_Name,
				u.last_Name,
				u.user_Type,
				u.city,
				u.state,
				l.lock_var,
				l.no_Of_Tries
			FROM User u
			JOIN Login l ON l.user_ID = u.user_ID
			WHERE $whereSql
			ORDER BY u.user_ID
			LIMIT 25";

	$users = all_rows(
		$pdo,
		$sql,
		$params
	);

} else {

		$sql = "SELECT
					u.user_ID,
					u.first_Name,
					u.last_Name,
					u.user_Type,
					u.city,
					u.state,
					l.lock_var,
					l.no_Of_Tries
				FROM User u
				JOIN Login l ON l.user_ID = u.user_ID
				ORDER BY u.user_ID
				LIMIT 25";

		$users = all_rows(
			$pdo,
			$sql
		);
	}

// Get one user for editing
$editUser = null;

if (isset($_GET['edit'])) {

	$editID = get_int('edit') ?? 0;

	$editUser = one(
		$pdo,
		"SELECT u.user_ID, u.first_Name, u.last_Name, u.user_Type, u.city, u.state,
				l.lock_var, l.no_Of_Tries,
				a.security_Level, a.lock_manager
			FROM User u
			JOIN Login l ON l.user_ID = u.user_ID
			LEFT JOIN Admin a ON a.user_ID = u.user_ID
			WHERE u.user_ID = ?",
		[$editID]
	);
}

// Office list for new faculty records
$offices = all_rows(
	$pdo,
	"SELECT o.office_ID, r.room_Number, b.Bldg_Name
	FROM Office o
	JOIN Room r
		ON r.room_ID = o.office_ID
	JOIN Building b
		ON b.Bldg_ID = r.Bldg_ID
	ORDER BY b.Bldg_Name, r.room_Number"
);

// Majors for new student records
$majors = all_rows(
	$pdo,
	"SELECT major_ID, major_Name
	FROM Major
	ORDER BY major_Name"
);

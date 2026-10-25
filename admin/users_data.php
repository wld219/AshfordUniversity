<?php

declare(strict_types=1);

// Data for the users page: search results, edit target, form options.

// Search
$search = get_str('search', 100);

if ($search !== '') {

	$searchValue = '%' . $search . '%';

	$sql = "SELECT
				user_ID,
				first_Name,
				last_Name,
				user_Type,
				city,
				state
			FROM User
			WHERE
				CAST(user_ID AS CHAR) LIKE ?
				OR first_Name LIKE ?
				OR last_Name LIKE ?
			ORDER BY user_ID
			LIMIT 25";

	$users = all_rows(
		$pdo,
		$sql,
		[
			$searchValue,
			$searchValue,
			$searchValue
		]
	);

} else {

	$sql = "SELECT
				user_ID,
				first_Name,
				last_Name,
				user_Type,
				city,
				state
			FROM User
			ORDER BY user_ID
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
		"SELECT user_ID, first_Name, last_Name, user_Type, city, state
		FROM User
		WHERE user_ID = ?",
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

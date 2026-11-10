<?php

declare(strict_types=1);

// Data for the users page: search results, edit target, form options.

// Search
$search = get_str('search', 100);

	if ($search !== '') {

		$searchValue = '%' . $search . '%';

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
				WHERE
					CAST(u.user_ID AS CHAR) LIKE ?
					OR u.first_Name LIKE ?
					OR u.last_Name LIKE ?
				ORDER BY u.user_ID
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
		"SELECT u.*, l.user_Email, l.lock_var, l.no_Of_Tries,
                a.security_Level, a.lock_manager,
                st.major_ID, st.student_Year, st.student_Type
			FROM User u
			JOIN Login l ON l.user_ID = u.user_ID
			LEFT JOIN Admin a ON a.user_ID = u.user_ID
            LEFT JOIN Student st ON st.student_ID = u.user_ID
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

// Academic selections for student profile editing.
$minors = all_rows($pdo, 'SELECT minor_ID, minor_Name FROM Minor ORDER BY minor_Name');
$studentMajors = [];
$studentMinors = [];
if ($editUser && $editUser['user_Type'] === 'Student') {
    $studentMajors = array_map('intval', array_column(all_rows($pdo, 'SELECT major_ID FROM Student_Major WHERE student_ID = ?', [(int)$editUser['user_ID']]), 'major_ID'));
    $studentMinors = array_map('intval', array_column(all_rows($pdo, 'SELECT minor_ID FROM Student_Minor WHERE student_ID = ?', [(int)$editUser['user_ID']]), 'minor_ID'));
}

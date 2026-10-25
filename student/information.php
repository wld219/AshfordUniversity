<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

	require_role(['Student']);

	// Get the logged-in student's ID
	$studentID = (int) $_SESSION['user_id'];
	$pdo = get_pdo();

	/*
	 * Students may update their own contact details. Protected fields
	 * (user_ID, email, role, grades, holds, credits) are never editable here.
	 * Every field written below is validated against System_Field_Restriction
	 * so that unsafe or identifiable fields cannot be changed.
	 */
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		csrf_verify();

		$street = post_str('street', 100);
		$city = post_str('city', 50);
		$state = post_str('state', 50);
		$zipCode = post_str('zip_code', 10);

		if (
			$street === '' ||
			$city === '' ||
			$state === '' ||
			$zipCode === ''
		) {
			redirect_with_flash(
				url('student/information.php'),
				'error',
				'Please complete every field.'
			);
		}

		// Validate each field against the database-level restriction metadata.
		$fields = [
			'street' => $street,
			'city' => $city,
			'state' => $state,
			'zip_Code' => $zipCode,
		];

		foreach ($fields as $column => $value) {
			if (field_is_restricted($pdo, 'User', $column, 'Student')) {
				redirect_with_flash(
					url('student/information.php'),
					'error',
					"Cannot update {$column} — field is restricted."
				);
			}
		}

		$oldUser = one(
			$pdo,
			'SELECT street, city, state, zip_Code FROM User WHERE user_ID = ?',
			[$studentID]
		);

	try {

		$pdo->beginTransaction();

		$pdo->prepare(
			'UPDATE User
			SET street = ?, city = ?, state = ?, zip_Code = ?
			WHERE user_ID = ?'
		)->execute([
			$street,
			$city,
			$state,
			$zipCode,
			$studentID
		]);

		log_user_changes(
			$pdo,
			$studentID,
			$oldUser ?: [],
			[
				'street' => $street,
				'city' => $city,
				'state' => $state,
				'zip_Code' => $zipCode
			],
			'Student self-service update'
		);

		audit_log($pdo, 'Update', 'User', (string) $studentID, 'Student information update');

		$pdo->commit();

		redirect_with_flash(
			url('student/information.php'),
			'success',
			'Your information has been updated.'
		);

	} catch (PDOException $e) {

		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}

		error_log('Student information update failed: ' . $e->getMessage());

		redirect_with_flash(
			url('student/information.php'),
			'error',
			'Your information could not be updated.'
		);
	}
}


// Get the student's personal and academic information
$student = one(
	$pdo,
	"		SELECT
			User.user_ID,
			User.first_Name,
			User.middle_Name,
			User.last_Name,
			User.gender,
			User.DOB,
			Login.user_Email,
			User.street,
			User.city,
			User.state,
			User.zip_Code,
			Student.student_Year,
			Student.student_Type,
			Major.major_Name
		FROM User
		JOIN Student
			ON Student.student_ID = User.user_ID
		JOIN Login
			ON Login.user_ID = User.user_ID
		LEFT JOIN Major
			ON Major.major_ID = Student.major_ID
		WHERE User.user_ID = ?",
	[$studentID]
);


page_start(
	'My Information',
	'Student',
	'information.php'
);

flash();

require_once __DIR__ . '/partials/information_view.php';

page_end();

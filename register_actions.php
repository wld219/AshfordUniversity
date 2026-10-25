<?php

declare(strict_types=1);

// Registration action: validate the form and create a self-service student
// account inside one transaction. Uses $pdo, $error, $success, $majors
// from the entry point.

$firstName = post_str('first_name', 50);
$middleName = post_str('middle_name', 50);
$lastName = post_str('last_name', 50);
$gender = post_str('gender', 10);
$dob = post_str('dob', 10);
$street = post_str('street', 100);
$city = post_str('city', 50);
$state = post_str('state', 50);
$zipCode = post_str('zip_code', 10);
$majorID = post_int('major_id') ?? 0;
$email = post_str('email', 255);
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');

if (
	$firstName === '' ||
	$lastName === '' ||
	!in_array($gender, ['Male', 'Female', 'Other'], true) ||
	!is_past_date($dob) ||
	$street === '' ||
	$city === '' ||
	$state === '' ||
	$zipCode === '' ||
	$majorID === 0 ||
	!valid_email($email) ||
	$password === ''
) {
	$error = 'Please complete every field with a valid value.';

} elseif ($password !== $confirmPassword) {

	$error = 'Passwords do not match.';

} else {

	[$policyOk, $policyMessage] = password_policy_ok($password);

	if (!$policyOk) {

		$error = $policyMessage;

	} else {

		// Make sure the email is not already being used
		$stmt = $pdo->prepare(
			"SELECT user_ID
			FROM Login
			WHERE user_Email = ?"
		);

		$stmt->execute([$email]);

		if ($stmt->fetch()) {

			$error = 'An account with this email already exists.';

		} else {

			try {

				$pdo->beginTransaction();


				// Re-read the maximum ID inside the transaction so two
				// concurrent registrations cannot claim the same ID.
				$row = one(
					$pdo,
					"SELECT MAX(user_ID) AS max_id
					FROM User"
				);

				$userID = ((int) ($row['max_id'] ?? 0)) + 1;


				// Add user
				$sql = "INSERT INTO User
						(user_ID, first_Name, middle_Name, last_Name,
						gender, DOB, street, city, state,
						zip_Code, user_Type)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

				$pdo->prepare($sql)->execute([
					$userID,
					$firstName,
					$middleName !== '' ? $middleName : null,
					$lastName,
					$gender,
					$dob,
					$street,
					$city,
					$state,
					$zipCode,
					'Student'
				]);


				// Create password
				$hashedPassword = password_hash(
					$password,
					PASSWORD_DEFAULT
				);


				// Add login
				$pdo->prepare(
					"INSERT INTO Login
						(user_ID, user_Email, user_Password,
						no_Of_Tries, lock_var, user_Type)
					VALUES (?, ?, ?, 0, 0, 'Student')"
				)->execute([
					$userID,
					$email,
					$hashedPassword
				]);


				// Look up the department that owns the chosen major
				$major = one(
					$pdo,
					"SELECT dept_ID
					FROM Major
					WHERE major_ID = ?",
					[$majorID]
				);


				// Add student information: new self-registered students
				// start as full-time undergraduates (documented default).
				$pdo->prepare(
					"INSERT INTO Student
						(student_ID, major_ID, student_Year, student_Type)
					VALUES (?, ?, ?, ?)"
				)->execute([
					$userID,
					$majorID,
					1,
					'Undergraduate'
				]);

				$pdo->prepare(
					"INSERT INTO Undergraduate
						(student_ID, dept_ID, undergraduate_student_Type)
					VALUES (?, ?, 'Full-Time')"
				)->execute([
					$userID,
					(int) ($major['dept_ID'] ?? 0)
				]);

				$pdo->prepare(
					"INSERT INTO Full_Time_Undergraduate
						(student_ID, Status, min_Credits, max_Credits, credits_Earned)
					VALUES (?, 'Freshman', 12, 16, 0)"
				)->execute([$userID]);


				// Record the major choice date
				$pdo->prepare(
					"INSERT INTO Student_Major
						(student_ID, major_ID, date_Of_Choice)
					VALUES (?, ?, CURDATE())"
				)->execute([$userID, $majorID]);


				$pdo->commit();

				audit_log(
					$pdo,
					'Create',
					'Student',
					(string) $userID,
					'Self-registration'
				);

				$success =
					'Account created successfully. You can now log in.';


			} catch (PDOException $e) {

				if ($pdo->inTransaction()) {
					$pdo->rollBack();
				}

				error_log('Registration failed: ' . $e->getMessage());

				$error = 'Unable to create the account.';
			}
		}
	}
}

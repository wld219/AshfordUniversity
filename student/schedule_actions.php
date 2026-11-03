<?php

declare(strict_types=1);

// Schedule drop action. Uses $pdo, $studentID from the entry point.
//
// The drop must happen inside the semester's drop window, and the seat is
// only returned when a course was actually dropped.

$crn = (int) $_POST['drop'];

// The drop deadline for the section's semester must still be open
$sectionSemester = one(
	$pdo,
	"SELECT semester_ID
	FROM Course_Section
	WHERE CRN = ?",
	[$crn]
);

if (!$sectionSemester) {

	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => 'Course could not be dropped.'
	];

	header('Location: schedule.php');
	exit;
}

[$dropOpen, $dropMessage] = drop_window_status(
	$pdo,
	(int) $sectionSemester['semester_ID']
);

if (!$dropOpen) {

	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => $dropMessage
	];

	header('Location: schedule.php');
	exit;
}

try {

	$pdo->beginTransaction();


	// Remove the student from the course
	$sql = "DELETE FROM Enrollment
			WHERE student_ID = ?
			AND CRN = ?";

	$stmt = $pdo->prepare($sql);

	$stmt->execute([
		$studentID,
		$crn
	]);


	// Only return the seat if a course was actually dropped
	if ($stmt->rowCount() > 0) {

		$sql = "UPDATE Course_Section
				SET available_Seats = available_Seats + 1
				WHERE CRN = ?";

		$stmt = $pdo->prepare($sql);
		$stmt->execute([$crn]);

		audit_log($pdo, 'Delete', 'Enrollment', (string) $crn, 'Student dropped course');

		$_SESSION['flash'] = [
			'type' => 'success',
			'message' => 'Course dropped.'
		];

	} else {

		$_SESSION['flash'] = [
			'type' => 'error',
			'message' => 'Course could not be dropped.'
		];
	}


	$pdo->commit();

} catch (Throwable $error) {

	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}

	error_log('Course drop failed: ' . $error->getMessage());

	$_SESSION['flash'] = [
		'type' => 'error',
		'message' => 'Course could not be dropped.'
	];
}


header('Location: schedule.php');
exit;

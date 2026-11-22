<?php

declare(strict_types=1);

// All queries in this file are scoped to the authenticated Student session ID.
$studentAuditProfile = one(
	$pdo,
	"SELECT u.user_ID, u.first_Name, u.middle_Name, u.last_Name,
			st.student_Type, st.major_ID, m.major_Name,
			gs.Program AS graduate_Program
	 FROM Student st
	 JOIN User u ON u.user_ID = st.student_ID AND u.user_Type = 'Student'
	 LEFT JOIN Major m ON m.major_ID = st.major_ID
	 LEFT JOIN Graduate_Student gs ON gs.student_ID = st.student_ID
	 WHERE st.student_ID = ?",
	[$studentID]
);

$studentAuditFullName = trim(implode(' ', array_filter([
	(string) ($studentAuditProfile['first_Name'] ?? ''),
	(string) ($studentAuditProfile['middle_Name'] ?? ''),
	(string) ($studentAuditProfile['last_Name'] ?? ''),
], static fn(string $part): bool => $part !== '')));

// Student_Major is the declared-major relationship. Prefer the primary ID
// when it agrees with a declaration; when a single declared major conflicts
// with Student.major_ID, the sole declaration is the unambiguous program.
// Leave multiple conflicting declarations unranked rather than guessing.
$studentAuditDeclaredMajorRows = ($studentAuditProfile['student_Type'] ?? '') === 'Undergraduate'
	? all_rows(
		$pdo,
		"SELECT sm.major_ID, m.major_Name, sm.date_Of_Choice
		 FROM Student_Major sm
		 JOIN Major m ON m.major_ID = sm.major_ID
		 WHERE sm.student_ID = ?
		 ORDER BY sm.date_Of_Choice, sm.major_ID",
		[$studentID]
	)
	: [];
$studentAuditPrimaryMajorID = null;
$storedStudentMajorID = (int) ($studentAuditProfile['major_ID'] ?? 0);
$declaredMajorIDs = array_map(static fn(array $row): int => (int) $row['major_ID'], $studentAuditDeclaredMajorRows);
if ($storedStudentMajorID > 0 && in_array($storedStudentMajorID, $declaredMajorIDs, true)) {
	$studentAuditPrimaryMajorID = $storedStudentMajorID;
} elseif (count($studentAuditDeclaredMajorRows) === 1) {
	$studentAuditPrimaryMajorID = (int) $studentAuditDeclaredMajorRows[0]['major_ID'];
} elseif (!$studentAuditDeclaredMajorRows && $storedStudentMajorID > 0) {
	// Legacy record with no Student_Major rows: retain the Student FK as source.
	$studentAuditPrimaryMajorID = $storedStudentMajorID;
}
$studentAuditPrimaryMajor = $studentAuditPrimaryMajorID > 0
	? one($pdo, 'SELECT major_ID, major_Name FROM Major WHERE major_ID = ?', [$studentAuditPrimaryMajorID])
	: null;
$studentAuditProfile['major_ID'] = $studentAuditPrimaryMajorID;
$studentAuditProfile['major_Name'] = $studentAuditPrimaryMajor['major_Name'] ?? null;
$studentAuditDegreeProgram = ($studentAuditProfile['student_Type'] ?? '') === 'Graduate'
	? (string) ($studentAuditProfile['graduate_Program'] ?? 'Not recorded')
	: 'Not separately configured';
$studentAuditDeclaredMajors = array_values(array_map(
	static fn(array $row): string => (string) $row['major_Name'],
	array_filter($studentAuditDeclaredMajorRows, static fn(array $row): bool => (int) $row['major_ID'] !== (int) ($studentAuditPrimaryMajorID ?? 0))
));

$studentAuditSemesters = student_portal_semesters($pdo);
$studentAuditRequestedSemesterID = filter_input(INPUT_GET, 'semester', FILTER_VALIDATE_INT);
if ($studentAuditRequestedSemesterID === null && isset($_GET['semester']) && is_scalar($_GET['semester'])) {
	$studentAuditRequestedSemesterID = filter_var($_GET['semester'], FILTER_VALIDATE_INT);
}
$studentAuditSemesterID = student_portal_selected_semester_id(
	$studentAuditSemesters,
	is_int($studentAuditRequestedSemesterID) ? $studentAuditRequestedSemesterID : null
);
$studentAuditSemesterName = '';
foreach ($studentAuditSemesters as $term) {
	if ((int)$term['semester_ID'] === (int)$studentAuditSemesterID) {
		$studentAuditSemesterName = (string)$term['semester_Name'];
		break;
	}
}
$studentAuditRegisteredCourses = $studentAuditSemesterID !== null
	? student_semester_registered_courses($pdo, $studentID, $studentAuditSemesterID)
	: [];
$studentAuditSemesterStatus = $studentAuditSemesterID !== null
	? student_semester_enrollment_status($pdo, $studentID, $studentAuditSemesterID)
	: null;
$studentAuditCurrentCourseStatuses = [];
$studentAuditCurrentCourseStatusByCourse = [];
if ($studentAuditSemesterID !== null) {
	$currentEnrollmentRows = all_rows(
		$pdo,
		"SELECT e.CRN, e.Grade AS enrollment_Grade, sh.Grade AS history_Grade,
				c.course_ID, sem.semester_Name
		 FROM Enrollment e
		 JOIN Course_Section cs
			ON cs.CRN = e.CRN
			AND cs.semester_ID = e.semester_ID
			AND cs.is_Cancelled = 0
		 JOIN Course c ON c.course_ID = cs.course_ID
		 JOIN Semester sem ON sem.semester_ID = e.semester_ID
		 LEFT JOIN Student_History sh
			ON sh.student_ID = e.student_ID
			AND sh.CRN = e.CRN
			AND sh.semester_ID = e.semester_ID
		 WHERE e.student_ID = ? AND e.semester_ID = ?
		 ORDER BY c.course_ID, e.CRN",
		[$studentID, $studentAuditSemesterID]
	);
	$currentRowsByCRN = [];
	foreach ($currentEnrollmentRows as $row) {
		$currentRowsByCRN[(int) $row['CRN']] = $row;
	}
	foreach ($studentAuditRegisteredCourses as $registeredCourse) {
		$crn = (int) $registeredCourse['CRN'];
		$currentRow = $currentRowsByCRN[$crn] ?? null;
		if ($currentRow === null) continue;
		$historyGrade = trim((string) ($currentRow['history_Grade'] ?? ''));
		$grade = $historyGrade !== ''
			? $historyGrade
			: ($currentRow['enrollment_Grade'] !== null ? (string) $currentRow['enrollment_Grade'] : null);
		$gradeFromHistory = $historyGrade !== '';
		if (!$gradeFromHistory && (string) $currentRow['semester_Name'] === 'Spring 2027') {
			$grade = null;
		}
		$status = student_course_status(
			$grade,
			(string) $currentRow['semester_Name'],
			!$gradeFromHistory
		);
		$registeredCourse['Grade'] = $grade;
		$registeredCourse['grade_from_history'] = $gradeFromHistory;
		$registeredCourse['status'] = $status;
		$studentAuditCurrentCourseStatuses[] = $registeredCourse;
		$studentAuditCurrentCourseStatusByCourse[(string) $registeredCourse['course_ID']][] = [
			'status' => $status,
			'grade' => trim((string) ($grade ?? '')),
			'term' => (string) $currentRow['semester_Name'],
		];
	}
}

// Student_History is the sole completed-course source. Keep the existing
// best-grade behavior and preserve history rows regardless of section status.
$studentAuditHistory = $studentAuditProfile
	? all_rows(
		$pdo,
		' SELECT sh.CRN, sh.course_ID, sh.Grade, c.course_Credits,
				sem.semester_Name, sem.semester_Start
		  FROM Student_History sh
		  JOIN Course c ON c.course_ID = sh.course_ID
		  JOIN Semester sem ON sem.semester_ID = sh.semester_ID
		  WHERE sh.student_ID = ? AND sh.Grade IS NOT NULL
		  ORDER BY sem.semester_Start DESC, sh.CRN DESC',
		[$studentID]
	)
	: [];

$studentAuditBestGrades = [];
$studentAuditLatestOutcomes = [];
foreach ($studentAuditHistory as $historyRow) {
	$courseID = (string) $historyRow['course_ID'];
	$grade = trim((string) $historyRow['Grade']);
	$points = grade_points($grade);
	if (!isset($studentAuditLatestOutcomes[$courseID])) {
		$studentAuditLatestOutcomes[$courseID] = [
			'status' => student_course_status($grade, (string) $historyRow['semester_Name'], false),
			'grade' => $grade,
			'term' => (string) $historyRow['semester_Name'],
			'semester_Start' => (string) ($historyRow['semester_Start'] ?? ''),
		];
	}

	if (
		$points !== null
		&& (
			!isset($studentAuditBestGrades[$courseID])
			|| $points > $studentAuditBestGrades[$courseID]['points']
		)
	) {
		$studentAuditBestGrades[$courseID] = [
			'grade' => (string) $historyRow['Grade'],
			'points' => $points,
			'term' => (string) $historyRow['semester_Name'],
		];
	}
}

$studentAuditEarnedCourses = [];
$studentAuditGpaPoints = 0.0;
$studentAuditGpaCredits = 0;
foreach ($studentAuditHistory as $historyRow) {
	$points = grade_points((string) $historyRow['Grade']);
	$credits = (int) $historyRow['course_Credits'];
	if ($points === null) continue;
	$studentAuditGpaPoints += $points * $credits;
	$studentAuditGpaCredits += $credits;
	$courseID = (string) $historyRow['course_ID'];
	if ($points >= 1.0 && !isset($studentAuditEarnedCourses[$courseID])) {
		$studentAuditEarnedCourses[$courseID] = $credits;
	}
}
$studentAuditEarnedCredits = array_sum($studentAuditEarnedCourses);
$studentAuditGpa = $studentAuditGpaCredits > 0
	? round($studentAuditGpaPoints / $studentAuditGpaCredits, 2)
	: null;
$studentAuditAcademicStanding = $studentAuditGpa === null
	? 'N/A'
	: ($studentAuditGpa >= 3.5 ? 'Dean\'s List' : ($studentAuditGpa >= 2.0 ? 'Good Standing' : 'Academic Probation'));

$evaluateStudentAuditRequirements = static function (array $requirements) use (
	$studentAuditBestGrades,
	$studentAuditLatestOutcomes,
	$studentAuditCurrentCourseStatusByCourse,
	$studentAuditSemesterName
): array {
	$completed = 0;
	$rows = [];

	foreach ($requirements as $requirement) {
		$courseID = (string) $requirement['course_ID'];
		$earned = $studentAuditBestGrades[$courseID] ?? null;
		$met = $earned !== null
			&& grade_meets($earned['grade'], (string) $requirement['min_Grade_Req']);
		$latestOutcome = $studentAuditLatestOutcomes[$courseID] ?? null;
		$currentOutcomes = $studentAuditCurrentCourseStatusByCourse[$courseID] ?? [];
		$currentOutcome = null;
		foreach (['In Progress', 'Completed', 'Failed', 'Withdrawn', 'Registered'] as $preferredStatus) {
			foreach ($currentOutcomes as $outcome) {
				if ($outcome['status'] === $preferredStatus) {
					$currentOutcome = $outcome;
					break 2;
				}
			}
		}
		$hasPassingAttempt = $earned !== null && $earned['points'] >= 1.0;
		if ($met || ($hasPassingAttempt && $currentOutcome === null)) {
			$status = 'Completed';
		} elseif ($currentOutcome !== null) {
			$status = (string) $currentOutcome['status'];
		} else {
			$status = (string) ($latestOutcome['status'] ?? 'Not Started');
			if (!in_array($status, ['Failed', 'Withdrawn', 'Completed', 'In Progress'], true)) {
				$status = 'Not Started';
			}
		}
		$inProgress = $status === 'In Progress';
		if ($met || ($status === 'Completed' && $currentOutcome === null)) {
			$displayGrade = $earned['grade'] ?? $latestOutcome['grade'] ?? null;
			$displayTerm = $earned['term'] ?? $latestOutcome['term'] ?? '—';
		} else {
			$displayGrade = $currentOutcome['grade'] ?? $latestOutcome['grade'] ?? $earned['grade'] ?? null;
			$displayTerm = $currentOutcome['term'] ?? $latestOutcome['term'] ?? $earned['term'] ?? '—';
		}
		$minimumNotMet = $status === 'Completed'
			&& $displayGrade !== null
			&& !grade_meets((string) $displayGrade, (string) $requirement['min_Grade_Req']);

		if ($met) {
			$completed++;
		}

		$rows[] = [
			'course_ID' => $courseID,
			'course_Name' => (string) $requirement['course_Name'],
			'course_Credits' => (int) $requirement['course_Credits'],
			'min_Grade_Req' => (string) $requirement['min_Grade_Req'],
			'grade' => $displayGrade,
			'term' => $displayTerm,
			'earned_credits' => $met ? (int) $requirement['course_Credits'] : 0,
			'met' => $met,
			'in_progress' => $inProgress,
			'failed' => $status === 'Failed',
			'withdrawn' => $status === 'Withdrawn',
			'minimum_not_met' => $minimumNotMet,
			'status' => $status,
		];
	}

	return [
		'rows' => $rows,
		'completed' => $completed,
		'total' => count($requirements),
	];
};

$studentAuditPrograms = [];
$studentAuditDeclaredMinors = [];
$studentAuditDeclaredMajors = [];
$studentAuditGraduateMessage = null;

if (($studentAuditProfile['student_Type'] ?? null) === 'Undergraduate') {
	$majorByID = [];
	$primaryMajorID = (int) ($studentAuditProfile['major_ID'] ?? 0);

	if ($primaryMajorID > 0) {
		$primaryMajor = one(
			$pdo,
			'SELECT major_ID, major_Name FROM Major WHERE major_ID = ?',
			[$primaryMajorID]
		);
		if ($primaryMajor) {
			$majorByID[$primaryMajorID] = $primaryMajor + ['primary' => true];
		}
	}

	$additionalMajors = all_rows(
		$pdo,
		"SELECT m.major_ID, m.major_Name
		 FROM Student_Major sm
		 JOIN Major m ON m.major_ID = sm.major_ID
		 WHERE sm.student_ID = ?
		 ORDER BY sm.date_Of_Choice, m.major_Name",
		[$studentID]
	);

	foreach ($additionalMajors as $major) {
		$majorID = (int) $major['major_ID'];
		if (!isset($majorByID[$majorID])) {
			$majorByID[$majorID] = $major + ['primary' => false];
		}
	}

	foreach ($majorByID as $major) {
		if (!$major['primary']) {
			$studentAuditDeclaredMajors[] = (string) $major['major_Name'];
		}
		$requirements = all_rows(
			$pdo,
			"SELECT mr.course_ID, mr.min_Grade_Req, c.course_Name, c.course_Credits
			 FROM Major_Requirement mr
			 JOIN Course c ON c.course_ID = mr.course_ID
			 WHERE mr.major_ID = ?
			 ORDER BY mr.course_ID",
			[(int) $major['major_ID']]
		);

		$studentAuditPrograms[] = [
			'type' => 'Major',
			'name' => (string) $major['major_Name'],
			'primary' => (bool) $major['primary'],
		] + $evaluateStudentAuditRequirements($requirements);
	}

	$minorRows = all_rows(
		$pdo,
		"SELECT mn.minor_ID, mn.minor_Name
		 FROM Student_Minor sm
		 JOIN Minor mn ON mn.minor_ID = sm.minor_ID
		 WHERE sm.student_ID = ?
		 ORDER BY sm.date_Of_Choice, mn.minor_Name",
		[$studentID]
	);

	foreach ($minorRows as $minor) {
		$minorID = (int) $minor['minor_ID'];
		$studentAuditDeclaredMinors[] = (string) $minor['minor_Name'];
		$requirements = all_rows(
			$pdo,
			"SELECT mr.course_ID, mr.min_Grade_Req, c.course_Name, c.course_Credits
			 FROM Minor_Requirement mr
			 JOIN Course c ON c.course_ID = mr.course_ID
			 WHERE mr.minor_ID = ?
			 ORDER BY mr.course_ID",
			[$minorID]
		);

		$studentAuditPrograms[] = [
			'type' => 'Minor',
			'name' => (string) $minor['minor_Name'],
			'primary' => false,
		] + $evaluateStudentAuditRequirements($requirements);
	}
} elseif (($studentAuditProfile['student_Type'] ?? null) === 'Graduate') {
	$minorRows = all_rows(
		$pdo,
		"SELECT mn.minor_Name
		 FROM Student_Minor sm
		 JOIN Minor mn ON mn.minor_ID = sm.minor_ID
		 WHERE sm.student_ID = ?
		 ORDER BY sm.date_Of_Choice, mn.minor_Name",
		[$studentID]
	);
	$studentAuditDeclaredMinors = array_map(
		static fn(array $minor): string => (string) $minor['minor_Name'],
		$minorRows
	);
	$studentAuditGraduateMessage = 'Graduate degree requirements not yet configured.';
}

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

$studentAuditSemesters = all_rows(
	$pdo,
	"SELECT semester_ID, semester_Name
	 FROM Semester
	 WHERE semester_Name IN ('Spring 2026', 'Fall 2026', 'Spring 2027')
	 ORDER BY CASE semester_Name
		 WHEN 'Spring 2026' THEN 1
		 WHEN 'Fall 2026' THEN 2
		 WHEN 'Spring 2027' THEN 3
	 END"
);
$studentAuditSemesterIDs = array_map(
	static fn(array $term): int => (int)$term['semester_ID'],
	$studentAuditSemesters
);
$studentAuditDefaultSemesterID = null;
foreach ($studentAuditSemesters as $term) {
	if ($term['semester_Name'] === 'Fall 2026') {
		$studentAuditDefaultSemesterID = (int)$term['semester_ID'];
		break;
	}
}
$studentAuditRequestedSemesterID = filter_input(INPUT_GET, 'semester', FILTER_VALIDATE_INT);
$studentAuditSemesterID = $studentAuditRequestedSemesterID !== null
	&& $studentAuditRequestedSemesterID !== false
	&& in_array($studentAuditRequestedSemesterID, $studentAuditSemesterIDs, true)
	? $studentAuditRequestedSemesterID
	: ($studentAuditDefaultSemesterID ?? ($studentAuditSemesterIDs[0] ?? null));
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
$studentAuditInProgressCourses = $studentAuditSemesterID !== null
	? student_semester_registered_courses($pdo, $studentID, $studentAuditSemesterID, true)
	: [];
$studentAuditInProgressCourseIDs = array_fill_keys(
	array_map(
		static fn(array $course): string => (string) $course['course_ID'],
		$studentAuditInProgressCourses
	),
	true
);

// Student_History is the sole completed-course source. Keep the existing
// best-grade behavior and preserve history rows regardless of section status.
$studentAuditHistory = $studentAuditProfile
	? all_rows(
		$pdo,
		' SELECT sh.course_ID, sh.Grade, c.course_Credits, sem.semester_Name
		  FROM Student_History sh
		  JOIN Course c ON c.course_ID = sh.course_ID
		  JOIN Semester sem ON sem.semester_ID = sh.semester_ID
		  WHERE sh.student_ID = ? AND sh.Grade IS NOT NULL',
		[$studentID]
	)
	: [];

$studentAuditBestGrades = [];
foreach ($studentAuditHistory as $historyRow) {
	$courseID = (string) $historyRow['course_ID'];
	$points = grade_points((string) $historyRow['Grade']);

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
	$studentAuditInProgressCourseIDs,
	$studentAuditSemesterName
): array {
	$completed = 0;
	$rows = [];

	foreach ($requirements as $requirement) {
		$courseID = (string) $requirement['course_ID'];
		$earned = $studentAuditBestGrades[$courseID] ?? null;
		$met = $earned !== null
			&& grade_meets($earned['grade'], (string) $requirement['min_Grade_Req']);
		$inProgress = !$met && isset($studentAuditInProgressCourseIDs[$courseID]);

		if ($met) {
			$completed++;
		}

		$rows[] = [
			'course_ID' => $courseID,
			'course_Name' => (string) $requirement['course_Name'],
			'course_Credits' => (int) $requirement['course_Credits'],
			'min_Grade_Req' => (string) $requirement['min_Grade_Req'],
			'grade' => $earned['grade'] ?? null,
			'term' => $met ? ($earned['term'] ?? '—') : ($inProgress ? $studentAuditSemesterName : '—'),
			'earned_credits' => $met ? (int) $requirement['course_Credits'] : 0,
			'met' => $met,
			'in_progress' => $inProgress,
			'status' => $met ? 'Completed' : ($inProgress ? 'In Progress' : 'Not Started'),
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

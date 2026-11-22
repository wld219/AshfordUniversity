<?php

declare(strict_types=1);

// All overview data is scoped to the authenticated Student session.
$student = one(
	$pdo,
	"SELECT u.user_ID, u.first_Name, u.middle_Name, u.last_Name,
			st.student_Type, st.major_ID, m.major_Name AS stored_major_Name,
			gs.Program AS graduate_Program
	 FROM Student st
	 JOIN User u ON u.user_ID = st.student_ID
	 LEFT JOIN Major m ON m.major_ID = st.major_ID
	 LEFT JOIN Graduate_Student gs ON gs.student_ID = st.student_ID
	 WHERE st.student_ID = ? AND u.user_Type = 'Student'",
	[$studentID]
);

$displayName = trim(implode(' ', array_filter([
	(string) ($student['first_Name'] ?? ''),
	(string) ($student['middle_Name'] ?? ''),
	(string) ($student['last_Name'] ?? ''),
], static fn(string $part): bool => $part !== '')));
$displayName = $displayName !== '' ? $displayName : 'Student';

// Student_Major and Student_Minor are the declaration relationships. A sole
// declared major resolves legacy mismatches with Student.major_ID; never
// relabel a Major catalog row that is explicitly named as a minor.
$studentMajorRows = ($student['student_Type'] ?? '') === 'Undergraduate'
	? all_rows(
		$pdo,
		"SELECT sm.major_ID, m.major_Name
		 FROM Student_Major sm
		 JOIN Major m ON m.major_ID = sm.major_ID
		 WHERE sm.student_ID = ?
		 ORDER BY sm.date_Of_Choice, sm.major_ID",
		[$studentID]
	)
	: [];
$studentMinorRows = all_rows(
	$pdo,
	"SELECT DISTINCT m.minor_Name
	 FROM Student_Minor sm
	 JOIN Minor m ON m.minor_ID = sm.minor_ID
	 WHERE sm.student_ID = ?
	 ORDER BY m.minor_Name",
	[$studentID]
);

$validDeclaredMajors = array_values(array_filter(
	$studentMajorRows,
	static fn(array $major): bool => !preg_match('/\bminor\b/i', (string) $major['major_Name'])
));
$primaryMajor = null;
$storedMajorID = (int) ($student['major_ID'] ?? 0);
	foreach ($validDeclaredMajors as $declaredMajor) {
	if ((int) $declaredMajor['major_ID'] === $storedMajorID) {
		$primaryMajor = $declaredMajor;
		break;
	}
}
if ($primaryMajor === null && count($validDeclaredMajors) === 1) {
	$primaryMajor = $validDeclaredMajors[0];
}
if ($primaryMajor === null && $studentMajorRows === [] && $storedMajorID > 0) {
	$legacyMajor = one($pdo, 'SELECT major_ID, major_Name FROM Major WHERE major_ID = ?', [$storedMajorID]);
	if ($legacyMajor && !preg_match('/\bminor\b/i', (string) $legacyMajor['major_Name'])) {
		$primaryMajor = $legacyMajor;
	}
}
$studentMajorDisplay = $primaryMajor !== null
	? (string) $primaryMajor['major_Name']
	: ($studentMajorRows !== [] || $storedMajorID > 0 ? 'Major needs review' : 'Not Declared');
$studentMinorDisplay = $studentMinorRows
	? implode(', ', array_map(static fn(array $row): string => (string) $row['minor_Name'], $studentMinorRows))
	: 'Not Declared';
$studentProgramLabel = ($student['student_Type'] ?? '') === 'Graduate' ? 'Degree Program' : 'Major';
$studentProgramDisplay = ($student['student_Type'] ?? '') === 'Graduate'
	? (string) ($student['graduate_Program'] ?? 'Not recorded')
	: $studentMajorDisplay;

// Select only Fall 2026 or Spring 2027, with Fall 2026 as the safe default.
$availableSemesters = student_portal_semesters($pdo);
$requestedSemester = filter_input(INPUT_GET, 'semester', FILTER_VALIDATE_INT);
if ($requestedSemester === null && isset($_GET['semester']) && is_scalar($_GET['semester'])) {
	$requestedSemester = filter_var($_GET['semester'], FILTER_VALIDATE_INT);
}
$currentSemester = student_portal_selected_semester_id(
	$availableSemesters,
	is_int($requestedSemester) ? $requestedSemester : null
);
$semesterLabel = 'Semester unavailable';
$currentSemesterLabel = 'Not available yet';
$upcomingSemesterLabel = 'Not available yet';
foreach ($availableSemesters as $term) {
	if ((int) $term['semester_ID'] === (int) $currentSemester) {
		$semesterLabel = (string) $term['semester_Name'];
	}
	if ($term['semester_Name'] === 'Fall 2026') {
		$currentSemesterLabel = (string) $term['semester_Name'];
	}
	if ($term['semester_Name'] === 'Spring 2027') {
		$upcomingSemesterLabel = (string) $term['semester_Name'];
	}
}
$schedule = $currentSemester !== null
	? student_semester_registered_courses($pdo, $studentID, $currentSemester)
	: [];
if ($schedule !== [] && $currentSemester !== null) {
	$sectionCRNs = array_values(array_unique(array_map(
		static fn(array $course): int => (int) $course['CRN'],
		$schedule
	)));
	$marks = implode(',', array_fill(0, count($sectionCRNs), '?'));
	$sectionRows = all_rows(
		$pdo,
		"SELECT CRN, section_No
		 FROM Course_Section
		 WHERE semester_ID = ? AND is_Cancelled = 0 AND CRN IN ($marks)",
		array_merge([$currentSemester], $sectionCRNs)
	);
	$sectionNumbers = [];
	foreach ($sectionRows as $sectionRow) {
		$sectionNumbers[(int) $sectionRow['CRN']] = (int) $sectionRow['section_No'];
	}
	foreach ($schedule as &$course) {
		$course['section_No'] = $sectionNumbers[(int) $course['CRN']] ?? null;
	}
	unset($course);
}
$semesterEnrollmentStatus = $currentSemester !== null
	? student_semester_enrollment_status($pdo, $studentID, $currentSemester)
	: null;

// Advisor has no active flag; show the latest recorded assignment.
$advisor = one(
	$pdo,
	"SELECT CONCAT_WS(' ', u.first_Name, u.last_Name) AS advisor_Name
	 FROM Advisor a
	 JOIN User u ON u.user_ID = a.faculty_ID
	 WHERE a.student_ID = ?
	 ORDER BY a.date_Of_Appnt DESC, a.faculty_ID
	 LIMIT 1",
	[$studentID]
);
$advisorName = (string) ($advisor['advisor_Name'] ?? 'Not assigned');

// Student_Hold has no status/expiry column, so all linked hold records are
// treated as active until the schema provides a different lifecycle field.
$holds = all_rows(
	$pdo,
	"SELECT h.hold_Type
	 FROM Student_Hold sh
	 JOIN Hold h ON h.hold_ID = sh.hold_ID
	 WHERE sh.student_ID = ?
	 ORDER BY h.hold_Type",
	[$studentID]
);
$holdTypes = array_values(array_unique(array_map(
	static fn(array $hold): string => (string) $hold['hold_Type'],
	$holds
)));
$registrationHoldDisplay = $holdTypes === []
	? 'No Active Holds'
	: count($holdTypes) . ' Active Hold' . (count($holdTypes) === 1 ? '' : 's') . ': ' . implode(', ', $holdTypes);

// Keep the existing grade-point scale and credit-weighted transcript behavior.
$historyGrades = all_rows(
	$pdo,
	"SELECT sh.Grade, c.course_Credits
	 FROM Student_History sh
	 JOIN Course c ON c.course_ID = sh.course_ID
	 WHERE sh.student_ID = ? AND sh.Grade IS NOT NULL",
	[$studentID]
);
$weightedPoints = 0.0;
$gradedCredits = 0;
foreach ($historyGrades as $row) {
	$points = grade_points((string) $row['Grade']);
	$credits = (int) $row['course_Credits'];
	if ($points !== null && $credits > 0) {
		$weightedPoints += $points * $credits;
		$gradedCredits += $credits;
	}
}
$gpa = $gradedCredits > 0 ? round($weightedPoints / $gradedCredits, 2) : null;

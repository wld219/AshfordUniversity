<?php

declare(strict_types=1);

// Data for the student dashboard.

// Student information
$student = one(
	$pdo,
	"SELECT
			User.user_ID,
			User.first_Name,
			User.last_Name,
			Student.student_Type,
			Major.major_Name,
			CONCAT(advisor_User.first_Name, ' ', advisor_User.last_Name) AS advisor_Name
		FROM User
		JOIN Student
			ON Student.student_ID = User.user_ID
		LEFT JOIN Major
			ON Major.major_ID = Student.major_ID
		LEFT JOIN Advisor
			ON Advisor.student_ID = Student.student_ID
		LEFT JOIN User AS advisor_User
			ON advisor_User.user_ID = Advisor.faculty_ID
		WHERE User.user_ID = ?",
	[$studentID]
);



// ASHFORD_STUDENT_MINOR_OVERVIEW_V1
$studentMinorNames = [];

$minorRows = all_rows(
    $pdo,
    "SELECT DISTINCT m.minor_Name
     FROM Student_Minor sm
     JOIN Minor m ON m.minor_ID = sm.minor_ID
     WHERE sm.student_ID = ?
     ORDER BY m.minor_Name",
    [$studentID]
);

foreach ($minorRows as $minorRow) {
    $studentMinorNames[] = (string)$minorRow['minor_Name'];
}

$studentMinorDisplay = implode(', ', $studentMinorNames);

// Current semester courses
$availableSemesters = all_rows(
    $pdo,
    "SELECT semester_ID, semester_Name
     FROM Semester
     WHERE semester_Name IN ('Fall 2026', 'Spring 2027')
     ORDER BY CASE semester_Name
         WHEN 'Fall 2026' THEN 1
         WHEN 'Spring 2027' THEN 2
     END"
);

$semesterIDs = array_map(
    static fn(array $row): int => (int) $row['semester_ID'],
    $availableSemesters
);

$requestedSemester = filter_input(INPUT_GET, 'semester', FILTER_VALIDATE_INT);

$currentSemester = (
    $requestedSemester !== null
    && $requestedSemester !== false
    && in_array($requestedSemester, $semesterIDs, true)
)
    ? $requestedSemester
    : ($semesterIDs[0] ?? null);

$schedule = all_rows(
	$pdo,
	"SELECT
			Course_Section.CRN,
			Course.course_ID,
			Course.course_Name,
			Course.course_Credits,
			Enrollment.Grade
		FROM Enrollment
		JOIN Course_Section
			ON Course_Section.CRN = Enrollment.CRN
		JOIN Course
			ON Course.course_ID = Course_Section.course_ID
		WHERE Enrollment.student_ID = ?
			AND Enrollment.semester_ID = ?
		ORDER BY Course.course_ID",
	[$studentID, $currentSemester]
);


// Registration holds
$holds = all_rows(
	$pdo,
	"SELECT
			Hold.hold_Type
		FROM Student_Hold
		JOIN Hold
			ON Hold.hold_ID = Student_Hold.hold_ID
		WHERE Student_Hold.student_ID = ?",
	[$studentID]
);


// Credit totals
$credits = one(
	$pdo,
	"SELECT
			COALESCE(SUM(Course.course_Credits), 0) AS total
		FROM Enrollment
		JOIN Course_Section
			ON Course_Section.CRN = Enrollment.CRN
		JOIN Course
			ON Course.course_ID = Course_Section.course_ID
		WHERE Enrollment.student_ID = ?
			AND Enrollment.semester_ID = ?",
	[$studentID, $currentSemester]
);


// Credit-weighted GPA from completed course history.
// grade_points() excludes grades that do not contribute grade points.
$historyGrades = all_rows(
    $pdo,
    "SELECT sh.Grade, c.course_Credits
     FROM Student_History AS sh
     JOIN Course AS c ON c.course_ID = sh.course_ID
     WHERE sh.student_ID = ? AND sh.Grade IS NOT NULL",
    [$studentID]
) ?? [];

$gpa = null;
$weightedPoints = 0.0;
$gradedCredits = 0.0;
foreach ($historyGrades as $row) {
    $points = grade_points($row['Grade']);
    $courseCredits = (float) $row['course_Credits'];
    if ($points !== null && $courseCredits > 0) {
        $weightedPoints += $points * $courseCredits;
        $gradedCredits += $courseCredits;
    }
}
if ($gradedCredits > 0) {
    $gpa = round($weightedPoints / $gradedCredits, 2);
}

// Midterm grades for current enrolled courses.
$midtermGrades = [];
if ($currentSemester !== null) {
    $midtermRows = all_rows(
        $pdo,
        "SELECT
                e.CRN,
                c.course_ID,
                c.course_Name,
                e.midterm_Grade,
                e.midterm_Submitted_At
            FROM Enrollment e
            JOIN Course_Section cs ON cs.CRN = e.CRN
            JOIN Course c ON c.course_ID = cs.course_ID
            WHERE e.student_ID = ?
                AND e.semester_ID = ?
                AND e.midterm_Grade IS NOT NULL
            ORDER BY c.course_ID",
        [$studentID, $currentSemester]
    ) ?? [];
    foreach ($midtermRows as $row) {
        $midtermGrades[(int) $row['CRN']] = $row;
    }
}

// Upcoming deadlines for the current semester.
$upcomingDeadlines = [];
if ($currentSemester !== null) {
    $deadlineRow = one(
        $pdo,
        "SELECT
                registration_End,
                drop_Deadline,
                grade_Deadline
            FROM Semester
            WHERE semester_ID = ?",
        [$currentSemester]
    );
    if ($deadlineRow) {
        $now = new DateTimeImmutable('now');
        $fields = [
            'registration_End' => 'Registration Deadline',
            'drop_Deadline' => 'Drop Deadline',
            'grade_Deadline' => 'Grade Submission Deadline',
        ];
        foreach ($fields as $dbField => $label) {
            if ($deadlineRow[$dbField] !== null) {
                $deadlineDate = new DateTimeImmutable((string) $deadlineRow[$dbField]);
                $daysRemaining = (int) $now->diff($deadlineDate)->format('%r%a');
                $upcomingDeadlines[] = [
                    'label' => $label,
                    'date' => $deadlineRow[$dbField],
                    'daysRemaining' => $daysRemaining,
                    'isPast' => $daysRemaining < 0,
                ];
            }
        }
        usort($upcomingDeadlines, static fn(array $a, array $b) => $a['daysRemaining'] <=> $b['daysRemaining']);
    }
}

// Total credits earned toward degree (from Student_History).
$degreeCredits = 0;
$degreeCreditRow = one(
    $pdo,
    "SELECT COALESCE(SUM(c.course_Credits), 0) AS total
     FROM Student_History sh
     JOIN Course c ON c.course_ID = sh.course_ID
     WHERE sh.student_ID = ? AND sh.Grade IS NOT NULL",
    [$studentID]
);
if ($degreeCreditRow) {
    $degreeCredits = (int) $degreeCreditRow['total'];
}

// Required credits for graduation (default 120, can be adjusted per major).
$requiredCredits = 120;
$creditProgress = $degreeCredits > 0 ? min(100, round(($degreeCredits / $requiredCredits) * 100, 1)) : 0;

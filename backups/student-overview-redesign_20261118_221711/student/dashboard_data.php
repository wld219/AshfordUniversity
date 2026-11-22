<?php

declare(strict_types=1);

// Data for the student dashboard.

// Student information
$student = one(
	$pdo,
	"SELECT
			User.user_ID,
			User.first_Name,
			User.middle_Name,
			User.last_Name,
			Student.student_Type,
			Major.major_Name,
			Graduate_Student.Program AS graduate_Program,
			CONCAT(advisor_User.first_Name, ' ', advisor_User.last_Name) AS advisor_Name
		FROM User
		JOIN Student
			ON Student.student_ID = User.user_ID
		LEFT JOIN Major
			ON Major.major_ID = Student.major_ID
		LEFT JOIN Graduate_Student
			ON Graduate_Student.student_ID = Student.student_ID
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

// Semester selector and selected-semester course list.
$availableSemesters = student_portal_semesters($pdo);

$requestedSemester = filter_input(INPUT_GET, 'semester', FILTER_VALIDATE_INT);
$currentSemester = student_portal_selected_semester_id(
	$availableSemesters,
	is_int($requestedSemester) ? $requestedSemester : null
);

$schedule = $currentSemester !== null
	? student_semester_registered_courses($pdo, $studentID, (int) $currentSemester)
	: [];

$semesterCourseLists = [];
foreach ($availableSemesters as $term) {
    $termID = (int)$term['semester_ID'];
    $semesterCourseLists[$termID] = $termID === (int)$currentSemester
        ? $schedule
        : student_semester_registered_courses($pdo, $studentID, $termID);
}


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


// Semester credits and status use only active sections in the selected term.
$semesterEnrollmentStatus = $currentSemester !== null
	? student_semester_enrollment_status($pdo, $studentID, (int) $currentSemester)
	: null;


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

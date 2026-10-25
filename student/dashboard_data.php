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


// Current semester courses
$currentSemester = current_registration_semester($pdo);

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


// GPA from completed history (weighted average; not available when no grades)
$historyGrades = all_rows(
	$pdo,
	"SELECT Grade
		FROM Student_History
		WHERE student_ID = ?
			AND Grade IS NOT NULL",
	[$studentID]
);

$gpa = null;

$gradePoints = 0.0;
$gradeCount = 0;

foreach ($historyGrades as $row) {

	$points = grade_points($row['Grade']);

	if ($points !== null) {
		$gradePoints += $points;
		$gradeCount++;
	}
}

if ($gradeCount > 0) {
	$gpa = round($gradePoints / $gradeCount, 2);
}

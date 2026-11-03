<?php

declare(strict_types=1);

// Data for the academic management page.

$departments = all_rows(
	$pdo,
	"SELECT d.*, CONCAT(u.first_Name, ' ', u.last_Name) AS chair_Name
	FROM Department d
	JOIN User u ON u.user_ID = d.chair_ID
	ORDER BY d.dept_Name"
);

$facultyChairs = all_rows(
	$pdo,
	"SELECT f.faculty_ID, CONCAT(u.first_Name, ' ', u.last_Name) AS name
	FROM Faculty f
	JOIN User u ON u.user_ID = f.faculty_ID
	ORDER BY name"
);

$courses = all_rows(
	$pdo,
	"SELECT c.*, d.dept_Name
	FROM Course c
	JOIN Department d ON d.dept_ID = c.dept_ID
	ORDER BY d.dept_Name, c.course_ID
	LIMIT 200"
);

$prerequisites = all_rows(
	$pdo,
	"SELECT cp.course_ID, cp.prerequisite_course_ID, cp.min_Grade_Req,
			c1.course_Name AS course_Name, c2.course_Name AS prereq_Name
	FROM Course_Prerequisite cp
	JOIN Course c1 ON c1.course_ID = cp.course_ID
	JOIN Course c2 ON c2.course_ID = cp.prerequisite_course_ID
	ORDER BY cp.course_ID
	LIMIT 200"
);

$majors = all_rows(
	$pdo,
	"SELECT m.*, d.dept_Name,
		(SELECT COUNT(*) FROM Major_Requirement mr WHERE mr.major_ID = m.major_ID) AS requirement_Count
	FROM Major m
	JOIN Department d ON d.dept_ID = m.dept_ID
	ORDER BY m.major_Name"
);

$minors = all_rows(
	$pdo,
	"SELECT mn.*, d.dept_Name,
		(SELECT COUNT(*) FROM Minor_Requirement mr WHERE mr.minor_ID = mn.minor_ID) AS requirement_Count
	FROM Minor mn
	JOIN Department d ON d.dept_ID = mn.dept_ID
	ORDER BY mn.minor_Name"
);

$courseOptions = all_rows(
	$pdo,
	"SELECT c.course_ID, c.course_Name, d.dept_Name
	FROM Course c
	JOIN Department d ON d.dept_ID = c.dept_ID
	ORDER BY d.dept_Name, c.course_ID"
);

$editCourseID = get_int('edit_course');

$editCourse = null;

if ($editCourseID !== null) {
	$editCourse = one(
		$pdo,
		'SELECT * FROM Course WHERE course_ID = ?',
		[$editCourseID]
	);
}

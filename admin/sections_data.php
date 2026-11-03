<?php

declare(strict_types=1);

// Reference data for the section forms.

$semesters = all_rows(
	$pdo,
	'SELECT semester_ID, semester_Name FROM Semester ORDER BY semester_ID DESC'
);

$departments = all_rows(
	$pdo,
	'SELECT dept_ID, dept_Name FROM Department ORDER BY dept_Name'
);

$courses = all_rows(
	$pdo,
	"SELECT c.course_ID, c.course_Name, c.course_Type, d.dept_Name
	FROM Course c
	JOIN Department d ON d.dept_ID = c.dept_ID
	ORDER BY d.dept_Name, c.course_ID"
);

$faculty = all_rows(
	$pdo,
	"SELECT f.faculty_ID, f.faculty_Type,
			CONCAT(u.first_Name, ' ', u.last_Name) AS name
	FROM Faculty f
	JOIN User u ON u.user_ID = f.faculty_ID
	ORDER BY name"
);

$timeSlots = all_rows(
	$pdo,
	'SELECT time_Slot_ID FROM Time_Slot ORDER BY time_Slot_ID'
);

$lectureRooms = all_rows(
	$pdo,
	"SELECT l.lecture_ID, l.avail_Seats, r.room_Number, b.Bldg_Name
	FROM Lecture l
	JOIN Room r ON r.room_ID = l.lecture_ID
	JOIN Building b ON b.Bldg_ID = r.Bldg_ID
	ORDER BY b.Bldg_Name, r.room_Number"
);

$labRooms = all_rows(
	$pdo,
	"SELECT b.lab_ID, b.no_Of_Workstations, r.room_Number, bl.Bldg_Name
	FROM Lab b
	JOIN Room r ON r.room_ID = b.lab_ID
	JOIN Building bl ON bl.Bldg_ID = r.Bldg_ID
	ORDER BY bl.Bldg_Name, r.room_Number"
);

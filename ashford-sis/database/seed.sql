-- Ashford SIS Seed Data
-- Departments, Majors, Minors, Semesters, Days, Periods, Time Slots,
-- Faculty, Courses, Sections, Students, Advisors, Holds, Enrollments

USE ashford_sis;

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Departments
-- -----------------------------------------------------
INSERT INTO Department (dept_ID, dept_Name, office_ID, Email, Phone_No, dept_Manager) VALUES
(1, 'Computer Science', 101, 'cs@ashford.edu', '555-0101', 'Dr. Chen'),
(2, 'Business Administration', 102, 'bus@ashford.edu', '555-0102', 'Dr. Reyes'),
(3, 'Mathematics', 103, 'math@ashford.edu', '555-0103', 'Dr. Whitfield'),
(4, 'Biology', 104, 'bio@ashford.edu', '555-0104', 'Dr. Whitfield'),
(5, 'English', 105, 'eng@ashford.edu', '555-0105', 'Dr. Chen'),
(6, 'History', 106, 'hist@ashford.edu', '555-0106', 'Dr. Reyes'),
(7, 'Psychology', 107, 'psych@ashford.edu', '555-0107', 'Dr. Chen'),
(8, 'Economics', 108, 'econ@ashford.edu', '555-0108', 'Dr. Whitfield');

-- -----------------------------------------------------
-- Majors
-- -----------------------------------------------------
INSERT INTO Major (major_ID, major_Name, dept_ID) VALUES
(1, 'Computer Science', 1),
(2, 'Information Systems', 1),
(3, 'Business Administration', 2),
(4, 'Finance', 2),
(5, 'Mathematics', 3),
(6, 'Biology', 4),
(7, 'English Literature', 5),
(8, 'History', 6),
(9, 'Psychology', 7),
(10, 'Economics', 8),
(11, 'Cybersecurity', 1),
(12, 'Data Science', 1);

-- -----------------------------------------------------
-- Minors
-- -----------------------------------------------------
INSERT INTO Minor (minor_ID, minor_Name, dept_ID) VALUES
(1, 'Computer Science', 1),
(2, 'Business Administration', 2),
(3, 'Mathematics', 3),
(4, 'Biology', 4),
(5, 'English Literature', 5),
(6, 'History', 6),
(7, 'Psychology', 7),
(8, 'Economics', 8);

-- -----------------------------------------------------
-- Semesters (Fall 2026 and Spring 2027 with 4-week registration windows)
-- -----------------------------------------------------
INSERT INTO Semester (semester_ID, semester_Name, semester_Start, registration_Start, registration_End, drop_Deadline, grade_Deadline) VALUES
(1, 'Fall 2026', '2026-08-24', '2026-04-01 00:00:00', '2026-04-29 23:59:59', '2026-09-14 23:59:59', '2026-12-18 23:59:59'),
(2, 'Spring 2027', '2027-01-11', '2026-11-01 00:00:00', '2026-11-29 23:59:59', '2027-02-01 23:59:59', '2027-05-14 23:59:59');

-- -----------------------------------------------------
-- Days
-- -----------------------------------------------------
INSERT INTO Day (day_ID, day_Of_Week) VALUES
(1, 'Monday'), (2, 'Tuesday'), (3, 'Wednesday'), (4, 'Thursday'),
(5, 'Friday'), (6, 'Saturday'), (7, 'Sunday');

-- -----------------------------------------------------
-- Periods
-- -----------------------------------------------------
INSERT INTO Period (period_ID, start_Time, end_Time) VALUES
(1, '08:00:00', '09:15:00'),
(2, '09:30:00', '10:45:00'),
(3, '11:00:00', '12:15:00'),
(4, '12:30:00', '13:45:00'),
(5, '14:00:00', '15:15:00'),
(6, '15:30:00', '16:45:00'),
(7, '17:00:00', '18:15:00');

-- -----------------------------------------------------
-- Time Slots (combos of days + periods)
-- -----------------------------------------------------
INSERT INTO Time_Slot (time_Slot_ID) VALUES (1),(2),(3),(4),(5),(6),(7),(8),(9),(10),(11),(12);

-- MWF 08:00
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (1,1),(1,3),(1,5);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (1,1);

-- TTh 09:30
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (2,2),(2,4);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (2,2);

-- MWF 11:00
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (3,1),(3,3),(3,5);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (3,3);

-- TTh 12:30
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (4,2),(4,4);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (4,4);

-- MWF 14:00
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (5,1),(5,3),(5,5);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (5,5);

-- TTh 15:30
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (6,2),(6,4);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (6,6);

-- MW 17:00
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (7,1),(7,3);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (7,7);

-- TTh 08:00
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (8,2),(8,4);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (8,1);

-- MWF 09:30
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (9,1),(9,3),(9,5);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (9,2);

-- TTh 11:00
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (10,2),(10,4);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (10,3);

-- MWF 12:30
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (11,1),(11,3),(11,5);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (11,4);

-- TTh 14:00
INSERT INTO Time_Slot_Day (time_Slot_ID, day_ID) VALUES (12,2),(12,4);
INSERT INTO Time_Slot_Period (time_Slot_ID, period_ID) VALUES (12,5);

-- -----------------------------------------------------
-- Users: Admin, Faculty, Students
-- -----------------------------------------------------
-- Password for all: Password123 (bcrypt hash)
SET @pw = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

INSERT INTO User (user_ID, first_Name, middle_Name, last_Name, email, user_Type) VALUES
(1, 'Ariana', '', 'Augustin', 'arianaaugustin@ashford.edu', 'Admin'),
(2, 'Kevin', '', 'Baptiste', 'kevinbaptiste200001@ashford.edu', 'Faculty'),
(3, 'Liam', '', 'Walker', 'liamwalker@ashford.edu', 'Student'),
(4, 'Sarah', '', 'Chen', 'sarahchen@ashford.edu', 'Student'),
(5, 'Marcus', '', 'Reyes', 'marcusreyes@ashford.edu', 'Student'),
(6, 'Elena', '', 'Whitfield', 'elenawhitfield@ashford.edu', 'Student');

INSERT INTO Login (user_ID, user_Email, password_hash, no_Of_Tries, lock_var) VALUES
(1, 'arianaaugustin@ashford.edu', @pw, 0, FALSE),
(2, 'kevinbaptiste200001@ashford.edu', @pw, 0, FALSE),
(3, 'liamwalker@ashford.edu', @pw, 0, FALSE),
(4, 'sarahchen@ashford.edu', @pw, 0, FALSE),
(5, 'marcusreyes@ashford.edu', @pw, 0, FALSE),
(6, 'elenawhitfield@ashford.edu', @pw, 0, FALSE);

-- Admin
INSERT INTO Admin (user_ID, security_Level) VALUES (1, 'Full-Access');

-- Faculty
INSERT INTO Faculty (faculty_ID, office_ID, Specialty, faculty_Rank, faculty_Type, no_of_Classes) VALUES
(2, 201, 'Software Engineering', 'Associate Professor', 'Full-Time', 2);

-- Students
INSERT INTO Student (student_ID, student_Type, enrollment_Status, student_Year, major_ID) VALUES
(3, 'Undergraduate', 'Full-Time', 2, 1),
(4, 'Undergraduate', 'Part-Time', 3, 3),
(5, 'Graduate', 'Full-Time', 1, 11),
(6, 'Undergraduate', 'Full-Time', 1, 7);

INSERT INTO Student_Major (student_ID, major_ID, date_Of_Choice) VALUES
(3, 1, '2025-08-01'),
(4, 3, '2024-08-01'),
(5, 11, '2026-08-01'),
(6, 7, '2026-08-01');

-- -----------------------------------------------------
-- Holds
-- -----------------------------------------------------
INSERT INTO Hold (hold_ID, hold_Type) VALUES
(1, 'Financial Hold'), (2, 'Academic Probation'), (3, 'Advisor Hold');

INSERT INTO Student_Hold (hold_ID, student_ID, hold_Date) VALUES
(1, 4, '2026-03-01');

-- -----------------------------------------------------
-- Advisors
-- -----------------------------------------------------
INSERT INTO Advisor (faculty_ID, student_ID, date_Of_Appnt) VALUES
(2, 3, '2025-08-01'),
(2, 6, '2026-08-01');

-- -----------------------------------------------------
-- Courses
-- -----------------------------------------------------
INSERT INTO Course (course_ID, dept_ID, course_Name, course_Credits, course_Type, course_DSC) VALUES
(101, 1, 'Introduction to Programming', 3, 'Undergraduate', 'Basic programming concepts using Python.'),
(102, 1, 'Data Structures', 3, 'Undergraduate', 'Lists, trees, graphs, and algorithm analysis.'),
(103, 1, 'Database Systems', 3, 'Undergraduate', 'Relational database design and SQL.'),
(104, 1, 'Operating Systems', 3, 'Undergraduate', 'OS concepts, processes, memory management.'),
(201, 1, 'Advanced Algorithms', 3, 'Graduate', 'Graduate-level algorithm design and analysis.'),
(202, 1, 'Machine Learning', 3, 'Graduate', 'Supervised and unsupervised learning.'),
(111, 2, 'Principles of Marketing', 3, 'Undergraduate', 'Marketing fundamentals.'),
(112, 2, 'Financial Accounting', 3, 'Undergraduate', 'Accounting principles and practice.'),
(121, 3, 'Calculus I', 4, 'Undergraduate', 'Limits, derivatives, integrals.'),
(122, 3, 'Calculus II', 4, 'Undergraduate', 'Series, sequences, multivariable intro.'),
(131, 4, 'General Biology', 4, 'Undergraduate', 'Cell biology and genetics.'),
(141, 5, 'Composition I', 3, 'Undergraduate', 'Academic writing and composition.'),
(151, 6, 'World History I', 3, 'Undergraduate', 'Civilizations to 1500.'),
(161, 7, 'Introduction to Psychology', 3, 'Undergraduate', 'Foundations of psychological science.'),
(171, 8, 'Principles of Economics', 3, 'Undergraduate', 'Micro and macroeconomics.');

-- Prerequisites: Data Structures requires Intro to Programming (C- or better)
INSERT INTO Course_Prerequisite (course_ID, prerequisite_course_ID, min_Grade_Req) VALUES
(102, 101, 'C-'),
(103, 101, 'D'),
(104, 102, 'C-'),
(201, 102, 'B-'),
(202, 103, 'B-');

-- -----------------------------------------------------
-- Faculty user for Kevin (already created above as user_ID 2)
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Course Sections (Fall 2026 = semester_ID 1)
-- -----------------------------------------------------
-- CRN 10001-10010: Intro to Programming, various slots
INSERT INTO Course_Section (CRN, course_ID, section_No, faculty_ID, time_Slot_ID, lecture_ID, lab_ID, semester_ID, available_Seats, is_Cancelled) VALUES
(10001, 101, 1, 2, 1, NULL, NULL, 1, 30, 0),
(10002, 101, 2, 2, 2, NULL, NULL, 1, 30, 0),
(10003, 102, 1, 2, 3, NULL, NULL, 1, 25, 0),
(10004, 103, 1, 2, 4, NULL, NULL, 1, 25, 0),
(10005, 104, 1, 2, 5, NULL, NULL, 1, 20, 0),
(10006, 201, 1, 2, 6, NULL, NULL, 1, 15, 0),
(10007, 202, 1, 2, 7, NULL, NULL, 1, 15, 0),
(10008, 111, 1, 2, 8, NULL, NULL, 1, 30, 0),
(10009, 112, 1, 2, 9, NULL, NULL, 1, 30, 0),
(10010, 121, 1, 2, 10, NULL, NULL, 1, 30, 0);

-- Spring 2027 sections (semester_ID 2)
INSERT INTO Course_Section (CRN, course_ID, section_No, faculty_ID, time_Slot_ID, lecture_ID, lab_ID, semester_ID, available_Seats, is_Cancelled) VALUES
(20001, 101, 1, 2, 1, NULL, NULL, 2, 30, 0),
(20002, 102, 1, 2, 3, NULL, NULL, 2, 25, 0),
(20003, 103, 1, 2, 4, NULL, NULL, 2, 25, 0),
(20004, 104, 1, 2, 5, NULL, NULL, 2, 20, 0),
(20005, 121, 1, 2, 10, NULL, NULL, 2, 30, 0),
(20006, 122, 1, 2, 11, NULL, NULL, 2, 25, 0),
(20007, 131, 1, 2, 12, NULL, NULL, 2, 25, 0);

-- -----------------------------------------------------
-- Enrollments: Liam (student 3, FT undergrad) registered Fall 2026
-- -----------------------------------------------------
INSERT INTO Enrollment (student_ID, CRN, semester_ID, Grade) VALUES
(3, 10001, 1, 'A'),
(3, 10003, 1, NULL),
(3, 10005, 1, NULL);

-- Sarah (student 4, PT undergrad, has hold) registered Fall 2026
INSERT INTO Enrollment (student_ID, CRN, semester_ID, Grade) VALUES
(4, 10002, 1, NULL);

-- -----------------------------------------------------
-- Student History (completed work for transcript)
-- -----------------------------------------------------
INSERT INTO Student_History (student_ID, CRN, course_ID, semester_ID, Grade) VALUES
(3, 10001, 101, 1, 'A');

SET FOREIGN_KEY_CHECKS = 1;

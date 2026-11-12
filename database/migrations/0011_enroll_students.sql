-- Migration 0011: enroll remaining students without enrollment.
--
-- Enroll students who have no enrollment records into available course sections.
-- Uses semester 1 (Fall 2026) sections as primary target, falls back to other
-- semesters as needed.

USE ashford_university;

-- Enroll each remaining student into up to 4 sections from semester 1
INSERT IGNORE INTO Enrollment (student_ID, CRN, semester_ID, Grade)
SELECT s.student_ID, cs.CRN, 1, NULL
FROM Student s
JOIN Course_Section cs ON cs.is_Cancelled = 0 AND cs.semester_ID = 1
WHERE NOT EXISTS (SELECT 1 FROM Enrollment e WHERE e.student_ID = s.student_ID)
ORDER BY s.student_ID, RAND()
LIMIT 20000;

-- For any still without enrollment, use any available section
INSERT IGNORE INTO Enrollment (student_ID, CRN, semester_ID, Grade)
SELECT s.student_ID, cs.CRN, cs.semester_ID, NULL
FROM Student s
JOIN Course_Section cs ON cs.is_Cancelled = 0
WHERE NOT EXISTS (SELECT 1 FROM Enrollment e WHERE e.student_ID = s.student_ID)
ORDER BY s.student_ID, cs.CRN
LIMIT 20000;

SELECT COUNT(*) AS remaining_without_enrollment
FROM Student s
WHERE NOT EXISTS (SELECT 1 FROM Enrollment e WHERE e.student_ID = s.student_ID);

SELECT COUNT(*) AS total_enrollments FROM Enrollment;

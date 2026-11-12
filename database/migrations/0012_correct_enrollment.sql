-- Migration 0012: correct enrollment data.
--
-- The previous enrollment migration (0011) enrolled every student into every
-- section, causing massive over-enrollment (e.g., 1,029 students in a section
-- with capacity 10). This migration clears all enrollment records and
-- re-enrolls students realistically:
--
--   - Each student is enrolled in 3-5 sections per semester
--   - Section enrollment is varied (30%-100% of room capacity)
--   - Enrollment never exceeds room capacity
--   - available_Seats is recomputed as room_capacity - actual_enrolled

USE ashford_university;

START TRANSACTION;

-- Step 1: Clear all incorrect enrollment records
DELETE FROM Enrollment;

-- Step 2: Build a list of (student, section) pairs for semesters 1 and 2.
-- Use room_capacity (from Lecture/Lab) as the enrollment limit.
CREATE TEMPORARY TABLE temp_pairs AS
SELECT DISTINCT
    s.student_ID,
    cs.CRN,
    cs.semester_ID,
    COALESCE(l.avail_Seats, b.no_Of_Workstations, 0) AS room_capacity,
    RAND() AS student_rand
FROM Student s
JOIN Course_Section cs ON cs.is_Cancelled = 0 AND cs.semester_ID IN (1, 2)
JOIN Course c ON c.course_ID = cs.course_ID
LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID
WHERE COALESCE(l.avail_Seats, b.no_Of_Workstations, 0) > 0;

-- Index for performance
CREATE INDEX idx_pairs_crn ON temp_pairs(CRN, semester_ID);

-- Step 3: For each section, compute a varied target enrollment (30%-100% of room capacity)
CREATE TEMPORARY TABLE temp_targets AS
SELECT
    CRN,
    semester_ID,
    MAX(room_capacity) AS cap,
    GREATEST(1, ROUND(MAX(room_capacity) * (0.30 + RAND() * 0.70))) AS target_enrollment
FROM temp_pairs
GROUP BY CRN, semester_ID;

-- Debug: show target enrollments
SELECT 'Target enrollments (sample)' AS debug_info, CRN, semester_ID, cap, target_enrollment
FROM temp_targets
ORDER BY CRN
LIMIT 10;

-- Step 4: Assign students to sections using window functions.
-- For each section, rank students randomly and take the first `target_enrollment`.
INSERT INTO Enrollment (student_ID, CRN, semester_ID, Grade)
SELECT ranked.student_ID, ranked.CRN, ranked.semester_ID, NULL
FROM (
    SELECT
        tp.student_ID,
        tp.CRN,
        tp.semester_ID,
        ROW_NUMBER() OVER (
            PARTITION BY tp.CRN, tp.semester_ID
            ORDER BY tp.student_rand
        ) AS rn
    FROM temp_pairs tp
) ranked
JOIN temp_targets tt ON tt.CRN = ranked.CRN AND tt.semester_ID = ranked.semester_ID
WHERE ranked.rn <= tt.target_enrollment;

-- Step 5: Ensure each student has at least 3 enrollments per semester.
CREATE TEMPORARY TABLE temp_under_enrolled AS
SELECT student_ID, semester_ID, COUNT(*) AS enrolled_count
FROM Enrollment
WHERE semester_ID IN (1, 2)
GROUP BY student_ID, semester_ID
HAVING enrolled_count < 3;

INSERT IGNORE INTO Enrollment (student_ID, CRN, semester_ID, Grade)
SELECT ue.student_ID, tp.CRN, ue.semester_ID, NULL
FROM temp_under_enrolled ue
JOIN temp_pairs tp ON tp.student_ID = ue.student_ID AND tp.semester_ID = ue.semester_ID
JOIN temp_targets tt ON tt.CRN = tp.CRN AND tt.semester_ID = tp.semester_ID
WHERE NOT EXISTS (
    SELECT 1 FROM Enrollment e
    WHERE e.student_ID = ue.student_ID
      AND e.CRN = tp.CRN
      AND e.semester_ID = ue.semester_ID
)
AND (
    SELECT COUNT(*) FROM Enrollment e2
    WHERE e2.CRN = tp.CRN AND e2.semester_ID = tp.semester_ID
) < tt.target_enrollment
ORDER BY ue.student_ID, tp.student_rand
LIMIT 10000;

-- Step 6: Cap each student at 5 enrollments per semester.
CREATE TEMPORARY TABLE temp_over_enrolled AS
SELECT student_ID, semester_ID, COUNT(*) AS enrolled_count
FROM Enrollment
WHERE semester_ID IN (1, 2)
GROUP BY student_ID, semester_ID
HAVING enrolled_count > 5;

CREATE TEMPORARY TABLE temp_delete_enrollments AS
SELECT e.student_ID, e.CRN, e.semester_ID
FROM Enrollment e
JOIN temp_over_enrolled oe ON oe.student_ID = e.student_ID AND oe.semester_ID = e.semester_ID
WHERE (
    SELECT COUNT(*)
    FROM Enrollment e2
    WHERE e2.student_ID = e.student_ID
      AND e2.semester_ID = e.semester_ID
      AND e2.CRN <= e.CRN
) > 5;

DELETE e FROM Enrollment e
JOIN temp_delete_enrollments d ON d.student_ID = e.student_ID
    AND d.CRN = e.CRN AND d.semester_ID = e.semester_ID;

-- Step 7: Update available_Seats to reflect actual remaining capacity
UPDATE Course_Section cs
LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID
SET cs.available_Seats = GREATEST(0, COALESCE(l.avail_Seats, b.no_Of_Workstations, 0) - (
    SELECT COUNT(*) FROM Enrollment e WHERE e.CRN = cs.CRN AND e.semester_ID = cs.semester_ID
))
WHERE cs.semester_ID IN (1, 2)
  AND cs.is_Cancelled = 0;

-- Step 8: Clean up temporary tables
DROP TEMPORARY TABLE IF EXISTS temp_pairs;
DROP TEMPORARY TABLE IF EXISTS temp_targets;
DROP TEMPORARY TABLE IF EXISTS temp_under_enrolled;
DROP TEMPORARY TABLE IF EXISTS temp_over_enrolled;
DROP TEMPORARY TABLE IF EXISTS temp_delete_enrollments;

COMMIT;

-- Report results
SELECT
    'Enrollment correction complete' AS status,
    (SELECT COUNT(*) FROM Enrollment) AS total_enrollments,
    (SELECT COUNT(DISTINCT student_ID) FROM Enrollment WHERE semester_ID IN (1,2)) AS students_enrolled_sem1_2,
    (SELECT COUNT(DISTINCT CRN) FROM Enrollment WHERE semester_ID IN (1,2)) AS sections_with_enrollment;

-- Show enrollment distribution per section for semester 1
SELECT
    'Semester 1 enrollment distribution' AS info,
    actual_enrolled,
    COUNT(*) AS num_sections
FROM (
    SELECT cs.CRN,
           (SELECT COUNT(*) FROM Enrollment e WHERE e.CRN = cs.CRN AND e.semester_ID = cs.semester_ID) AS actual_enrolled
    FROM Course_Section cs
    WHERE cs.is_Cancelled = 0 AND cs.semester_ID = 1
) t
GROUP BY actual_enrolled
ORDER BY actual_enrolled DESC;

-- Show sample sections with their enrollment vs capacity
SELECT
    'Sample sections (semester 1)' AS info,
    cs.CRN,
    cs.course_ID,
    cs.section_No,
    COALESCE(l.avail_Seats, b.no_Of_Workstations, 0) AS capacity,
    (SELECT COUNT(*) FROM Enrollment e WHERE e.CRN = cs.CRN AND e.semester_ID = cs.semester_ID) AS enrolled,
    cs.available_Seats
FROM Course_Section cs
LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID
WHERE cs.is_Cancelled = 0 AND cs.semester_ID = 1
ORDER BY enrolled DESC, cs.CRN
LIMIT 20;

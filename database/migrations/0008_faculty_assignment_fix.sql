-- Migration 0008: correct faculty assignment violations.
--
-- Repairs the historical data reported by admin/academic.php so the faculty
-- workload rules hold again, without touching any enrolment records (every
-- excess section below has zero enrolments).
--
-- Rules enforced:
--   - Full-time faculty: at most 2 non-cancelled sections per semester.
--   - Part-time faculty: exactly 1 non-cancelled section per semester.
--   - Full-time faculty: no two overlapping sections in the same semester.
--
-- Strategy: mark the excess sections (is_Cancelled = 1), keeping the lowest
-- CRN sections. Historical enrolment rows are left untouched.

USE ashford_university;

START TRANSACTION;

-- 1. Full-time faculty over the two-section limit: keep the two lowest CRN
--    sections per semester, cancel the rest.
WITH ranked_ft AS (
    SELECT cs.CRN,
           ROW_NUMBER() OVER (
               PARTITION BY cs.faculty_ID, cs.semester_ID
               ORDER BY cs.CRN
           ) AS rn
    FROM Course_Section cs
    JOIN Faculty f ON f.faculty_ID = cs.faculty_ID
    WHERE cs.is_Cancelled = 0
      AND f.faculty_Type = 'Full-Time'
)
UPDATE Course_Section cs
JOIN ranked_ft r ON r.CRN = cs.CRN
SET cs.is_Cancelled = 1
WHERE r.rn > 2;

-- 2. Part-time faculty over the one-section limit: keep the lowest CRN
--    section per semester, cancel the rest.
WITH ranked_pt AS (
    SELECT cs.CRN,
           ROW_NUMBER() OVER (
               PARTITION BY cs.faculty_ID, cs.semester_ID
               ORDER BY cs.CRN
           ) AS rn
    FROM Course_Section cs
    JOIN Faculty f ON f.faculty_ID = cs.faculty_ID
    WHERE cs.is_Cancelled = 0
      AND f.faculty_Type = 'Part-Time'
)
UPDATE Course_Section cs
JOIN ranked_pt r ON r.CRN = cs.CRN
SET cs.is_Cancelled = 1
WHERE r.rn > 1;

-- 3. Full-time faculty with overlapping sections in the same semester:
--    cancel the higher-CRN section of each overlapping pair. (After steps 1
--    and 2 each faculty has at most two sections per semester, so each pair
--    is independent.)
WITH overlapping AS (
    SELECT a.CRN AS crn_a, b.CRN AS crn_b
    FROM Course_Section a
    JOIN Course_Section b
        ON b.faculty_ID = a.faculty_ID
        AND b.CRN > a.CRN
        AND b.is_Cancelled = 0
        AND b.semester_ID = a.semester_ID
    JOIN Faculty f ON f.faculty_ID = a.faculty_ID
    JOIN Time_Slot_Day da ON da.time_Slot_ID = a.time_Slot_ID
    JOIN Time_Slot_Day db ON db.time_Slot_ID = b.time_Slot_ID AND db.day_ID = da.day_ID
    JOIN Time_Slot_Period pa ON pa.time_Slot_ID = a.time_Slot_ID
    JOIN Period ta ON ta.period_ID = pa.period_ID
    JOIN Time_Slot_Period pb ON pb.time_Slot_ID = b.time_Slot_ID
    JOIN Period tb ON tb.period_ID = pb.period_ID
    WHERE f.faculty_Type = 'Full-Time'
      AND a.is_Cancelled = 0
      AND ta.start_Time < tb.end_Time
      AND tb.start_Time < ta.end_Time
)
UPDATE Course_Section cs
JOIN overlapping o ON o.crn_b = cs.CRN
SET cs.is_Cancelled = 1;

COMMIT;

SELECT 'Migration 0008 complete: faculty assignment violations corrected.' AS status;

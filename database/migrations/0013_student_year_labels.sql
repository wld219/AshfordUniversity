-- Migration 0013: Replace numerical student_Year values with academic classifications.
--
-- Changes student_Year from INT to ENUM with text labels:
--   1 -> Freshman, 2 -> Sophomore, 3 -> Junior, 4 -> Senior, 5 -> Graduate
-- MySQL auto-maps existing INT values (1-5) to ENUM positions (1-5) on ALTER.

USE ashford_university;

START TRANSACTION;

-- Change column type from INT to ENUM.
-- MySQL maps existing INT values to ENUM positions:
--   1 -> Freshman (position 1), 2 -> Sophomore (position 2),
--   3 -> Junior (position 3), 4 -> Senior (position 4), 5 -> Graduate (position 5)
ALTER TABLE Student MODIFY COLUMN student_Year ENUM('Freshman','Sophomore','Junior','Senior','Graduate') NOT NULL;

COMMIT;

-- Verify the migration
SELECT student_Year, COUNT(*) AS count FROM Student GROUP BY student_Year ORDER BY FIELD(student_Year, 'Freshman','Sophomore','Junior','Senior','Graduate');

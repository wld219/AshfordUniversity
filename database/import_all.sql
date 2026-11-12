-- ======================================================================
-- Ashford University Portal - Complete Database Import Script
--
-- Usage:
--   mysql -u root < database/import_all.sql
--
-- What it does:
--   1. Creates the ashford_university database (utf8mb4)
--   2. Creates all tables (database/schema.sql) -- already reflects
--      migrations 0002-0005, so those are NOT re-run
--   3. Applies migration 0006 (appointments table) -- new since schema.sql
--   4. Temporarily drops the three business-logic triggers so historical
--      data (e.g. past attendance dates) can load
--   5. Imports every seeded data file from database/*.sql
--   6. Re-creates the triggers and runs migration 0005's Admin backfill
--
-- Data imports run with foreign_key_checks disabled for safe import;
-- checks are re-enabled at the end.
-- ======================================================================

CREATE DATABASE IF NOT EXISTS ashford_university
	CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ashford_university;

-- --------------------------------------------------------
-- 1. Schema (includes migrations 0002-0005 state + trigger definitions)
-- --------------------------------------------------------
SOURCE database/schema.sql;

-- --------------------------------------------------------
-- 2. Migration 0006: appointments table (new since schema.sql)
--    CREATE TABLE IF NOT EXISTS, safe to run
-- --------------------------------------------------------
SOURCE database/migrations/0006_appointments.sql;

-- --------------------------------------------------------
-- 3. Migration 0007: lock-manager flag on administrators
-- --------------------------------------------------------
SOURCE database/migrations/0007_lock_manager.sql;

-- --------------------------------------------------------
-- 3. Temporarily drop business-logic triggers so historical data loads
-- --------------------------------------------------------
DROP TRIGGER IF EXISTS trg_attendance_current_day;
DROP TRIGGER IF EXISTS trg_enrollment_grade_deadline;
DROP TRIGGER IF EXISTS trg_faculty_max_departments;

-- --------------------------------------------------------
-- 4. Seed data (foreign key checks disabled for safe import)
-- --------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;

-- Core reference / identity
SOURCE database/user.sql;
SOURCE database/department.sql;
SOURCE database/building.sql;
SOURCE database/room.sql;
SOURCE database/lecture.sql;
SOURCE database/lab.sql;
SOURCE database/office.sql;
SOURCE database/faculty.sql;
SOURCE database/student.sql;
SOURCE database/login.sql;
SOURCE database/major.sql;
SOURCE database/minor.sql;
SOURCE database/course.sql;
SOURCE database/course_prerequisite.sql;
SOURCE database/time_slot.sql;
SOURCE database/time_slot_day.sql;
SOURCE database/time_slot_period.sql;
SOURCE database/period.sql;
SOURCE database/day.sql;
SOURCE database/semester.sql;

-- Memberships & relationships
SOURCE database/faculty_department.sql;
SOURCE database/faculty_history.sql;
SOURCE database/student_major.sql;
SOURCE database/student_minor.sql;
SOURCE database/student_hold.sql;
SOURCE database/hold.sql;

-- Sections & registration data
SOURCE database/course_section.sql;
SOURCE database/enrollment.sql;
SOURCE database/attendance.sql;
SOURCE database/advisor.sql;

-- Historical & role-specific records
SOURCE database/historical_course_section.sql;
SOURCE database/full_time_faculty.sql;
SOURCE database/part_time_faculty.sql;
SOURCE database/full_time_graduate.sql;
SOURCE database/part_time_graduate.sql;
SOURCE database/full_time_undergraduate.sql;
SOURCE database/part_time_undergraduate.sql;
SOURCE database/graduate_student.sql;
SOURCE database/undergraduate.sql;
SOURCE database/stat_staff.sql;
SOURCE database/admin.sql;

-- Requirements
SOURCE database/major_requirement.sql;
SOURCE database/minor_requirement.sql;

-- Student history
SOURCE database/student_history.sql;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 5. Re-create business-logic triggers after data load
-- --------------------------------------------------------
DROP TRIGGER IF EXISTS trg_faculty_max_departments;
DELIMITER //
CREATE TRIGGER trg_faculty_max_departments
BEFORE INSERT ON Faculty_Department
FOR EACH ROW
BEGIN
    DECLARE dept_count INT;
    SELECT COUNT(*) INTO dept_count
    FROM Faculty_Department
    WHERE faculty_ID = NEW.faculty_ID;
    IF dept_count >= 3 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Faculty may belong to at most 3 departments.';
    END IF;
END//

DELIMITER ;

-- --------------------------------------------------------
DROP TRIGGER IF EXISTS trg_enrollment_grade_deadline;
DELIMITER //
CREATE TRIGGER trg_enrollment_grade_deadline
BEFORE UPDATE ON Enrollment
FOR EACH ROW
BEGIN
    DECLARE deadline DATETIME;
    IF NEW.Grade IS NOT NULL AND NEW.Grade <> OLD.Grade THEN
        SELECT s.grade_Deadline INTO deadline
        FROM Semester s WHERE s.semester_ID = NEW.semester_ID;
        IF deadline IS NOT NULL AND NOW() > deadline THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Grade submission deadline has passed for this semester.';
        END IF;
    END IF;
END//

DELIMITER ;

-- --------------------------------------------------------
DROP TRIGGER IF EXISTS trg_attendance_current_day;
DELIMITER //
CREATE TRIGGER trg_attendance_current_day
BEFORE INSERT ON Attendance
FOR EACH ROW
BEGIN
    IF NEW.attendance_Date <> CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Attendance can only be recorded for the current day.';
    END IF;
END//

DELIMITER ;

-- --------------------------------------------------------
-- 6. Migration 0005 backfill: populate Admin table from Login
--    (CREATE TABLE IF NOT EXISTS + NOT EXISTS backfill = idempotent)
--    Run after Login data is imported
-- --------------------------------------------------------
SOURCE database/migrations/0005_admin.sql;

-- --------------------------------------------------------
-- 7. Migration 0008: correct faculty assignment violations
--    (idempotent: only cancels sections currently in violation)
-- --------------------------------------------------------
SOURCE database/migrations/0008_faculty_assignment_fix.sql;

-- --------------------------------------------------------
-- 8. Migration 0009: faculty availability table
-- --------------------------------------------------------
SOURCE database/migrations/0009_faculty_availability.sql;

-- --------------------------------------------------------
-- 9. Migration 0010: create login accounts for faculty missing them
-- --------------------------------------------------------
SOURCE database/migrations/0010_faculty_logins.sql;

-- --------------------------------------------------------
-- 10. Migration 0012: correct enrollment data (replaces bad data from 0011)
--     Clears all enrollment records and re-enrolls students realistically:
--     3-5 sections per student, varied section fill rates, never exceeds capacity.
-- --------------------------------------------------------
SOURCE database/migrations/0012_correct_enrollment.sql;

-- --------------------------------------------------------
-- 11. Migration 0013: Replace numerical student_Year values with academic classifications.
--     Changes student_Year from INT to ENUM ('Freshman','Sophomore','Junior','Senior','Graduate').
-- --------------------------------------------------------
SOURCE database/migrations/0013_student_year_labels.sql;

-- --------------------------------------------------------
-- 12. (Re)create all business-logic triggers, including the faculty
--     workload and availability enforcement triggers. Must run AFTER data
--     load and the 0008 fix so seed import is not blocked by the new rules.
-- --------------------------------------------------------
SOURCE database/triggers.sql;

-- --------------------------------------------------------
-- Done
-- --------------------------------------------------------
SELECT 'Ashford University database import complete.' AS status;

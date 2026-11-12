-- ======================================================================
-- Ashford University Portal - Database Triggers
--
-- Consolidated business-logic triggers for the current schema
-- (database/schema.sql). Previously split across schema.sql and
-- import_all.sql; kept here as a single source of truth.
--
-- Usage:
--   mysql -u root ashford_university < database/triggers.sql
-- ======================================================================

USE ashford_university;

-- -----------------------------------------------------
-- TABLE OF TRIGGERS
-- Trigger                      Table             Timing      Event   Rule enforced
-- -------                      -----             -------     -----   -------------
-- trg_faculty_max_departments  Faculty_Department BEFORE      INSERT  Faculty belongs to at most 3 departments
-- trg_enrollment_grade_deadline Enrollment       BEFORE        UPDATE  No grade changes after semester grade_Deadline
-- trg_attendance_current_day   Attendance       BEFORE        INSERT  Attendance date must be the current day
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Trigger: Faculty may belong to at most 3 departments
-- -----------------------------------------------------
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

-- -----------------------------------------------------
-- Trigger: block grade updates after semester grade deadline
-- -----------------------------------------------------
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

-- -----------------------------------------------------
-- Trigger: restrict attendance recording to current day
-- -----------------------------------------------------
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

-- -----------------------------------------------------
-- Trigger: enforce faculty section load per semester
--   Full-time: at most 2 sections per semester
--   Part-time: exactly 1 section per semester
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS trg_faculty_section_load;
DELIMITER //
CREATE TRIGGER trg_faculty_section_load
BEFORE INSERT ON Course_Section
FOR EACH ROW
BEGIN
    DECLARE ft ENUM('Full-Time','Part-Time');
    DECLARE cnt INT;
    SELECT faculty_Type INTO ft FROM Faculty WHERE faculty_ID = NEW.faculty_ID;
    IF ft IS NOT NULL THEN
        SELECT COUNT(*) INTO cnt
        FROM Course_Section
        WHERE faculty_ID = NEW.faculty_ID
          AND semester_ID = NEW.semester_ID
          AND is_Cancelled = 0;
        IF ft = 'Full-Time' AND cnt >= 2 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Full-time faculty may teach at most two sections per semester.';
        END IF;
        IF ft = 'Part-Time' AND cnt >= 1 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Part-time faculty may teach exactly one section per semester.';
        END IF;
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS trg_faculty_section_load_update;
DELIMITER //
CREATE TRIGGER trg_faculty_section_load_update
BEFORE UPDATE ON Course_Section
FOR EACH ROW
BEGIN
    DECLARE ft ENUM('Full-Time','Part-Time');
    DECLARE cnt INT;
    SELECT faculty_Type INTO ft FROM Faculty WHERE faculty_ID = NEW.faculty_ID;
    IF ft IS NOT NULL THEN
        SELECT COUNT(*) INTO cnt
        FROM Course_Section
        WHERE faculty_ID = NEW.faculty_ID
          AND semester_ID = NEW.semester_ID
          AND is_Cancelled = 0
          AND CRN <> NEW.CRN;
        IF ft = 'Full-Time' AND cnt >= 2 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Full-time faculty may teach at most two sections per semester.';
        END IF;
        IF ft = 'Part-Time' AND cnt >= 1 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Part-time faculty may teach exactly one section per semester.';
        END IF;
    END IF;
END//
DELIMITER ;

-- -----------------------------------------------------
-- Trigger: prevent full-time faculty from teaching overlapping sections
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS trg_faculty_no_overlap;
DELIMITER //
CREATE TRIGGER trg_faculty_no_overlap
BEFORE INSERT ON Course_Section
FOR EACH ROW
BEGIN
    DECLARE ft ENUM('Full-Time','Part-Time');
    DECLARE overlap_cnt INT;
    SELECT faculty_Type INTO ft FROM Faculty WHERE faculty_ID = NEW.faculty_ID;
    IF ft = 'Full-Time' THEN
        SELECT COUNT(*) INTO overlap_cnt
        FROM Course_Section b
        JOIN Time_Slot_Day da ON da.time_Slot_ID = NEW.time_Slot_ID
        JOIN Time_Slot_Day db ON db.time_Slot_ID = b.time_Slot_ID AND db.day_ID = da.day_ID
        JOIN Time_Slot_Period pa ON pa.time_Slot_ID = NEW.time_Slot_ID
        JOIN Period ta ON ta.period_ID = pa.period_ID
        JOIN Time_Slot_Period pb ON pb.time_Slot_ID = b.time_Slot_ID
        JOIN Period tb ON tb.period_ID = pb.period_ID
        WHERE b.faculty_ID = NEW.faculty_ID
          AND b.semester_ID = NEW.semester_ID
          AND b.is_Cancelled = 0
          AND ta.start_Time < tb.end_Time
          AND tb.start_Time < ta.end_Time;
        IF overlap_cnt > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Full-time faculty may not teach overlapping sections in the same semester.';
        END IF;
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS trg_faculty_no_overlap_update;
DELIMITER //
CREATE TRIGGER trg_faculty_no_overlap_update
BEFORE UPDATE ON Course_Section
FOR EACH ROW
BEGIN
    DECLARE ft ENUM('Full-Time','Part-Time');
    DECLARE overlap_cnt INT;
    SELECT faculty_Type INTO ft FROM Faculty WHERE faculty_ID = NEW.faculty_ID;
    IF ft = 'Full-Time' THEN
        SELECT COUNT(*) INTO overlap_cnt
        FROM Course_Section b
        JOIN Time_Slot_Day da ON da.time_Slot_ID = NEW.time_Slot_ID
        JOIN Time_Slot_Day db ON db.time_Slot_ID = b.time_Slot_ID AND db.day_ID = da.day_ID
        JOIN Time_Slot_Period pa ON pa.time_Slot_ID = NEW.time_Slot_ID
        JOIN Period ta ON ta.period_ID = pa.period_ID
        JOIN Time_Slot_Period pb ON pb.time_Slot_ID = b.time_Slot_ID
        JOIN Period tb ON tb.period_ID = pb.period_ID
        WHERE b.faculty_ID = NEW.faculty_ID
          AND b.semester_ID = NEW.semester_ID
          AND b.is_Cancelled = 0
          AND b.CRN <> NEW.CRN
          AND ta.start_Time < tb.end_Time
          AND tb.start_Time < ta.end_Time;
        IF overlap_cnt > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Full-time faculty may not teach overlapping sections in the same semester.';
        END IF;
    END IF;
END//
DELIMITER ;

-- -----------------------------------------------------
-- Trigger: Faculty availability must be within school days (Mon-Fri)
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS trg_availability_school_day;
DELIMITER //
CREATE TRIGGER trg_availability_school_day
BEFORE INSERT ON Faculty_Availability
FOR EACH ROW
BEGIN
    IF NEW.day_ID < 1 OR NEW.day_ID > 5 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Availability may only be set Monday through Friday.';
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS trg_availability_school_day_update;
DELIMITER //
CREATE TRIGGER trg_availability_school_day_update
BEFORE UPDATE ON Faculty_Availability
FOR EACH ROW
BEGIN
    IF NEW.day_ID < 1 OR NEW.day_ID > 5 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Availability may only be set Monday through Friday.';
    END IF;
END//
DELIMITER ;

-- -----------------------------------------------------
-- Trigger: Faculty availability must be within school hours (08:00-17:00)
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS trg_availability_school_hours;
DELIMITER //
CREATE TRIGGER trg_availability_school_hours
BEFORE INSERT ON Faculty_Availability
FOR EACH ROW
BEGIN
    IF NEW.start_Time < '08:00:00' OR NEW.end_Time > '17:00:00' OR NEW.start_Time >= NEW.end_Time THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Availability must be within school hours (08:00-17:00) and start before end.';
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS trg_availability_school_hours_update;
DELIMITER //
CREATE TRIGGER trg_availability_school_hours_update
BEFORE UPDATE ON Faculty_Availability
FOR EACH ROW
BEGIN
    IF NEW.start_Time < '08:00:00' OR NEW.end_Time > '17:00:00' OR NEW.start_Time >= NEW.end_Time THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Availability must be within school hours (08:00-17:00) and start before end.';
    END IF;
END//
DELIMITER ;

-- -----------------------------------------------------
-- Trigger: Faculty availability must not overlap their own class sections
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS trg_availability_no_class_conflict;
DELIMITER //
CREATE TRIGGER trg_availability_no_class_conflict
BEFORE INSERT ON Faculty_Availability
FOR EACH ROW
BEGIN
    DECLARE conflict_cnt INT;
    SELECT COUNT(*) INTO conflict_cnt
    FROM Course_Section cs
    JOIN Time_Slot_Day tsd ON tsd.time_Slot_ID = cs.time_Slot_ID
    JOIN Time_Slot_Period tsp ON tsp.time_Slot_ID = cs.time_Slot_ID
    JOIN Period p ON p.period_ID = tsp.period_ID
    WHERE cs.faculty_ID = NEW.faculty_ID
      AND cs.is_Cancelled = 0
      AND tsd.day_ID = NEW.day_ID
      AND p.start_Time < NEW.end_Time
      AND NEW.start_Time < p.end_Time;
    IF conflict_cnt > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Availability overlaps one of your own class sections.';
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS trg_availability_no_class_conflict_update;
DELIMITER //
CREATE TRIGGER trg_availability_no_class_conflict_update
BEFORE UPDATE ON Faculty_Availability
FOR EACH ROW
BEGIN
    DECLARE conflict_cnt INT;
    SELECT COUNT(*) INTO conflict_cnt
    FROM Course_Section cs
    JOIN Time_Slot_Day tsd ON tsd.time_Slot_ID = cs.time_Slot_ID
    JOIN Time_Slot_Period tsp ON tsp.time_Slot_ID = cs.time_Slot_ID
    JOIN Period p ON p.period_ID = tsp.period_ID
    WHERE cs.faculty_ID = NEW.faculty_ID
      AND cs.is_Cancelled = 0
      AND tsd.day_ID = NEW.day_ID
      AND p.start_Time < NEW.end_Time
      AND NEW.start_Time < p.end_Time;
    IF conflict_cnt > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Availability overlaps one of your own class sections.';
    END IF;
END//
DELIMITER ;

-- -----------------------------------------------------
-- Trigger: Faculty availability must not overlap existing availability
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS trg_availability_no_overlap;
DELIMITER //
CREATE TRIGGER trg_availability_no_overlap
BEFORE INSERT ON Faculty_Availability
FOR EACH ROW
BEGIN
    DECLARE overlap_cnt INT;
    SELECT COUNT(*) INTO overlap_cnt
    FROM Faculty_Availability fa
    WHERE fa.faculty_ID = NEW.faculty_ID
      AND fa.day_ID = NEW.day_ID
      AND fa.availability_ID <> NEW.availability_ID
      AND fa.start_Time < NEW.end_Time
      AND NEW.start_Time < fa.end_Time;
    IF overlap_cnt > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Availability overlaps an existing availability window.';
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS trg_availability_no_overlap_update;
DELIMITER //
CREATE TRIGGER trg_availability_no_overlap_update
BEFORE UPDATE ON Faculty_Availability
FOR EACH ROW
BEGIN
    DECLARE overlap_cnt INT;
    SELECT COUNT(*) INTO overlap_cnt
    FROM Faculty_Availability fa
    WHERE fa.faculty_ID = NEW.faculty_ID
      AND fa.day_ID = NEW.day_ID
      AND fa.availability_ID <> NEW.availability_ID
      AND fa.start_Time < NEW.end_Time
      AND NEW.start_Time < fa.end_Time;
    IF overlap_cnt > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Availability overlaps an existing availability window.';
    END IF;
END//
DELIMITER ;

SELECT 'Triggers applied.' AS status;

-- Migration 0013: Make Faculty_Availability semester-specific.
-- Run once on existing databases after backing up Faculty_Availability.
-- Existing rows retain semester_ID = NULL and are not bookable until assigned.

ALTER TABLE Faculty_Availability
    ADD COLUMN semester_ID INT NULL AFTER faculty_ID,
    DROP INDEX uq_faculty_availability,
    ADD UNIQUE KEY uq_faculty_availability_term (faculty_ID, semester_ID, day_ID, start_Time),
    ADD INDEX idx_faculty_availability_term_active
        (faculty_ID, semester_ID, is_active, day_ID, start_Time, end_Time),
    ADD CONSTRAINT fk_faculty_availability_semester
        FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID);

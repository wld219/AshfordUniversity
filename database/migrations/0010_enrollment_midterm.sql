-- Migration 0010: Midterm grade columns on Enrollment.
ALTER TABLE Enrollment
    ADD COLUMN midterm_Grade VARCHAR(5) NULL AFTER Grade,
    ADD COLUMN midterm_Submitted_At DATETIME NULL AFTER midterm_Grade;

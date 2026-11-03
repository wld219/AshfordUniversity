-- Migration 0003: semester start dates.
--
-- Run this ONCE against a database created before schedule-lock rules
-- existed. Fresh installs already include these changes via schema.sql.
--
-- semester_Start drives the schedule restrictions:
--   - Past-semester schedules cannot be updated.
--   - Current-semester schedules cannot be updated once the semester begins.
--   - Room / time-slot / faculty assignments cannot change after the start.

ALTER TABLE Semester
	ADD COLUMN semester_Start DATE DEFAULT NULL AFTER semester_Name;

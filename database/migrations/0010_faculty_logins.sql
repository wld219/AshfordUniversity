-- Migration 0010: create login accounts for faculty missing them.
--
-- 72 faculty records exist in the Faculty table but have no Login account,
-- so they cannot sign in to the faculty portal. This migration creates
-- Login records for all of them using the standard password hash.

USE ashford_university;

SOURCE /tmp/faculty_logins.sql;

SELECT 'Migration 0010 complete: faculty login accounts created.' AS status;

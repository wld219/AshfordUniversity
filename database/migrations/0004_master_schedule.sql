-- Migration 0004: master schedule records.
--
-- Adds the schedule-level record used by the Master Schedule Management
-- System. Course assignments themselves live in Course_Section; this table
-- only tracks status, optimistic-concurrency version, and audit actors.
-- Run ONCE on existing databases; fresh installs get it from schema.sql.

CREATE TABLE IF NOT EXISTS Master_Schedule (
	semester_ID INT PRIMARY KEY,
	status ENUM('Draft', 'Published') NOT NULL DEFAULT 'Draft',
	version INT NOT NULL DEFAULT 1,
	created_By_User_ID INT NULL,
	created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_By_User_ID INT NULL,
	updated_At TIMESTAMP NULL,
	published_By_User_ID INT NULL,
	published_At TIMESTAMP NULL,
	FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID),
	FOREIGN KEY (created_By_User_ID) REFERENCES User(user_ID),
	FOREIGN KEY (updated_By_User_ID) REFERENCES User(user_ID),
	FOREIGN KEY (published_By_User_ID) REFERENCES User(user_ID)
);

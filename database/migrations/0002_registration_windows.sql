-- Migration 0002: registration windows, section cancellations, grade exceptions.
--
-- Run this ONCE against a database created before these features existed.
-- Fresh installs already contain these changes via database/schema.sql.
--
-- MySQL (XAMPP ships MariaDB; both accept these statements):

ALTER TABLE Semester
	ADD COLUMN registration_Start DATE DEFAULT NULL AFTER semester_Name,
	ADD COLUMN registration_End DATE DEFAULT NULL AFTER registration_Start,
	ADD COLUMN drop_Deadline DATE DEFAULT NULL AFTER registration_End,
	ADD COLUMN grade_Deadline DATETIME DEFAULT NULL AFTER drop_Deadline;

ALTER TABLE Course_Section
	ADD COLUMN is_Cancelled TINYINT(1) NOT NULL DEFAULT 0 AFTER available_Seats;

CREATE TABLE IF NOT EXISTS Section_Cancellation (
	cancellation_ID INT AUTO_INCREMENT PRIMARY KEY,
	CRN INT NOT NULL,
	enrolled_Count INT NOT NULL,
	reason VARCHAR(255) NOT NULL,
	cancelled_By_User_ID INT NULL,
	created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (CRN) REFERENCES Course_Section(CRN),
	FOREIGN KEY (cancelled_By_User_ID) REFERENCES User(user_ID)
);

CREATE TABLE IF NOT EXISTS Grade_Exception (
	exception_ID INT AUTO_INCREMENT PRIMARY KEY,
	student_ID INT NOT NULL,
	CRN INT NOT NULL,
	reason VARCHAR(255) NOT NULL,
	approved_By_User_ID INT NULL,
	created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
	FOREIGN KEY (CRN) REFERENCES Course_Section(CRN),
	FOREIGN KEY (approved_By_User_ID) REFERENCES User(user_ID)
);

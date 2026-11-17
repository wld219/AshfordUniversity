-- Migration 0008: Waitlist table for full course sections.
-- Run ONCE against existing databases; fresh installs get this from schema.sql.

CREATE TABLE IF NOT EXISTS Waitlist (
    waitlist_ID INT AUTO_INCREMENT PRIMARY KEY,
    student_ID INT NOT NULL,
    CRN INT NOT NULL,
    semester_ID INT NOT NULL,
    position INT NOT NULL DEFAULT 0,
    enrolled_At DATETIME NULL,
    created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (CRN) REFERENCES Course_Section(CRN),
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID),
    UNIQUE KEY uq_waitlist_student_crn_semester (student_ID, CRN, semester_ID)
) ENGINE=InnoDB;

CREATE INDEX idx_waitlist_crn_semester ON Waitlist(CRN, semester_ID);
CREATE INDEX idx_waitlist_student_active ON Waitlist(student_ID, enrolled_At);

-- Migration 0009: faculty availability (office hours) for advisement appointments.
--
-- Faculty define recurring weekly availability windows. Students book
-- advisement appointments inside those windows. Availability may not
-- overlap a faculty member's own class sections or fall outside school
-- hours/days.

USE ashford_university;

CREATE TABLE IF NOT EXISTS Faculty_Availability (
    availability_ID INT AUTO_INCREMENT PRIMARY KEY,
    faculty_ID INT NOT NULL,
    day_ID INT NOT NULL,
    start_Time TIME NOT NULL,
    end_Time TIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_At TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    FOREIGN KEY (day_ID) REFERENCES Day(day_ID),
    UNIQUE KEY uq_faculty_availability (faculty_ID, day_ID, start_Time, end_Time)
) ENGINE=InnoDB;

SELECT 'Migration 0009 complete: Faculty_Availability table created.' AS status;

-- Migration 0009: Faculty availability windows for advisor booking.
-- Run ONCE against existing databases; fresh installs get this from schema.sql.

CREATE TABLE IF NOT EXISTS Faculty_Availability (
    availability_ID INT AUTO_INCREMENT PRIMARY KEY,
    faculty_ID INT NOT NULL,
    day_ID INT NOT NULL,
    start_Time TIME NOT NULL,
    end_Time TIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    FOREIGN KEY (day_ID) REFERENCES Day(day_ID),
    UNIQUE KEY uq_faculty_availability (faculty_ID, day_ID, start_Time),
    INDEX idx_faculty_availability_active (faculty_ID, is_active, day_ID)
) ENGINE=InnoDB;

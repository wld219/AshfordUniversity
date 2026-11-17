-- Migration 0012: Advising_Appointment table for faculty advising workflow.
-- Referenced by faculty/advisees.php for appointment conflict checking
-- and creation when assigning advisees.
-- Run ONCE against existing databases; fresh installs get this from schema.sql.

CREATE TABLE IF NOT EXISTS Advising_Appointment (
    appointment_ID INT AUTO_INCREMENT PRIMARY KEY,
    student_ID INT NOT NULL,
    faculty_ID INT NOT NULL,
    requested_DateTime DATETIME NOT NULL,
    status ENUM('Pending','Accepted','Denied','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
    student_Reason VARCHAR(500) NOT NULL DEFAULT '',
    created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    INDEX idx_advising_appointment_faculty_status (faculty_ID, status),
    INDEX idx_advising_appointment_student_status (student_ID, status),
    INDEX idx_advising_appointment_datetime (requested_DateTime)
) ENGINE=InnoDB;

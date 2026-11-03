CREATE TABLE IF NOT EXISTS Appointment (
    appointment_ID INT AUTO_INCREMENT PRIMARY KEY,
    student_ID INT NOT NULL,
    faculty_ID INT NOT NULL,
    slot_start DATETIME NOT NULL,
    slot_end DATETIME NOT NULL,
    topic VARCHAR(255) NULL,
    notes TEXT NULL,
    status ENUM('Pending','Accepted','Denied','Rescheduled','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
    reason VARCHAR(255) NULL,
    created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_At TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID)
);

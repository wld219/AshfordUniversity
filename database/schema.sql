USE ashford_university;

CREATE TABLE User (
    user_ID INT PRIMARY KEY,
    first_Name VARCHAR(50) NOT NULL,
    middle_Name VARCHAR(50) DEFAULT NULL,
    last_Name VARCHAR(50) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    DOB DATE NOT NULL,
    street VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    zip_Code VARCHAR(10) NOT NULL,
    user_Type ENUM('Student', 'Faculty', 'Admin', 'StatStaff') NOT NULL
);

CREATE TABLE Login (
    user_ID INT PRIMARY KEY,
    user_Email VARCHAR(255) NOT NULL UNIQUE,
    user_Password VARCHAR(255) NOT NULL,
    no_Of_Tries INT NOT NULL DEFAULT 0,
    lock_var BOOLEAN NOT NULL DEFAULT FALSE,
    user_Type ENUM('Student', 'Faculty', 'Admin', 'StatStaff') NOT NULL,
    FOREIGN KEY (user_ID) REFERENCES User(user_ID)
);

-- Administrator role record. Every admin portal page reads security_Level
-- to decide between full access and read-only access.
CREATE TABLE Admin (
    user_ID INT PRIMARY KEY,
    security_Level ENUM('Full-Access', 'Read-Only') NOT NULL DEFAULT 'Read-Only',
    FOREIGN KEY (user_ID) REFERENCES User(user_ID)
);

CREATE TABLE Building (
    Bldg_ID VARCHAR(8) PRIMARY KEY,
    Bldg_Name VARCHAR(255) NOT NULL,
    building_Usage VARCHAR(255) NOT NULL
);

CREATE TABLE Room (
    room_ID INT PRIMARY KEY,
    Bldg_ID VARCHAR(8) NOT NULL,
    room_Number VARCHAR(20) NOT NULL,
    room_Type ENUM('Lecture', 'Office', 'Lab') NOT NULL,
    FOREIGN KEY (Bldg_ID) REFERENCES Building(Bldg_ID)
);

CREATE TABLE Lecture (
    lecture_ID INT PRIMARY KEY,
    avail_Seats INT NOT NULL,
    FOREIGN KEY (lecture_ID) REFERENCES Room(room_ID)
);

CREATE TABLE Office (
    office_ID INT PRIMARY KEY,
    No_Of_Desks INT NOT NULL,
    FOREIGN KEY (office_ID) REFERENCES Room(room_ID)
);

CREATE TABLE Lab (
    lab_ID INT PRIMARY KEY,
    no_Of_Workstations INT NOT NULL,
    FOREIGN KEY (lab_ID) REFERENCES Room(room_ID)
);

CREATE TABLE Faculty (
    faculty_ID INT PRIMARY KEY,
    office_ID INT NOT NULL,
    Specialty VARCHAR(100) NOT NULL,
    faculty_Rank VARCHAR(50) NOT NULL,
    faculty_Type ENUM('Full-Time', 'Part-Time') NOT NULL,
    no_of_Classes INT NOT NULL DEFAULT 0,
    FOREIGN KEY (faculty_ID) REFERENCES User(user_ID),
    FOREIGN KEY (office_ID) REFERENCES Office(office_ID)
);

CREATE TABLE Full_Time_Faculty (
    faculty_ID INT PRIMARY KEY,
    no_of_Classes INT NOT NULL DEFAULT 0,
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID)
);

CREATE TABLE Part_Time_Faculty (
    faculty_ID INT PRIMARY KEY,
    no_of_Classes INT NOT NULL DEFAULT 0,
    DOA DATE NOT NULL,
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID)
);

CREATE TABLE Department (
    dept_ID INT PRIMARY KEY,
    dept_Name VARCHAR(100) NOT NULL,
    office_ID INT NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Phone_No VARCHAR(20) NOT NULL,
    chair_ID INT NOT NULL,
    dept_Manager VARCHAR(100) NOT NULL,
    FOREIGN KEY (office_ID) REFERENCES Office(office_ID),
    FOREIGN KEY (chair_ID) REFERENCES Faculty(faculty_ID)
);

CREATE TABLE Major (
    major_ID INT PRIMARY KEY,
    major_Name VARCHAR(100) NOT NULL,
    dept_ID INT NOT NULL,
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Minor (
    minor_ID INT PRIMARY KEY,
    minor_Name VARCHAR(100) NOT NULL,
    dept_ID INT NOT NULL,
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Student (
    student_ID INT PRIMARY KEY,
    major_ID INT NOT NULL,
    student_Year INT NOT NULL,
    student_Type ENUM('Undergraduate', 'Graduate') NOT NULL,
    FOREIGN KEY (student_ID) REFERENCES User(user_ID),
    FOREIGN KEY (major_ID) REFERENCES Major(major_ID)
);

CREATE TABLE Undergraduate (
    student_ID INT PRIMARY KEY,
    dept_ID INT NOT NULL,
    undergraduate_student_Type ENUM('Full-Time', 'Part-Time') NOT NULL,
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Full_Time_Undergraduate (
    student_ID INT PRIMARY KEY,
    Status VARCHAR(50) NOT NULL,
    min_Credits INT NOT NULL,
    max_Credits INT NOT NULL,
    credits_Earned INT NOT NULL DEFAULT 0,
    FOREIGN KEY (student_ID) REFERENCES Undergraduate(student_ID)
);

CREATE TABLE Part_Time_Undergraduate (
    student_ID INT PRIMARY KEY,
    Status VARCHAR(50) NOT NULL,
    min_Credits INT NOT NULL,
    max_Credits INT NOT NULL,
    credits_Earned INT NOT NULL DEFAULT 0,
    FOREIGN KEY (student_ID) REFERENCES Undergraduate(student_ID)
);

CREATE TABLE Graduate_Student (
    student_ID INT PRIMARY KEY,
    dept_ID INT NOT NULL,
    Program VARCHAR(100) NOT NULL,
    graduate_Student_Type ENUM('Full-Time', 'Part-Time') NOT NULL,
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Full_Time_Graduate (
    student_ID INT PRIMARY KEY,
    year INT NOT NULL,
    credits_Earned INT NOT NULL DEFAULT 0,
    Thesis VARCHAR(255),
    FOREIGN KEY (student_ID) REFERENCES Graduate_Student(student_ID)
);

CREATE TABLE Part_Time_Graduate (
    student_ID INT PRIMARY KEY,
    FOREIGN KEY (student_ID) REFERENCES Graduate_Student(student_ID)
);

CREATE TABLE Hold (
    hold_ID INT PRIMARY KEY,
    hold_Type VARCHAR(100) NOT NULL
);

CREATE TABLE Course (
    course_ID INT PRIMARY KEY,
    dept_ID INT NOT NULL,
    course_Name VARCHAR(255) NOT NULL,
    course_Credits INT NOT NULL,
    course_DSC VARCHAR(500) NOT NULL,
    course_Type ENUM('Undergraduate', 'Graduate') NOT NULL,
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Day (
    day_ID INT PRIMARY KEY,
    day_Of_Week ENUM(
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    ) NOT NULL
);

CREATE TABLE Period (
    period_ID INT PRIMARY KEY,
    start_Time TIME NOT NULL,
    end_Time TIME NOT NULL
);

CREATE TABLE Time_Slot (
    time_Slot_ID INT PRIMARY KEY
);

CREATE TABLE Semester (
    semester_ID INT PRIMARY KEY,
    semester_Name VARCHAR(50) NOT NULL,
    semester_Start DATE DEFAULT NULL,
    registration_Start DATE DEFAULT NULL,
    registration_End DATE DEFAULT NULL,
    drop_Deadline DATE DEFAULT NULL,
    grade_Deadline DATETIME DEFAULT NULL
);

CREATE TABLE Course_Section (
    CRN INT PRIMARY KEY,
    course_ID INT NOT NULL,
    section_No INT NOT NULL,
    faculty_ID INT NOT NULL,
    time_Slot_ID INT NOT NULL,
    lecture_ID INT,
    lab_ID INT,
    semester_ID INT NOT NULL,
    available_Seats INT NOT NULL,
    is_Cancelled TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    FOREIGN KEY (lecture_ID) REFERENCES Lecture(lecture_ID),
    FOREIGN KEY (lab_ID) REFERENCES Lab(lab_ID),
    FOREIGN KEY (time_Slot_ID) REFERENCES Time_Slot(time_Slot_ID),
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID)
);

CREATE TABLE Advisor (
    faculty_ID INT NOT NULL,
    student_ID INT NOT NULL,
    date_Of_Appnt DATE NOT NULL,
    PRIMARY KEY (faculty_ID, student_ID),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID)
);

CREATE TABLE Student_Hold (
    hold_ID INT NOT NULL,
    student_ID INT NOT NULL,
    hold_Date DATE NOT NULL,
    PRIMARY KEY (student_ID, hold_ID),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (hold_ID) REFERENCES Hold(hold_ID)
);

CREATE TABLE Course_Prerequisite (
    course_ID INT NOT NULL,
    prerequisite_course_ID INT NOT NULL,
    min_Grade_Req VARCHAR(5) NOT NULL,
    PRIMARY KEY (course_ID, prerequisite_course_ID),
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID),
    FOREIGN KEY (prerequisite_course_ID) REFERENCES Course(course_ID)
);

CREATE TABLE Enrollment (
    student_ID INT NOT NULL,
    CRN INT NOT NULL,
    semester_ID INT NOT NULL,
    Grade VARCHAR(5),
    PRIMARY KEY (student_ID, CRN),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (CRN) REFERENCES Course_Section(CRN),
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID)
);

CREATE TABLE Attendance (
    student_ID INT NOT NULL,
    CRN INT NOT NULL,
    course_ID INT NOT NULL,
    attendance_Date DATE NOT NULL,
    Present_Absent ENUM('Present', 'Absent') NOT NULL,
    PRIMARY KEY (student_ID, CRN, attendance_Date),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (CRN) REFERENCES Course_Section(CRN),
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID)
);

CREATE TABLE Student_History (
    student_ID INT NOT NULL,
    CRN INT NOT NULL,
    course_ID INT NOT NULL,
    semester_ID INT NOT NULL,
    Grade VARCHAR(5),
    PRIMARY KEY (student_ID, CRN),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (CRN) REFERENCES Course_Section(CRN),
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID),
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID)
);

CREATE TABLE Faculty_History (
    faculty_ID INT NOT NULL,
    CRN INT NOT NULL,
    course_ID INT NOT NULL,
    semester_ID INT NOT NULL,
    PRIMARY KEY (faculty_ID, CRN),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    FOREIGN KEY (CRN) REFERENCES Course_Section(CRN),
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID),
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID)
);

CREATE TABLE Faculty_Department (
    faculty_ID INT NOT NULL,
    dept_ID INT NOT NULL,
    percent_Time DECIMAL(5,2) NOT NULL,
    date_Of_Appointment DATE NOT NULL,
    PRIMARY KEY (faculty_ID, dept_ID),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Student_Major (
    student_ID INT NOT NULL,
    major_ID INT NOT NULL,
    date_Of_Choice DATE NOT NULL,
    PRIMARY KEY (student_ID, major_ID),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (major_ID) REFERENCES Major(major_ID)
);

CREATE TABLE Student_Minor (
    student_ID INT NOT NULL,
    minor_ID INT NOT NULL,
    date_Of_Choice DATE NOT NULL,
    PRIMARY KEY (student_ID, minor_ID),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (minor_ID) REFERENCES Minor(minor_ID)
);

CREATE TABLE Time_Slot_Day (
    time_Slot_ID INT NOT NULL,
    day_ID INT NOT NULL,
    PRIMARY KEY (time_Slot_ID, day_ID),
    FOREIGN KEY (time_Slot_ID) REFERENCES Time_Slot(time_Slot_ID),
    FOREIGN KEY (day_ID) REFERENCES Day(day_ID)
);

CREATE TABLE Time_Slot_Period (
    time_Slot_ID INT NOT NULL,
    period_ID INT NOT NULL,
    PRIMARY KEY (time_Slot_ID, period_ID),
    FOREIGN KEY (time_Slot_ID) REFERENCES Time_Slot(time_Slot_ID),
    FOREIGN KEY (period_ID) REFERENCES Period(period_ID)
);

-- Statistics department staff (read-only reporting role).
CREATE TABLE StatStaff (
    user_ID INT PRIMARY KEY,
    employment_Type ENUM('Full-Time', 'Part-Time') NOT NULL,
    access_Level VARCHAR(20) NOT NULL DEFAULT 'Read-Only',
    FOREIGN KEY (user_ID) REFERENCES User(user_ID)
);

-- Cancellation history for sections (enrollment records are preserved).
CREATE TABLE Section_Cancellation (
    cancellation_ID INT AUTO_INCREMENT PRIMARY KEY,
    CRN INT NOT NULL,
    enrolled_Count INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    cancelled_By_User_ID INT NULL,
    created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CRN) REFERENCES Course_Section(CRN),
    FOREIGN KEY (cancelled_By_User_ID) REFERENCES User(user_ID)
);

-- Administrator-approved grade submission exceptions.
CREATE TABLE Grade_Exception (
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

-- One schedule record per semester. Course assignments stay in
-- Course_Section; this table tracks schedule status, the optimistic
-- concurrency version, and the audit trail of schedule-level changes.
CREATE TABLE Master_Schedule (
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

-- Degree requirement definitions per major.
CREATE TABLE Major_Requirement (
    major_ID INT NOT NULL,
    course_ID INT NOT NULL,
    min_Grade_Req VARCHAR(5) NOT NULL,
    PRIMARY KEY (major_ID, course_ID),
    FOREIGN KEY (major_ID) REFERENCES Major(major_ID),
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID)
);

-- Degree requirement definitions per minor.
CREATE TABLE Minor_Requirement (
    minor_ID INT NOT NULL,
    course_ID INT NOT NULL,
    min_Grade_Req VARCHAR(5) NOT NULL,
    PRIMARY KEY (minor_ID, course_ID),
    FOREIGN KEY (minor_ID) REFERENCES Minor(minor_ID),
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID)
);

-- General audit trail for portal actions.
CREATE TABLE Audit_Log (
    log_ID INT AUTO_INCREMENT PRIMARY KEY,
    actor_User_ID INT NULL,
    action VARCHAR(50) NOT NULL,
    entity_Type VARCHAR(100) NOT NULL,
    entity_ID VARCHAR(50) NULL,
    details TEXT NULL,
    ip_Address VARCHAR(45) NULL,
    created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actor_User_ID) REFERENCES User(user_ID)
);

-- Per-field change history for user records edited through the admin portal.
CREATE TABLE User_Update_Log (
    update_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_ID INT NOT NULL,
    changed_By_User_ID INT NULL,
    changed_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    changed_Field VARCHAR(100) NOT NULL,
    old_Value TEXT NULL,
    new_Value TEXT NULL,
    user_Request_Reference VARCHAR(255) NULL,
    FOREIGN KEY (user_ID) REFERENCES User(user_ID),
    FOREIGN KEY (changed_By_User_ID) REFERENCES User(user_ID)
);

-- Password reset tokens (only hashes are stored).
CREATE TABLE Password_Reset (
    reset_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_ID INT NOT NULL,
    token_Hash CHAR(64) NOT NULL,
    expires_At DATETIME NOT NULL,
    used_At DATETIME NULL,
    requested_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_password_reset_token (token_Hash),
    FOREIGN KEY (user_ID) REFERENCES User(user_ID)
);

-- Computed remaining seats per section: room capacity minus current enrollment.
CREATE VIEW v_section_seats AS
SELECT
    cs.CRN,
    cs.semester_ID,
    COALESCE(l.avail_Seats, b.no_Of_Workstations, 0) AS capacity,
    (SELECT COUNT(*) FROM Enrollment e WHERE e.CRN = cs.CRN) AS enrolled,
    GREATEST(
        COALESCE(l.avail_Seats, b.no_Of_Workstations, 0)
        - (SELECT COUNT(*) FROM Enrollment e WHERE e.CRN = cs.CRN),
        0
    ) AS seats_Remaining
FROM Course_Section cs
LEFT JOIN Lecture l ON l.lecture_ID = cs.lecture_ID
LEFT JOIN Lab b ON b.lab_ID = cs.lab_ID;

-- -----------------------------------------------------
-- Password history: prevents reuse of previous passwords
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS Password_History (
    history_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_ID INT NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    changed_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_ID) REFERENCES User(user_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Indexes for registration/roster query performance
-- -----------------------------------------------------
CREATE INDEX idx_enrollment_student_semester ON Enrollment(student_ID, semester_ID);
CREATE INDEX idx_section_semester_faculty ON Course_Section(semester_ID, faculty_ID);
CREATE INDEX idx_section_cancelled ON Course_Section(is_Cancelled);

-- -----------------------------------------------------
-- Notification / email log (faculty -> student, admin -> user)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS Notification (
    notification_ID INT AUTO_INCREMENT PRIMARY KEY,
    sender_user_ID INT NOT NULL,
    recipient_user_ID INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    sent_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_At TIMESTAMP NULL,
    FOREIGN KEY (sender_user_ID) REFERENCES User(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (recipient_user_ID) REFERENCES User(user_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Collective statistics (anonymous aggregates for StatStaff)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS Stat_Aggregate (
    aggregate_ID INT AUTO_INCREMENT PRIMARY KEY,
    semester_ID INT NOT NULL,
    metric_Name VARCHAR(100) NOT NULL,
    metric_Value INT NOT NULL,
    computed_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Column-level sensitivity metadata (restricted personal fields)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS System_Field_Restriction (
    restriction_ID INT AUTO_INCREMENT PRIMARY KEY,
    table_Name VARCHAR(64) NOT NULL,
    column_Name VARCHAR(64) NOT NULL,
    restriction_Level ENUM('identifiable','unsafe','restricted') NOT NULL,
    editable_By_Role ENUM('Student','Faculty','Admin','StatStaff','None') DEFAULT 'None',
    UNIQUE KEY uq_field_restriction (table_Name, column_Name)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- User: add active/deactivation flag
-- -----------------------------------------------------
ALTER TABLE User
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER user_Type;

-- -----------------------------------------------------
-- Admin: add employment status (Full-Time required per SRS)
-- -----------------------------------------------------
ALTER TABLE Admin
    ADD COLUMN employment_Status ENUM('Full-Time','Part-Time') NOT NULL DEFAULT 'Full-Time' AFTER security_Level;

-- -----------------------------------------------------
-- StatStaff: constrain access_Level to valid values
-- -----------------------------------------------------
ALTER TABLE StatStaff
    MODIFY COLUMN access_Level ENUM('Read-Only','Full-Access') NOT NULL DEFAULT 'Read-Only';

-- -----------------------------------------------------
-- Seed restricted-field metadata
-- -----------------------------------------------------
INSERT IGNORE INTO System_Field_Restriction (table_Name, column_Name, restriction_Level, editable_By_Role) VALUES
('User', 'user_ID', 'identifiable', 'None'),
('User', 'gender', 'identifiable', 'None'),
('User', 'DOB', 'identifiable', 'None'),
('User', 'street', 'unsafe', 'Student'),
('User', 'city', 'unsafe', 'Student'),
('User', 'state', 'unsafe', 'Student'),
('User', 'zip_Code', 'unsafe', 'Student'),
('User', 'user_Type', 'restricted', 'None'),
('User', 'user_Email', 'identifiable', 'None'),
('User', 'first_Name', 'identifiable', 'None'),
('User', 'last_Name', 'identifiable', 'None'),
('User', 'middle_Name', 'identifiable', 'None');

-- -----------------------------------------------------
-- Trigger: Faculty_Department max 3 departments per faculty
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS trg_faculty_max_departments;
DELIMITER //
CREATE TRIGGER trg_faculty_max_departments
BEFORE INSERT ON Faculty_Department
FOR EACH ROW
BEGIN
    DECLARE dept_count INT;
    SELECT COUNT(*) INTO dept_count
    FROM Faculty_Department
    WHERE faculty_ID = NEW.faculty_ID;
    IF dept_count >= 3 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Faculty may belong to at most 3 departments.';
    END IF;
END//
DELIMITER ;

-- -----------------------------------------------------
-- Trigger: block grade updates after semester grade deadline
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS trg_enrollment_grade_deadline;
DELIMITER //
CREATE TRIGGER trg_enrollment_grade_deadline
BEFORE UPDATE ON Enrollment
FOR EACH ROW
BEGIN
    DECLARE deadline DATETIME;
    IF NEW.Grade IS NOT NULL AND NEW.Grade <> OLD.Grade THEN
        SELECT s.grade_Deadline INTO deadline
        FROM Semester s WHERE s.semester_ID = NEW.semester_ID;
        IF deadline IS NOT NULL AND NOW() > deadline THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Grade submission deadline has passed for this semester.';
        END IF;
    END IF;
END//
DELIMITER ;

-- -----------------------------------------------------
-- Trigger: restrict attendance recording to current day
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS trg_attendance_current_day;
DELIMITER //
CREATE TRIGGER trg_attendance_current_day
BEFORE INSERT ON Attendance
FOR EACH ROW
BEGIN
    IF NEW.attendance_Date <> CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Attendance can only be recorded for the current day.';
    END IF;
END//
DELIMITER ;


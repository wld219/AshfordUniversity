-- Ashford SIS Database Schema
-- Run against MySQL 8.0+ (XAMPP) on port 3300

CREATE DATABASE IF NOT EXISTS ashford_sis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ashford_sis;

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Core identity
-- -----------------------------------------------------
CREATE TABLE User (
    user_ID INT AUTO_INCREMENT PRIMARY KEY,
    first_Name VARCHAR(50) NOT NULL,
    middle_Name VARCHAR(50) DEFAULT NULL,
    last_Name VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    user_Type ENUM('Student','Faculty','Admin','StatStaff') NOT NULL,
    created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE Login (
    user_ID INT PRIMARY KEY,
    user_Email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    no_Of_Tries INT NOT NULL DEFAULT 0,
    lock_var BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (user_ID) REFERENCES User(user_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Password_History (
    history_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_ID INT NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    changed_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_ID) REFERENCES User(user_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Academic structure
-- -----------------------------------------------------
CREATE TABLE Department (
    dept_ID INT AUTO_INCREMENT PRIMARY KEY,
    dept_Name VARCHAR(100) NOT NULL,
    office_ID INT NULL,
    Email VARCHAR(255) NULL,
    Phone_No VARCHAR(20) NULL,
    dept_Manager VARCHAR(100) NULL
) ENGINE=InnoDB;

CREATE TABLE Major (
    major_ID INT AUTO_INCREMENT PRIMARY KEY,
    major_Name VARCHAR(100) NOT NULL,
    dept_ID INT NOT NULL,
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
) ENGINE=InnoDB;

CREATE TABLE Minor (
    minor_ID INT AUTO_INCREMENT PRIMARY KEY,
    minor_Name VARCHAR(100) NOT NULL,
    dept_ID INT NOT NULL,
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
) ENGINE=InnoDB;

CREATE TABLE Major_Requirement (
    major_ID INT NOT NULL,
    course_ID INT NOT NULL,
    min_Grade_Req VARCHAR(5) NOT NULL,
    PRIMARY KEY (major_ID, course_ID),
    FOREIGN KEY (major_ID) REFERENCES Major(major_ID) ON DELETE CASCADE,
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID)
) ENGINE=InnoDB;

CREATE TABLE Minor_Requirement (
    minor_ID INT NOT NULL,
    course_ID INT NOT NULL,
    min_Grade_Req VARCHAR(5) NOT NULL,
    PRIMARY KEY (minor_ID, course_ID),
    FOREIGN KEY (minor_ID) REFERENCES Minor(minor_ID) ON DELETE CASCADE,
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Students (mutually exclusive level/status via ENUM + trigger)
-- -----------------------------------------------------
CREATE TABLE Student (
    student_ID INT PRIMARY KEY,
    student_Type ENUM('Undergraduate','Graduate') NOT NULL,
    enrollment_Status ENUM('Full-Time','Part-Time') NOT NULL,
    student_Year INT NOT NULL,
    major_ID INT NULL COMMENT 'primary declared major',
    FOREIGN KEY (student_ID) REFERENCES User(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (major_ID) REFERENCES Major(major_ID)
) ENGINE=InnoDB;

CREATE TABLE Student_Major (
    student_ID INT NOT NULL,
    major_ID INT NOT NULL,
    date_Of_Choice DATE NOT NULL,
    PRIMARY KEY (student_ID, major_ID),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID) ON DELETE CASCADE,
    FOREIGN KEY (major_ID) REFERENCES Major(major_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Student_Minor (
    student_ID INT NOT NULL,
    minor_ID INT NOT NULL,
    date_Of_Choice DATE NOT NULL,
    PRIMARY KEY (student_ID, minor_ID),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID) ON DELETE CASCADE,
    FOREIGN KEY (minor_ID) REFERENCES Minor(minor_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Courses & scheduling infrastructure
-- -----------------------------------------------------
CREATE TABLE Course (
    course_ID INT AUTO_INCREMENT PRIMARY KEY,
    dept_ID INT NOT NULL,
    course_Name VARCHAR(255) NOT NULL,
    course_Credits INT NOT NULL,
    course_Type ENUM('Undergraduate','Graduate') NOT NULL,
    course_DSC TEXT NOT NULL,
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
) ENGINE=InnoDB;

CREATE TABLE Course_Prerequisite (
    course_ID INT NOT NULL,
    prerequisite_course_ID INT NOT NULL,
    min_Grade_Req VARCHAR(5) NOT NULL,
    PRIMARY KEY (course_ID, prerequisite_course_ID),
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID) ON DELETE CASCADE,
    FOREIGN KEY (prerequisite_course_ID) REFERENCES Course(course_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Semester (
    semester_ID INT AUTO_INCREMENT PRIMARY KEY,
    semester_Name VARCHAR(50) NOT NULL,
    semester_Start DATE NULL,
    registration_Start DATETIME NULL,
    registration_End DATETIME NULL,
    drop_Deadline DATETIME NULL,
    grade_Deadline DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE Day (
    day_ID INT AUTO_INCREMENT PRIMARY KEY,
    day_Of_Week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Period (
    period_ID INT AUTO_INCREMENT PRIMARY KEY,
    start_Time TIME NOT NULL,
    end_Time TIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Time_Slot (
    time_Slot_ID INT AUTO_INCREMENT PRIMARY KEY
) ENGINE=InnoDB;

CREATE TABLE Time_Slot_Day (
    time_Slot_ID INT NOT NULL,
    day_ID INT NOT NULL,
    PRIMARY KEY (time_Slot_ID, day_ID),
    FOREIGN KEY (time_Slot_ID) REFERENCES Time_Slot(time_Slot_ID) ON DELETE CASCADE,
    FOREIGN KEY (day_ID) REFERENCES Day(day_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Time_Slot_Period (
    time_Slot_ID INT NOT NULL,
    period_ID INT NOT NULL,
    PRIMARY KEY (time_Slot_ID, period_ID),
    FOREIGN KEY (time_Slot_ID) REFERENCES Time_Slot(time_Slot_ID) ON DELETE CASCADE,
    FOREIGN KEY (period_ID) REFERENCES Period(period_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Faculty (
    faculty_ID INT PRIMARY KEY,
    office_ID INT NOT NULL,
    Specialty VARCHAR(100) NOT NULL,
    faculty_Rank VARCHAR(50) NOT NULL,
    faculty_Type ENUM('Full-Time','Part-Time') NOT NULL,
    no_of_Classes INT NOT NULL DEFAULT 0,
    FOREIGN KEY (faculty_ID) REFERENCES User(user_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Course_Section (
    CRN INT AUTO_INCREMENT PRIMARY KEY,
    course_ID INT NOT NULL,
    section_No INT NOT NULL,
    faculty_ID INT NOT NULL,
    time_Slot_ID INT NOT NULL,
    lecture_ID INT NULL,
    lab_ID INT NULL,
    semester_ID INT NOT NULL,
    available_Seats INT NOT NULL DEFAULT 0,
    is_Cancelled TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    FOREIGN KEY (time_Slot_ID) REFERENCES Time_Slot(time_Slot_ID),
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Enrollment & records
-- -----------------------------------------------------
CREATE TABLE Enrollment (
    student_ID INT NOT NULL,
    CRN INT NOT NULL,
    semester_ID INT NOT NULL,
    Grade VARCHAR(5) NULL,
    enrolled_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_ID, CRN),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID) ON DELETE CASCADE,
    FOREIGN KEY (CRN) REFERENCES Course_Section(CRN) ON DELETE CASCADE,
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID)
) ENGINE=InnoDB;

CREATE TABLE Advisor (
    faculty_ID INT NOT NULL,
    student_ID INT NOT NULL,
    date_Of_Appnt DATE NOT NULL,
    PRIMARY KEY (faculty_ID, student_ID),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID) ON DELETE CASCADE,
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Hold (
    hold_ID INT AUTO_INCREMENT PRIMARY KEY,
    hold_Type VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Student_Hold (
    hold_ID INT NOT NULL,
    student_ID INT NOT NULL,
    hold_Date DATE NOT NULL,
    PRIMARY KEY (hold_ID, student_ID),
    FOREIGN KEY (hold_ID) REFERENCES Hold(hold_ID) ON DELETE CASCADE,
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Student_History (
    student_ID INT NOT NULL,
    CRN INT NOT NULL,
    course_ID INT NOT NULL,
    semester_ID INT NOT NULL,
    Grade VARCHAR(5) NULL,
    PRIMARY KEY (student_ID, CRN),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID) ON DELETE CASCADE,
    FOREIGN KEY (CRN) REFERENCES Course_Section(CRN) ON DELETE CASCADE,
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID),
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID)
) ENGINE=InnoDB;

CREATE TABLE Audit_Log (
    log_ID INT AUTO_INCREMENT PRIMARY KEY,
    actor_User_ID INT NULL,
    action VARCHAR(50) NOT NULL,
    entity_Type VARCHAR(100) NOT NULL,
    entity_ID VARCHAR(50) NULL,
    details TEXT NULL,
    ip_Address VARCHAR(45) NULL,
    created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actor_User_ID) REFERENCES User(user_ID) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------
-- Trigger: mutually exclusive student attributes backstop
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS trg_student_exclusive_status;

DELIMITER //
CREATE TRIGGER trg_student_exclusive_status
BEFORE INSERT ON Student
FOR EACH ROW
BEGIN
    IF NEW.student_Type IS NULL OR NEW.enrollment_Status IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Student must be assigned exactly one level and one enrollment status.';
    END IF;
END//
DELIMITER ;

-- ======================================================================
-- Ashford University Portal - Database Constraints
--
-- Adds explicit CHECK constraints on top of database/schema.sql so every
-- domain and business rule visible in the current schema is enforced by
-- the engine, not only by the application.
--
-- Usage:
--   mysql -u root ashford_university < database/constraints.sql
-- ======================================================================

USE ashford_university;

-- -----------------------------------------------------
-- TABLE OF CONSTRAINTS
-- Table              Constraint                       Rule enforced
-- ---------          -----------                      -------------
-- User               chk_user_user_type               student/faculty/admin/statstaff
-- User               chk_user_gender                  Male/Female/Other
-- User               chk_user_is_active               0/1
-- Login              chk_login_user_type              student/faculty/admin/statstaff
-- Login              chk_login_no_of_tries            >= 0
-- Login              chk_login_lock_var               0/1
-- Admin              chk_admin_security_level         Full-Access/Read-Only
-- Admin              chk_admin_is_full_time           0/1
-- Admin              chk_admin_employment_status      Full-Time/Part-Time
-- StatStaff          chk_statstaff_access_level       Read-Only/Full-Access
-- StatStaff          chk_statstaff_employment_type    Full-Time/Part-Time
-- Room               chk_room_room_type               Lecture/Office/Lab
-- Lecture            chk_lecture_avail_seats          >= 0
-- Office             chk_office_no_of_desks           >= 0
-- Lab                chk_lab_no_of_workstations       >= 0
-- Faculty            chk_faculty_faculty_type         Full-Time/Part-Time
-- Faculty            chk_faculty_no_of_classes        >= 0
-- Full_Time_Faculty  chk_full_time_faculty_classes    >= 0
-- Part_Time_Faculty  chk_part_time_faculty_classes    >= 0
-- Student            chk_student_student_type         Undergraduate/Graduate
-- Student            chk_student_student_year         > 0
-- Undergraduate      chk_undergraduate_type           Full-Time/Part-Time
-- FT_Undergraduate   chk_ft_ug_min_max                min <= max, both >= 0
-- FT_Undergraduate   chk_ft_ug_credits_earned         >= 0
-- PT_Undergraduate   chk_pt_ug_min_max                min <= max, both >= 0
-- PT_Undergraduate   chk_pt_ug_credits_earned         >= 0
-- Graduate_Student   chk_graduate_type                Full-Time/Part-Time
-- FT_Graduate        chk_ft_grad_year                 > 0
-- FT_Graduate        chk_ft_grad_credits_earned       >= 0
-- Course             chk_course_type                  Undergraduate/Graduate
-- Course             chk_course_credits               > 0
-- Period             chk_period_start_before_end      start < end
-- Semester           chk_semester_registration_window registration_Start <= registration_End
-- Semester           chk_semester_drop_window         registration_End <= drop_Deadline
-- Semester           chk_semester_grade_deadline      drop_Deadline <= grade_Deadline
-- Course_Section     chk_section_available_seats      >= 0
-- Course_Section     chk_section_is_cancelled         0/1
-- Attendance         chk_attendance_present_absent    Present/Absent
-- Appointment        chk_appointment_slot_order       slot_end > slot_start
-- Appointment        chk_appointment_status           Pending/Accepted/Denied/Rescheduled/Completed/Cancelled
-- Master_Schedule    chk_master_schedule_status       Draft/Published
-- Master_Schedule    chk_master_schedule_version      >= 1
-- Section_Cancellation chk_cancellation_enrolled_count >= 0
-- Stat_Aggregate     chk_stat_aggregate_value         >= 0
-- Password_Reset     chk_password_reset_expiry        expires_At > requested_At
-- -----------------------------------------------------

-- Identity / role domains
ALTER TABLE User
    ADD CONSTRAINT chk_user_user_type CHECK (user_Type IN ('Student','Faculty','Admin','StatStaff')),
    ADD CONSTRAINT chk_user_gender CHECK (gender IN ('Male','Female','Other')),
    ADD CONSTRAINT chk_user_is_active CHECK (is_active IN (0,1));

ALTER TABLE Login
    ADD CONSTRAINT chk_login_user_type CHECK (user_Type IN ('Student','Faculty','Admin','StatStaff')),
    ADD CONSTRAINT chk_login_no_of_tries CHECK (no_Of_Tries >= 0),
    ADD CONSTRAINT chk_login_lock_var CHECK (lock_var IN (0,1));

ALTER TABLE Admin
    ADD CONSTRAINT chk_admin_security_level CHECK (security_Level IN ('Full-Access','Read-Only')),
    ADD CONSTRAINT chk_admin_is_full_time CHECK (is_full_time IN (0,1)),
    ADD CONSTRAINT chk_admin_employment_status CHECK (employment_Status IN ('Full-Time','Part-Time'));

ALTER TABLE StatStaff
    ADD CONSTRAINT chk_statstaff_access_level CHECK (access_Level IN ('Read-Only','Full-Access')),
    ADD CONSTRAINT chk_statstaff_employment_type CHECK (employment_Type IN ('Full-Time','Part-Time'));

-- Rooms and room subtypes
ALTER TABLE Room
    ADD CONSTRAINT chk_room_room_type CHECK (room_Type IN ('Lecture','Office','Lab'));

ALTER TABLE Lecture
    ADD CONSTRAINT chk_lecture_avail_seats CHECK (avail_Seats >= 0);

ALTER TABLE Office
    ADD CONSTRAINT chk_office_no_of_desks CHECK (No_Of_Desks >= 0);

ALTER TABLE Lab
    ADD CONSTRAINT chk_lab_no_of_workstations CHECK (no_Of_Workstations >= 0);

-- Faculty
ALTER TABLE Faculty
    ADD CONSTRAINT chk_faculty_faculty_type CHECK (faculty_Type IN ('Full-Time','Part-Time')),
    ADD CONSTRAINT chk_faculty_no_of_classes CHECK (no_of_Classes >= 0);

ALTER TABLE Full_Time_Faculty
    ADD CONSTRAINT chk_full_time_faculty_classes CHECK (no_of_Classes >= 0);

ALTER TABLE Part_Time_Faculty
    ADD CONSTRAINT chk_part_time_faculty_classes CHECK (no_of_Classes >= 0);

-- Students
ALTER TABLE Student
    ADD CONSTRAINT chk_student_student_type CHECK (student_Type IN ('Undergraduate','Graduate')),
    ADD CONSTRAINT chk_student_student_year CHECK (student_Year > 0);

ALTER TABLE Undergraduate
    ADD CONSTRAINT chk_undergraduate_type CHECK (undergraduate_student_Type IN ('Full-Time','Part-Time'));

ALTER TABLE Full_Time_Undergraduate
    ADD CONSTRAINT chk_ft_ug_min_max CHECK (min_Credits >= 0 AND max_Credits >= min_Credits),
    ADD CONSTRAINT chk_ft_ug_credits_earned CHECK (credits_Earned >= 0);

ALTER TABLE Part_Time_Undergraduate
    ADD CONSTRAINT chk_pt_ug_min_max CHECK (min_Credits >= 0 AND max_Credits >= min_Credits),
    ADD CONSTRAINT chk_pt_ug_credits_earned CHECK (credits_Earned >= 0);

ALTER TABLE Graduate_Student
    ADD CONSTRAINT chk_graduate_type CHECK (graduate_Student_Type IN ('Full-Time','Part-Time'));

ALTER TABLE Full_Time_Graduate
    ADD CONSTRAINT chk_ft_grad_year CHECK (year > 0),
    ADD CONSTRAINT chk_ft_grad_credits_earned CHECK (credits_Earned >= 0);

-- Courses and scheduling
ALTER TABLE Course
    ADD CONSTRAINT chk_course_type CHECK (course_Type IN ('Undergraduate','Graduate')),
    ADD CONSTRAINT chk_course_credits CHECK (course_Credits > 0);

ALTER TABLE Period
    ADD CONSTRAINT chk_period_start_before_end CHECK (start_Time < end_Time);

ALTER TABLE Semester
    ADD CONSTRAINT chk_semester_registration_window CHECK (
        registration_Start IS NULL OR registration_End IS NULL OR registration_Start <= registration_End
    ),
    ADD CONSTRAINT chk_semester_drop_window CHECK (
        registration_End IS NULL OR drop_Deadline IS NULL OR registration_End <= drop_Deadline
    ),
    ADD CONSTRAINT chk_semester_grade_deadline CHECK (
        drop_Deadline IS NULL OR grade_Deadline IS NULL OR drop_Deadline <= grade_Deadline
    );

ALTER TABLE Course_Section
    ADD CONSTRAINT chk_section_available_seats CHECK (available_Seats >= 0),
    ADD CONSTRAINT chk_section_is_cancelled CHECK (is_Cancelled IN (0,1));

ALTER TABLE Attendance
    ADD CONSTRAINT chk_attendance_present_absent CHECK (Present_Absent IN ('Present','Absent'));

ALTER TABLE Appointment
    ADD CONSTRAINT chk_appointment_slot_order CHECK (slot_end > slot_start),
    ADD CONSTRAINT chk_appointment_status CHECK (status IN ('Pending','Accepted','Denied','Rescheduled','Completed','Cancelled'));

ALTER TABLE Master_Schedule
    ADD CONSTRAINT chk_master_schedule_status CHECK (status IN ('Draft','Published')),
    ADD CONSTRAINT chk_master_schedule_version CHECK (version >= 1);

ALTER TABLE Section_Cancellation
    ADD CONSTRAINT chk_cancellation_enrolled_count CHECK (enrolled_Count >= 0);

ALTER TABLE Stat_Aggregate
    ADD CONSTRAINT chk_stat_aggregate_value CHECK (metric_Value >= 0);

ALTER TABLE Password_Reset
    ADD CONSTRAINT chk_password_reset_expiry CHECK (expires_At > requested_At);

SELECT 'Constraints applied.' AS status;

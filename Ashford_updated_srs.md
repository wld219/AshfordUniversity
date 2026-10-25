Ashford University Fullstack Information System
Software Requirements Specification

Version 1.0
Date: October 25, 2026

----------------------------------------
1. Introduction
----------------------------------------

1.1 Purpose

This Software Requirements Specification (SRS) document describes the functional and non-functional requirements for the Ashford University Fullstack Information System. The system is a role-based university registration portal serving students, faculty, administrators, and Statistics Department staff. This document provides a complete description of the system's purpose, features, constraints, and user requirements to guide development, testing, and deployment.

1.2 Scope

The Ashford University Portal supports course registration, class scheduling, grade management, transcript viewing, degree audits, academic advising, holds management, and administrative oversight. The system enforces academic policies including credit limits, prerequisite checking, time conflict prevention, seat availability, registration windows, and role-based access control. The system covers four semesters of focus: Fall 2026 and Spring 2027 for student and faculty schedules, with administrative access limited to these terms for schedule viewing and searching.

1.3 Definitions, Acronyms, and Abbreviations

CRN: Course Reference Number, a unique identifier for a course section.
SRS: Software Requirements Specification.
UG: Undergraduate.
PG: Graduate.
CRUD: Create, Read, Update, Delete.
CSRF: Cross-Site Request Forgery.
PDO: PHP Data Objects, a database access layer in PHP.

1.4 References

- Ashford University academic catalog and course requirements documentation.
- University academic calendar for Fall 2026 and Spring 2027 registration windows.
- University security and privacy policies for student records.

1.5 Overview of the Remainder of this Document

Section 2 provides the overall description including product perspective, user classes, and operating environment. Section 3 details the specific requirements for each user role: Student, Faculty, Administrator, and Statistics Department staff. Section 4 contains the use case tables describing actor interactions, initial conditions, basic flows, and exit conditions.

----------------------------------------
2. Overall Description
----------------------------------------

2.1 Product Perspective

The Ashford University Portal is a standalone web application built with PHP 8, procedural PDO, MySQL or MariaDB, HTML, CSS, and vanilla JavaScript. The application uses a role-based access model with four portals: Student, Faculty, Administrator, and Statistics Department staff. The system includes a public-facing website for unauthenticated visitors and authenticated portals for each user role. PHPMailer is bundled for email functionality. The system is deployable on Apache with PHP and MySQL, on AWS, or on shared hosting such as Infinity Free.

2.2 Product Functions

The system provides the following major functions:

- User authentication and session management with password hashing and CSRF protection.
- Password reset flows with expiring, single-use tokens.
- Role-based dashboard redirection after login.
- Course catalog browsing by department, major, minor, and prerequisites.
- Master schedule viewing and searching by department, course name, faculty, time slot, building or room, and available seats.
- Student course registration and dropping within a four-week window.
- Credit limit enforcement (16 credits full-time, 8 credits part-time).
- Prerequisite and time conflict checking.
- Seat availability locking using SELECT FOR UPDATE.
- Grade assignment and viewing by faculty and administrators.
- Attendance recording and viewing by faculty.
- Degree audit and unofficial transcript viewing.
- Hold management and viewing.
- Advisor assignment and viewing.
- User creation, deactivation, and password changes by administrators.
- Department, course, major, minor, and prerequisite creation and updates by administrators.
- Master schedule creation and updates by administrators.
- Anonymous statistics reporting for Statistics Department staff.

2.3 User Classes and Characteristics

Student: Enrolled in undergraduate or graduate programs, either full-time or part-time. Students can register for and drop courses, view their own academic records, and manage their majors and minors within defined limits.

Faculty: Assigned to up to three departments. Faculty can view their own schedules and rosters, record attendance, assign grades, view advisee information, and send student notifications.

Administrator: Has Full-Access or Read-Only security clearance. Administrators are full-time staff who manage users, academics, sections, semesters, advisors, holds, and grade exceptions.

Statistics Department staff: View-only access to anonymous aggregate statistics and reports. Cannot view personally identifying information.

2.4 Operating Environment

- Server: Apache web server or built-in PHP server.
- Language: PHP 8.0 or newer with pdo_mysql extension.
- Database: MySQL or MariaDB with utf8mb4 character set.
- Client: Modern web browser with cookies and JavaScript enabled (JavaScript is progressive enhancement; core functions work without it).
- Deployment targets: Localhost (XAMPP or built-in server), AWS Lightsail or EC2 with LAMP stack, Infinity Free shared hosting.

2.5 Design and Implementation Constraints

- All SQL queries must use prepared statements.
- All output must be escaped with the e function to prevent cross-site scripting.
- Passwords must be hashed with password_hash and verified with password_verify.
- Sessions must regenerate on login.
- Cookies must be HttpOnly and SameSite Lax, with Secure over HTTPS.
- State-changing forms require a CSRF token.
- Password reset tokens must be random, hashed at rest, expiring, and single-use.
- The .env file is the only configuration file and must not be committed to version control.
- No sample data ships with the application; all university content is created through the administrator portal or installer.

2.6 Assumptions and Dependencies

- The database schema and migrations are imported before first use.
- The installer creates the first administrator account.
- Semester dates and registration windows are configured through the admin portal.
- Statistics reports use aggregate queries only by design.
- The system supports Fall 2026 and Spring 2027 semesters for student and faculty schedule viewing and searching.

----------------------------------------
3. Specific Requirements
----------------------------------------

3.1 External Interface Requirements

3.1.1 User Interfaces

The system provides web-based user interfaces for each role:

- Public landing page accessible without authentication.
- Login page for all authenticated roles.
- Student dashboard with navigation to registration, academics, advisement, and profile.
- Faculty dashboard with navigation to console, schedule, rosters, attendance, grades, and advisees.
- Administrator dashboard with navigation to student management, faculty management, academics, sections, semesters, advisors, holds, exceptions, and audit.
- Statistics Department dashboard with navigation to reports and catalog browsing.

3.1.2 Hardware Interfaces

The system runs on standard web server hardware with Apache, PHP 8, and MySQL or MariaDB. No specialized hardware is required.

3.1.3 Software Interfaces

- PHP 8.0 or newer with pdo_mysql extension.
- MySQL or MariaDB database server.
- PHPMailer library for email functionality (bundled in lib directory).
- Web browser on the client side.

3.1.4 Communications Interfaces

- HTTP for standard web traffic.
- HTTPS recommended for production deployment.
- SMTP for email delivery if configured.

----------------------------------------
3.2.1 Student User Requirements
----------------------------------------

3.2.1a Explicitly Required Specifications

The following capabilities must be provided to students:

1. Be either Undergraduate or Graduate.
2. Be either Full Time or Part Time.
3. Attempt to Login.
4. View Login Page.
5. View Home Pages.
6. View Their Own Personal Information.
7. View Advisor.
8. View Degree Audit.
9. View Semester Schedule for Fall 2026.
10. View Semester Schedule for Spring 2027.
11. View Holds.
12. View Semester Grades.
13. View Unofficial Transcript.
14. View Available Majors.
15. View Available Minors.
16. View Departments.
17. View Courses in the Departments.
18. View Course Prerequisites.
19. View Major Requirements.
20. View Minor Requirements.
21. View Master Schedule.
22. Search Master Schedule for Fall 2026.
23. Search Master Schedule for Spring 2027.
24. Search the Course Catalog by Course Name.
25. Search the Course Catalog by Day.
26. Search the Course Catalog by Department Name.
27. Search the Course Catalog by Faculty Name.
28. Search the Course Catalog by Period.
29. Search the Course Catalog by Room Number or Building.
30. Search a Course's Prerequisites.
31. Add Major.
32. Add Minor.
33. Add Course Section within a timeframe of 4 weeks.
34. Update Major.
35. Update Minor.
36. Update Account Password.
37. Update Personal Information.
38. Drop Course Section within a timeframe of 4 weeks.

3.2.1b Forbidden SRS

The following actions are prohibited for students:

1. Cannot register for graduate courses if undergraduate.
2. Cannot register for undergraduate courses if graduate.
3. Cannot be both graduate and undergraduate student.
4. Cannot be both full-time and part-time.
5. Cannot add course section with a hold.
6. Cannot re-add course section if student already has a passing grade.
7. Cannot add course section if prerequisites are not met.
8. Cannot add course section if there is a time conflict with another course.
9. Cannot add course section for a particular course more than once within semester.
10. Cannot add course section if course credits exceed 16 credit limits for full-time.
11. Cannot add course section if course credits exceed 8 credit limits for part-time.
12. Cannot add course section if no seats in section are available.
13. Cannot add course section for another student.
14. Cannot add a course section outside time window of 4 weeks.
15. Cannot drop a course section outside time window of 4 weeks.
16. Cannot drop from an unenrolled course section.
17. Cannot drop another student from a course section.
18. Cannot declare more than 2 majors and minors combined.
19. Cannot declare more than 2 majors.
20. Cannot declare more than 1 minor.
21. Cannot declare a minor without a major.
22. Cannot declare less than 1 major.
23. Cannot view course section roster.
24. Cannot view unassigned course sections.
25. Cannot view another student's personal information.
26. Cannot view another student's advisor.
27. Cannot view another student's degree audit.
28. Cannot view another student's holds.
29. Cannot view another student's semester grades.
30. Cannot view another student's semester schedules outside of their own account.
31. Cannot view another student's unofficial transcript.
32. Cannot view student semester schedules before Fall 2026 semester.
33. Cannot view student semester schedules after Spring 2027 semester.
34. Cannot change another student's major.
35. Cannot change another student's minor.
36. Cannot change another user's password.
37. Cannot change their new password to the existing password.
38. Cannot change their password without fulfilling character and length requirements.
39. Cannot update personal information that is unsafe to change.
40. Cannot update personal information that is identifiable.

----------------------------------------
3.2.2 Faculty User Requirements
----------------------------------------

3.2.2a Explicitly Required Specifications

The following capabilities must be provided to faculty:

1. Be either Full Time or Part Time.
2. Belong to up to 3 Departments.
3. Attempt to Login.
4. View Home Pages.
5. View Login Page.
6. View Their Own Personal Information.
7. View Advisees.
8. View a Student's Advisor.
9. View a Student's Degree Audit.
10. View a Student's Holds.
11. View a Student's Semester Grades.
12. View a Student's Semester Schedules.
13. View a Student's Unofficial Transcript.
14. View Their Own Semester Schedules.
15. View Available Majors.
16. View Available Minors.
17. View Major Requirements.
18. View Minor Requirements.
19. View Their Own Course Section Roster.
20. View Their Own Course Section Attendance.
21. View Semester Schedule for Fall 2026.
22. View Semester Schedule for Spring 2027.
23. View Master Schedule.
24. View All Advisors.
25. Search Master Schedule for Fall 2026.
26. Search Master Schedule for Spring 2027.
27. Search the Course Catalog.
28. Send Student Email or Notifications.
29. Update Account Password.
30. Update Personal Information.
31. Record Their Course Section Attendance.
32. Assign Students with Grades.

3.2.2b Forbidden SRS

The following actions are prohibited for faculty:

1. Cannot assign course section with grades outside their own course sections.
2. Cannot assign course section with grades outside period.
3. Cannot update their own course section attendance outside current day.
4. Cannot view personal information that is above faculty role.
5. Cannot view course section roster outside of the current semester.
6. Cannot view unassigned course sections.
7. Cannot change another user's password.
8. Cannot change their new password to the existing password.
9. Cannot change their password without fulfilling character and length requirements.
10. Cannot update personal information that is unsafe to change.
11. Cannot update personal information that is identifiable.

----------------------------------------
3.2.3 Admin User Requirements
----------------------------------------

3.2.3a Explicitly Required Specifications

The following capabilities must be provided to administrators:

1. Has a Security Level clearance (View-only or Update-enabled).
2. Be Full Time.
3. Attempt to Login.
4. View Login Page.
5. View Home Pages.
6. View Anyone's Semester Schedules (Fall 2026/Spring 2027 only).
7. View a Student's Advisors.
8. View a Student's Degree Audit.
9. View a Student's Holds.
10. View a Student's Semester Grades.
11. View a Student's Unofficial Transcript.
12. View Any Course Section's Course Section Roster.
13. View Any Course Section's Course Section Attendance.
14. View Available Majors.
15. View Available Minors.
16. View Major Requirements.
17. View Minor Requirements.
18. View Advisees or Any Other Student.
19. View All Advisors.
20. Search a Semester's Master Schedule (Fall 2026 or Spring 2027 only).
21. Search the Course Catalog.
22. Cancel or Delete Course Section within a time window.
23. Create Users and Passwords.
24. Create a New User.
25. Create a New Course Section (CRN, Course Name, Section Number, Faculty, Time Slot (day/period), Building/Room Number, Number of Available Seats).
26. Create or Update Departments.
27. Create or Update prerequisites.
28. Create a New Major.
29. Create a New Minor.
30. Create the Master Schedule.
31. Add a Student to Course Section.
32. Add a Student to Major.
33. Add a Student to Minor.
34. Add Student to Advisor.
35. Change a Student Grades.
36. Change Any Account Password.
37. Change a Student Major.
38. Change a Student Minor.
39. Delete a Student from Course Section.
40. Deactivate or Delete Users.
41. Update the Master Schedule.
42. Update anyone's Personal Information.
43. Update a Course's Catalog Information.
44. Remove or Reassign faculty from a course.
45. Assign Building Rooms.
46. Assign Time Slots.
47. Assign Available Seats.

3.2.3b Forbidden SRS

The following actions are prohibited for administrators:

1. Cannot view any semester schedule if requested semester is before Fall 2026.
2. Cannot view any semester schedule if requested semester is after Spring 2027.
3. Cannot search a semester's master schedule if requested semester is before Fall 2026.
4. Cannot search a semester's master schedule if requested semester is after Spring 2027.
5. Cannot add student to course section if student has a hold.
6. Cannot re-add student to course section if student already has a passing grade.
7. Cannot add student to course section if prerequisites are not met.
8. Cannot add student to course section if registered to another course with time conflict.
9. Cannot add student to course section more than once within a particular semester.
10. Cannot add student to course section if course credits exceed 16 credit limit for full-time.
11. Cannot add student to course section if course credits exceed 8 credit limit for part-time.
12. Cannot add student to course section if no seats in section are available.
13. Cannot add student to course section outside 4 week time window.
14. Cannot add student to advisor if student has an advisor.
15. Cannot add student to advisor if advisor does not share a department with student major.
16. Cannot declare a student to have more than 2 majors and minor declarations combined.
17. Cannot declare a student to have more than 1 minor.
18. Cannot declare a student to have 1 minor without a major.
19. Cannot declare a student to have less than 1 major.
20. Cannot declare a student to have more than 2 majors.
21. Cannot create a new course section record if there is time/date conflict with another section.
22. Cannot create a new course section record if there is faculty part-time/full-time status conflict.
23. Cannot create a new course section record if there is conflict with a faculty's department.
24. Cannot create a master schedule record if a faculty is not in related department.
25. Cannot create a master schedule record if a faculty has time conflicts.
26. Cannot create a master schedule record if a room has time conflicts.
27. Cannot create a master schedule record if a semester is ongoing or has passed.
28. Cannot assign course section with grades outside a specific faculty members own course sections.
29. Cannot assign a course section with grades for future semesters after Spring 2027.
30. Cannot change anyone's password to the existing password.
31. Cannot change anyone's password without fulfilling character and length requirements.
32. Cannot drop student from course section if student does not exist in the section.
33. Cannot update anyone's personal information that is unsafe to change or that is identifiable.

----------------------------------------
3.2.4 Stat-Dept.-Member Requirements
----------------------------------------

3.2.4a Explicitly Required Specifications

The following capabilities must be provided to Statistics Department staff:

1. Attempt to Login.
2. View Login Page.
3. View Their Own Personal Information.
4. View Collective Statistics.
5. Search a Semester's Master Schedule.
6. Search the Course Catalog.
7. Change Their Own Account Password.
8. Update Their Own Personal Information.

3.2.4b Forbidden SRS

The following actions are prohibited for Statistics Department staff:

1. Cannot view anyone else's personally identifying information anywhere.
2. Cannot view course section roster statistics outside of Fall 2026 and Spring 2027.
3. Cannot view unassigned faculty course sections.
4. Cannot change their password outside of their own account.
5. Cannot change their password to the existing password.
6. Cannot change their password without fulfilling character and length requirements.
7. Cannot update their personal information that is unsafe to change or that is identifiable.

----------------------------------------
4. Use Case Tables
----------------------------------------

4.1 Visitor

4.1.1 View Home Pages

Use Case: 4.1.1 View Home Pages
Actors: Visitor
Initial Condition:
1. Visitor is on the website.
Basic Flow:
1. Visitor navigates to the website URL.
2. System loads the Home Page.
3. Visitor views the Home Page contents.
Exit Condition:
1. Visitor leaves the website.
2. Web browser is closed.

----------------------------------------
4.2 Student

4.2.1 View Home Pages

Use Case: 4.2.1 View Home Pages
Actors: Student
Initial Condition:
1. Student logs in with valid credentials.
Basic Flow:
1. Student enters username and password.
2. System authenticates the student.
3. Student is redirected to the Home Page.
4. Student views personal information summary.
Exit Condition:
1. Student logs out.
2. Web browser is closed.

4.2.2 View Login Page

Use Case: 4.2.2 View Login Page
Actors: Student
Initial Condition:
1. Student is on the website.
Basic Flow:
1. Student clicks on Login or is redirected to login.
2. System loads the Login Page.
3. Student views login fields (username/password).
Exit Condition:
1. Student logs in successfully.
2. Student navigates away.
3. Browser is closed.

4.2.3 View Student Information

Use Case: 4.2.3 View Student Information
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Profile.
2. Inspect personal account information.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.4 View Schedule for Fall 2026 / Spring 2027

Use Case: 4.2.4 View Schedule for Fall 2026 / Spring 2027
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Navigate to Semester Schedule.
3. Select Fall 2026 or Spring 2027.
4. View registered courses.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.5 View Semester Grades

Use Case: 4.2.5 View Semester Grades
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Academics.
2. Navigate to Semester Grades.
3. View grades for courses.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.6 View Unofficial Transcript

Use Case: 4.2.6 View Unofficial Transcript
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Academics.
2. Navigate to Unofficial Transcript.
3. View transcript.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.7 View Degree Audit

Use Case: 4.2.7 View Degree Audit
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Academics.
2. Navigate to Degree Audit.
3. View degree progress.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.8 View Advisor Information

Use Case: 4.2.8 View Advisor Information
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Advisement Page.
2. View assigned advisor information.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.9 View Holds

Use Case: 4.2.9 View Holds
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Navigate to Holds.
3. View active holds.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.10 View Departments

Use Case: 4.2.10 View Departments
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Course Catalog.
2. Navigate to Departments.
3. View department list.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.11 View Courses by Department

Use Case: 4.2.11 View Courses by Department
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Course Catalog.
2. Select a department.
3. View available courses.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.12 View Majors by Department

Use Case: 4.2.12 View Majors by Department
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Course Catalog.
2. Select a department.
3. View available Majors.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.13 View Course Requirements for a Major

Use Case: 4.2.13 View Course Requirements for a Major
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Course Catalog.
2. Navigate to Programs.
3. Select a Major.
4. View course requirements.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.14 View Minors by Department

Use Case: 4.2.14 View Minors by Department
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Course Catalog.
2. Select a department.
3. View available Minors.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.15 View Course Requirements for a Minor

Use Case: 4.2.15 View Course Requirements for a Minor
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Course Catalog.
2. Navigate to Programs.
3. Select a Minor.
4. View course requirements.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.16 View Semester Master Schedule by Department

Use Case: 4.2.16 View Semester Master Schedule by Department
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Navigate to Master Schedule.
3. Search by Department.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.17 View Semester Master Schedule by Course Name

Use Case: 4.2.17 View Semester Master Schedule by Course Name
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Navigate to Master Schedule.
3. Search by Course Name.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.18 View Semester Master Schedule by Faculty Name

Use Case: 4.2.18 View Semester Master Schedule by Faculty Name
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Navigate to Master Schedule.
3. Search by Faculty Name.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.19 View Semester Master Schedule by Time Slot

Use Case: 4.2.19 View Semester Master Schedule by Time Slot
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Navigate to Master Schedule.
3. Search by Time Slot.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.20 View Semester Master Schedule by Building/Room Number

Use Case: 4.2.20 View Semester Master Schedule by Building/Room Number
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Navigate to Master Schedule.
3. Search by Building/Room Number.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.21 View Semester Master Schedule by Available Seats

Use Case: 4.2.21 View Semester Master Schedule by Available Seats
Actors: Student
Initial Condition:
1. User logs in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Navigate to Master Schedule.
3. Search by Available Seats.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.2.22 Login

Use Case: 4.2.22 Login
Actors: Student
Initial Condition:
1. User is on the Login Page
Basic Flow:
1. Student enters username and password.
2. System validates credentials.
3. If correct, system redirects to Student Home Page.
Exit Condition:
1. Student is logged in successfully.
2. Session ends when logging out.

4.2.23 Unsuccessful Login

Use Case: 4.2.23 Unsuccessful Login
Actors: Student
Initial Condition:
1. User is on the Login Page
Basic Flow:
1. Student enters username and password.
2. System validates the entered credentials.
3. Credentials do not match any existing account.
4. System prevents login and displays an error message: Invalid login credentials.
Exit Condition:
1. Student is unable to log in and closes browser.

4.2.24 Reset Password

Use Case: 4.2.24 Reset Password
Actors: Student
Initial Condition:
1. User is on the Login Page
Basic Flow:
1. Student clicks Forgot Password.
2. System requests student email/ID.
3. Student provides email/ID.
4. System sends reset instructions.
5. Student resets password successfully.
Exit Condition:
1. Student logs in with new password.

4.2.25 Unsuccessful Password Reset

Use Case: 4.2.25 Unsuccessful Password Reset
Actors: Student
Initial Condition:
1. User is on the Password Reset Page
Basic Flow:
1. Student selects Forgot Password.
2. System requests the registered email/ID.
3. Student enters an email/ID that is not found in the system.
4. System validates the input.
5. System detects invalid or unregistered email/ID.
6. System displays an error message: Email/ID not recognized. Please try again.
Exit Condition:
1. Password reset is not completed.
2. Student remains on the Password Reset Page.

4.2.26 Update Password

Use Case: 4.2.26 Update Password
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Profile/Account Settings.
2. Select Edit option.
3. Update address and/or password.
4. Save changes.
5. System confirms update.
Exit Condition:
1. Updated information is stored in student profile.

4.2.27 Unsuccessful Password Update - Using Existing Password

Use Case: 4.2.27 Unsuccessful Password Update - Using Existing Password
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Profile/Account Settings.
2. Select Edit option.
3. Student attempts to update the password but enters the same password as the current one.
4. System checks the new password against the existing password.
5. System detects that the new password matches the old password.
6. System displays an error message: New password cannot be the same as the current password.
Exit Condition:
1. Password is not updated.
2. Student remains on the Edit page to try again.

4.2.28 Unsuccessful Password Update - Not Meeting Requirements

Use Case: 4.2.28 Unsuccessful Password Update - Not Meeting Requirements
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Profile/Account Settings.
2. Select Edit option.
3. Student enters a new password that does not meet security requirements (e.g., too short, missing uppercase, missing number, etc.).
4. System validates the new password.
5. System detects invalid password format.
6. System displays an error message such as: Password does not meet security requirements. Password must include letters, numbers, and minimum length.
Exit Condition:
1. Password is not updated.
2. Student remains on the Edit page with validation message.

4.2.29 Personal Information Update

Use Case: 4.2.29 Personal Information Update
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Profile/Account Settings.
2. Select Edit Personal Information.
3. Student updates personal details (name, phone number, email, address, etc.).
4. Student submits or saves the changes.
5. System validates all provided information.
6. System updates the student profile.
Exit Condition:
1. Updated personal information is stored in the student profile.

4.2.30 Unsuccessful Personal Information Update

Use Case: 4.2.30 Unsuccessful Personal Information Update
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Profile/Account Settings.
2. Select Edit Personal Information.
3. Student enters information in restricted or unsafe fields (e.g., altering government ID numbers, system-generated IDs, sensitive system fields).
4. System validates the input.
5. System detects that the fields being updated are not allowed to be modified.
6. System displays an error message such as: These fields cannot be changed. Restricted information update not allowed.
Exit Condition:
1. Personal information is not updated.
2. Student remains on the Edit Personal Information page with error message displayed.

4.2.31 Add a Course-Section

Use Case: 4.2.31 Add a Course-Section
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Select Course Registration.
3. Choose semester.
4. Search for a course.
5. Select Add.
6. System checks prerequisites, time conflicts, credit limits, and seat availability.
7. If valid, course section is added.
Exit Condition:
1. Student's semester schedule updated.

4.2.32 Unsuccessful Add Course-Section - Student Has Holds

Use Case: 4.2.32 Unsuccessful Add Course-Section - Student Has Holds
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and has active holds.
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Student selects a course section to add.
3. System checks registration eligibility.
4. Active holds found.
Exit Condition:
1. System displays error message: Unable to register - active holds present.

4.2.33 Unsuccessful Add Course-Section - Undergraduate Adding

Use Case: 4.2.33 Unsuccessful Add Course-Section - Undergraduate Adding
Actors: Student
Initial Condition:
1. User is logged in with Undergraduate Credentials
Basic Flow:
1. Student selects a graduate-level course section.
2. System checks student type.
3. Undergraduate status detected.
Exit Condition:
1. System displays error message: Registration failed - course restricted to graduate students.

4.2.34 Unsuccessful Add Course-Section - Graduate Adding

Use Case: 4.2.34 Unsuccessful Add Course-Section - Graduate Adding
Actors: Student
Initial Condition:
1. User is logged in with Graduate Credentials
Basic Flow:
1. Student selects an undergraduate-level course section.
2. System checks student type.
3. Graduate status detected.
Exit Condition:
1. System displays error message: Registration failed - course restricted to undergraduate students.

4.2.35 Unsuccessful Add Course-Section - Exceeding Credit Limits

Use Case: 4.2.35 Unsuccessful Add Course-Section - Exceeding Credit Limits
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. Student attempts to add a course section.
2. System checks total registered credits.
3. Credits exceed allowed maximum (full-time > 16, part-time > 8).
Exit Condition:
1. System displays error message: Cannot register - credit limit exceeded.

4.2.36 Unsuccessful Add Course-Section - Already Taken Course

Use Case: 4.2.36 Unsuccessful Add Course-Section - Already Taken Course
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. Student selects a course already completed with a passing grade.
2. System checks academic history.
3. Duplicate enrollment detected.
Exit Condition:
1. System displays error message: Course already completed - cannot re-register.

4.2.37 Unsuccessful Add Course-Section - Missing Prerequisites

Use Case: 4.2.37 Unsuccessful Add Course-Section - Missing Prerequisites
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. Student selects a course requiring prerequisites not completed.
2. System checks prerequisites.
3. Missing requirements found.
Exit Condition:
1. System displays error message: Prerequisites not met - registration denied.

4.2.38 Unsuccessful Add Course-Section - Time Slot Conflict

Use Case: 4.2.38 Unsuccessful Add Course-Section - Time Slot Conflict
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. Student selects a course section.
2. System checks existing schedule.
3. Time overlap detected.
Exit Condition:
1. System displays error message: Schedule conflict - cannot register.

4.2.39 Unsuccessful Add Course-Section - No Available Seats

Use Case: 4.2.39 Unsuccessful Add Course-Section - No Available Seats
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. Student selects a course section.
2. System checks availability.
3. Section at capacity.
Exit Condition:
1. System displays error message: No seats available.

4.2.40 Unsuccessful Add Course-Section - Outside Time Window

Use Case: 4.2.40 Unsuccessful Add Course-Section - Outside Time Window
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. Student selects a course section to add.
2. System checks the current academic calendar and registration period.
3. System detects that the student is attempting to register after the allowed 4-week add period has ended.
4. Add action is denied.
Exit Condition:
1. System displays error message: Registration closed - add period has ended.

4.2.41 Drop a Course-Section

Use Case: 4.2.41 Drop a Course-Section
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Select Course Registration.
3. Choose course section to drop.
4. Confirm changes.
5. System removes the course from schedule.
Exit Condition:
1. Student's semester schedule updated.

4.2.42 Unsuccessful Drop Course - Outside Time Window

Use Case: 4.2.42 Unsuccessful Drop Course - Outside Time Window
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Select Course Registration.
3. Choose a course section to drop.
4. System checks if drop request is within allowed 4-week window.
5. System detects request is outside allowed time window.
Exit Condition:
1. System displays error message: Cannot drop course - drop period exceeded.
2. Student's schedule remains unchanged.

4.2.43 Unsuccessful Drop Course - Not Enrolled in Section

Use Case: 4.2.43 Unsuccessful Drop Course - Not Enrolled in Section
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Select Course Registration.
3. Choose a course section to drop.
4. System checks if student is enrolled in selected section.
5. System detects student is not enrolled in the selected section.
Exit Condition:
1. System displays error message: Cannot drop course - not enrolled in this section.
2. Student's schedule remains unchanged.

4.2.44 Register for Current Semester Courses

Use Case: 4.2.44 Register for Current Semester Courses
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Select Course Registration.
3. Choose current semester course sections to register.
4. Confirm registration.
5. System validates prerequisites, schedule conflicts, and seat availability.
6. System adds selected courses to student's schedule.
Exit Condition:
1. Schedule updated with successfully registered courses.

4.2.45 Unsuccessful Register for Current Semester

Use Case: 4.2.45 Unsuccessful Register for Current Semester
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Registration.
2. Select Course Registration.
3. Choose course sections to register.
4. System checks for prerequisites, schedule conflicts, and seat availability.
5. System detects constraint violation (e.g., missing prerequisite, time conflict, already passed course).
Exit Condition:
1. System displays error message describing the constraint.
2. Course is not added to student's schedule.

4.2.46 Add a Major

Use Case: 4.2.46 Add a Major
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Program Enrollment.
2. Select a Major.
3. Submit request to add.
4. System validates eligibility.
5. System assigns advisor if needed.
6. Save changes.
Exit Condition:
1. Major added to student profile.

4.2.47 Unsuccessful Add Major - Student Already Has a Major

Use Case: 4.2.47 Unsuccessful Add Major - Student Already Has a Major
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and already has a major.
Basic Flow:
1. Student attempts to add another major.
2. System checks enrollment record.
3. Existing major detected.
Exit Condition:
1. System displays error message: Student already has a major.

4.2.48 Unsuccessful Add Major - Trying to Add More Than 2

Use Case: 4.2.48 Unsuccessful Add Major - Trying to Add More Than 2
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and already has two majors.
Basic Flow:
1. Student attempts to add a third major.
2. System validates enrollment record.
3. Limit exceeded.
Exit Condition:
1. System displays error message: Cannot add more than 2 majors.

4.2.49 Unsuccessful Add Major - Would Exceed 2 Major/Minor

Use Case: 4.2.49 Unsuccessful Add Major - Would Exceed 2 Major/Minor
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and already has a combination of majors and minors that would reach the maximum allowed limit (2 total).
Basic Flow:
1. Student attempts to add a new major.
2. System checks enrollment record for current majors and minors.
3. System detects that adding the new major would exceed the combined limit of 2 majors/minors.
Exit Condition:
1. System displays error message: Cannot add major - would exceed combined limit of 2 majors/minors.

4.2.50 Drop a Major

Use Case: 4.2.50 Drop a Major
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and has more than one major.
Basic Flow:
1. From Student Home Page, navigate to Program Enrollment.
2. Select a Major to drop.
3. Submit request.
4. System validates that the student still has another major.
5. Save changes.
Exit Condition:
1. Major removed from student profile.

4.2.51 Unsuccessful Drop Major - Student Only Has One Major

Use Case: 4.2.51 Unsuccessful Drop Major - Student Only Has One Major
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and has only one major.
Basic Flow:
1. Student selects to drop their only major.
2. System validates enrollment record.
3. No major would remain.
Exit Condition:
1. System displays error message: Cannot drop - student must have at least one major.

4.2.52 Change Major

Use Case: 4.2.52 Change Major
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Program Enrollment.
2. Select Change Major option.
3. Choose a new major.
4. System verifies eligibility and assigns advisor if needed.
5. Save changes.
Exit Condition:
1. New major updated in student profile.

4.2.53 Unsuccessful Change Major - Undergraduate to Graduate

Use Case: 4.2.53 Unsuccessful Change Major - Undergraduate to Graduate
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and enrolled as an undergraduate.
Basic Flow:
1. From Student Home Page, navigate to Program Enrollment.
2. Select Change Major option.
3. Choose a graduate-level major.
4. System checks eligibility for graduate program.
5. System detects that undergraduate student cannot directly switch to graduate major.
Exit Condition:
1. System displays error message: Cannot change major - undergraduate students cannot switch directly to graduate programs.

4.2.54 Unsuccessful Change Major - Graduate to Undergraduate

Use Case: 4.2.54 Unsuccessful Change Major - Graduate to Undergraduate
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and enrolled as a graduate student.
Basic Flow:
1. From Student Home Page, navigate to Program Enrollment.
2. Select Change Major option.
3. Choose an undergraduate-level major.
4. System checks eligibility for undergraduate program.
5. System detects that graduate student cannot switch to undergraduate major.
Exit Condition:
1. System displays error message: Cannot change major - graduate students cannot switch to undergraduate programs.

4.2.55 Add a Minor

Use Case: 4.2.55 Add a Minor
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Program Enrollment.
2. Select a Minor.
3. Submit request to add.
4. System validates eligibility.
5. Save changes.
Exit Condition:
1. Minor added to student profile.

4.2.56 Unsuccessful Add Minor - Student Already Has a Minor

Use Case: 4.2.56 Unsuccessful Add Minor - Student Already Has a Minor
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and already has a minor.
Basic Flow:
1. Student attempts to add another minor.
2. System checks enrollment record.
3. Existing minor detected.
Exit Condition:
1. System displays error message: Student already has a minor.

4.2.57 Unsuccessful Add Minor - Trying to Add Same Minor Twice

Use Case: 4.2.57 Unsuccessful Add Minor - Trying to Add Same Minor Twice
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. Student attempts to add the same minor again.
2. System checks minor list.
3. Duplicate detected.
Exit Condition:
1. System displays error message: Duplicate minor - already added.

4.2.58 Unsuccessful Add Minor - No Major Declared

Use Case: 4.2.58 Unsuccessful Add Minor - No Major Declared
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and has not declared any major.
Basic Flow:
1. Student attempts to add a minor.
2. System checks if the student has a declared major.
3. System detects no major is declared.
Exit Condition:
1. System displays error message: Cannot add minor - major must be declared first.

4.2.59 Unsuccessful Add Minor - Would Exceed 2 Major/Minor

Use Case: 4.2.59 Unsuccessful Add Minor - Would Exceed 2 Major/Minor
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and already has a combination of majors and minors reaching the maximum allowed limit (2 total).
Basic Flow:
1. Student attempts to add a new minor.
2. System checks enrollment record for current majors and minors.
3. System detects that adding the new minor would exceed the combined limit of 2 majors/minors.
Exit Condition:
1. System displays error message: Cannot add minor - would exceed combined limit of 2 majors/minors.

4.2.60 Drop a Minor

Use Case: 4.2.60 Drop a Minor
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials and has at least one minor.
Basic Flow:
1. From Student Home Page, navigate to Program Enrollment.
2. Select a Minor to drop.
3. Submit request.
4. System validates eligibility.
5. Save changes.
Exit Condition:
1. Minor removed from student profile.

4.2.61 Unsuccessful Drop Minor - Student Has No Minor

Use Case: 4.2.61 Unsuccessful Drop Minor - Student Has No Minor
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials but has no minor.
Basic Flow:
1. Student attempts to drop a minor.
2. System validates enrollment record.
3. No minor found.
Exit Condition:
1. System displays error message: No minor to drop.

4.2.62 Change Minor

Use Case: 4.2.62 Change Minor
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Program Enrollment.
2. Select Change Minor option.
3. Choose new minor.
4. System validates eligibility.
5. Save changes.
Exit Condition:
1. New minor updated in student profile.

4.2.63 Unsuccessful Change Minor - Invalid Minor Selection

Use Case: 4.2.63 Unsuccessful Change Minor - Invalid Minor Selection
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Program Enrollment.
2. Select Update Minor option.
3. Choose a minor that is not valid or available.
4. System validates selection.
5. System detects invalid minor choice.
Exit Condition:
1. System displays error message: Invalid minor selection - cannot update.
2. Student's profile remains unchanged.

4.2.64 Search for Prerequisites of a Course

Use Case: 4.2.64 Search for Prerequisites of a Course
Actors: Student
Initial Condition:
1. User is logged in with Student Credential
Basic Flow:
1. From Student Home Page, navigate to Course Catalog.
2. Select Courses.
3. Search for a course.
4. Select View Prerequisites.
5. System displays prerequisites (or none).
Exit Condition:
1. Student views prerequisites.

4.2.65 Unsuccessful Search for Prerequisites - Search Fails

Use Case: 4.2.65 Unsuccessful Search for Prerequisites - Search Fails
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. Student searches course catalog for prerequisites.
2. System fails to retrieve course data.
3. Search returns no results.
Exit Condition:
1. System displays error message: Invalid login credentials.

4.2.66 Search for Advisor Information

Use Case: 4.2.66 Search for Advisor Information
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials
Basic Flow:
1. From Student Home Page, navigate to Advisement Page.
2. Select Advisor Information.
3. View assigned advisor details.
Exit Condition:
1. Student views advisor information.

4.2.67 Unsuccessful Search for Advisor Information - No Advisor

Use Case: 4.2.67 Unsuccessful Search for Advisor Information - No Advisor
Actors: Student
Initial Condition:
1. User is logged in with Student Credentials.
2. No advisor has been assigned to the student.
Basic Flow:
1. From Student Home Page, navigate to Advisement Page.
2. Select Advisor Information.
3. System checks if an advisor is assigned.
4. System detects that no advisor is assigned.
Exit Condition:
1. System displays error message: No advisor assigned.
2. Advisor information cannot be retrieved.

----------------------------------------
4.3 Faculty

4.3.1 View Home Pages

Use Case: 4.3.1 View Home Pages
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From the system dashboard, faculty navigates to available home page options.
2. System loads the selected home page (e.g., Faculty).
3. System displays relevant menus and faculty-specific functions.
Exit Condition:
1. Selected home page successfully displayed.

4.3.2 View Login Page

Use Case: 4.3.2 View Login Page
Actors: Faculty
Initial Condition:
1. User is not logged in
Basic Flow:
1. Faculty user navigates to the system URL or attempts to access a secured page.
2. System redirects user to Login Page.
3. Login fields (username and password) are displayed.
Exit Condition:
1. Login page successfully displayed.

4.3.3 View Personal Information

Use Case: 4.3.3 View Personal Information
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Profile or Personal Information.
2. System retrieves faculty profile details from the database.
3. System displays faculty personal information (e.g., name, ID, contact info, department, designation).
Exit Condition:
1. Faculty's personal information successfully displayed.

4.3.4 View Present Semester Schedule

Use Case: 4.3.4 View Present Semester Schedule
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Faculty Console.
2. Select Semester Schedule.
3. Choose Fall 2026.
4. View current teaching schedule.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.5 View Next Semester Schedule

Use Case: 4.3.5 View Next Semester Schedule
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Faculty Console.
2. Select Semester Schedule.
3. Choose Spring 2027.
4. View upcoming teaching schedule.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.6 View All Course-Sections

Use Case: 4.3.6 View All Course-Sections
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Faculty Console.
2. Select My Course-Sections.
3. System retrieves all course-sections assigned to the faculty.
4. Faculty views list of own course-sections.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.7 View Roster for a Course-Section

Use Case: 4.3.7 View Roster for a Course-Section
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Faculty Console.
2. Select Semester Schedule.
3. Choose a course section.
4. View roster (student list).
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.8 View Own Course Section Attendance

Use Case: 4.3.8 View Own Course Section Attendance
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Faculty Console.
2. Select Attendance Records.
3. Choose a course-section assigned to the faculty.
4. System retrieves and displays attendance information for that section.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.9 View Advisees' Information

Use Case: 4.3.9 View Advisees' Information
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Advisement.
2. Select View Advisees.
3. View assigned advisees' details.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.10 View Student Information

Use Case: 4.3.10 View Student Information
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Advisement.
2. Select a student from advisees list.
3. Navigate to Personal Information.
4. View student profile details.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.11 View Student Schedule for Fall 2026 / Spring 2027

Use Case: 4.3.11 View Student Schedule for Fall 2026 / Spring 2027
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Advisement.
2. Select a student.
3. Navigate to Semester Schedule.
4. View Fall 2026 or Spring 2027 schedule.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.12 View Student Semester Grades

Use Case: 4.3.12 View Student Semester Grades
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Advisement.
2. Select a student.
3. Navigate to Semester Grades.
4. View grades.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.13 View Student Unofficial Transcript

Use Case: 4.3.13 View Student Unofficial Transcript
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Advisement.
2. Select a student.
3. Navigate to Unofficial Transcript.
4. View transcript.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.14 View Student Degree Audit

Use Case: 4.3.14 View Student Degree Audit
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Advisement.
2. Select a student.
3. Navigate to Degree Audit.
4. View degree progress.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.15 View Student Holds

Use Case: 4.3.15 View Student Holds
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Advisement.
2. Select a student.
3. Navigate to Holds.
4. View active holds.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.16 View a Student's Advisor

Use Case: 4.3.16 View a Student's Advisor
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Advisement.
2. Select a student.
3. Navigate to Advisor Information.
4. System retrieves and displays the student's assigned advisor.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.17 View Departments

Use Case: 4.3.17 View Departments
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Course Catalog.
2. Select Departments.
3. View list of departments.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.18 View Courses by Department

Use Case: 4.3.18 View Courses by Department
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Course Catalog.
2. Select a department.
3. View courses offered.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.19 View Majors by Department

Use Case: 4.3.19 View Majors by Department
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Course Catalog.
2. Select a department.
3. View available majors.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.20 View Course Requirements for a Major

Use Case: 4.3.20 View Course Requirements for a Major
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Course Catalog.
2. Select Programs.
3. Select a Major.
4. View requirements.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.21 View Minors by Department

Use Case: 4.3.21 View Minors by Department
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Course Catalog.
2. Select a department.
3. View minors offered.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.22 View Course Requirements for a Minor

Use Case: 4.3.22 View Course Requirements for a Minor
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Course Catalog.
2. Select Programs.
3. Select a Minor.
4. View requirements.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.23 View Master Schedule by Department

Use Case: 4.3.23 View Master Schedule by Department
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Master Schedule.
2. Filter by Department.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.24 View Master Schedule by Course Name

Use Case: 4.3.24 View Master Schedule by Course Name
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Master Schedule.
2. Filter by Course Name.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.25 View Master Schedule by Faculty Name

Use Case: 4.3.25 View Master Schedule by Faculty Name
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Master Schedule.
2. Filter by Faculty Name.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.26 View Master Schedule by Time Slot

Use Case: 4.3.26 View Master Schedule by Time Slot
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Master Schedule.
2. Filter by Time Slot.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.27 View Master Schedule by Building/Room Number

Use Case: 4.3.27 View Master Schedule by Building/Room Number
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Master Schedule.
2. Filter by Building/Room Number.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.28 View Master Schedule by Available Seats

Use Case: 4.3.28 View Master Schedule by Available Seats
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Master Schedule.
2. Filter by Available Seats.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.29 View All Advisors

Use Case: 4.3.29 View All Advisors
Actors: Faculty
Initial Condition:
1. User logs in with Faculty Credentials
Basic Flow:
1. From Faculty Home Page, navigate to Advisement.
2. Select View Advisors.
3. View all advisors.
Exit Condition:
1. Faculty logs out.
2. Browser is closed.

4.3.30 Login

Use Case: 4.3.30 Login
Actors: Faculty
Initial Condition:
1. Faculty has valid credentials
Basic Flow:
1. Navigate to the login page.
2. Enter username and password.
3. System authenticates credentials.
4. Redirect to Faculty Home Page.
Exit Condition:
1. Faculty successfully logged in.

4.3.31 Unsuccessful Login

Use Case: 4.3.31 Unsuccessful Login
Actors: Faculty
Initial Condition:
1. Faculty opens login page
Basic Flow:
1. Enter incorrect credentials.
2. Click Login.
3. System denies access and displays error.
Exit Condition:
1. Faculty remains on login page.

4.3.32 Password Reset

Use Case: 4.3.32 Password Reset
Actors: Faculty
Initial Condition:
1. Faculty is on the login page
Basic Flow:
1. Click on Forgot Password.
2. Enter registered email or username.
3. System sends password reset link.
4. Faculty opens link and enters a new password.
5. Save changes.
Exit Condition:
1. Password updated successfully.

4.3.33 Unsuccessful Password Reset

Use Case: 4.3.33 Unsuccessful Password Reset
Actors: Faculty
Initial Condition:
1. Faculty selects Forgot Password
Basic Flow:
1. Faculty enters an email or ID that is not registered in the system.
2. System checks the entered information.
3. System detects the email/ID is invalid or not found.
Exit Condition:
1. System displays error message: Invalid email/ID - cannot reset password.
2. Password reset link is not sent.

4.3.34 Update Faculty Information

Use Case: 4.3.34 Update Faculty Information
Actors: Faculty
Initial Condition:
1. Faculty is logged in
Basic Flow:
1. Navigate to Profile or Faculty Information.
2. Update personal or professional details (e.g., phone, email, office).
3. Save changes.
Exit Condition:
1. Faculty information updated.

4.3.35 Unsuccessful Update Personal Information

Use Case: 4.3.35 Unsuccessful Update Personal Information
Actors: Faculty
Initial Condition:
1. Faculty is logged in
Basic Flow:
1. Navigate to Profile or Faculty Info.
2. Attempt to edit restricted or sensitive fields (e.g., government ID).
3. System validates updated fields.
4. System detects unsafe or non-editable fields were modified.
Exit Condition:
1. Updated information is stored unsuccessfully.

4.3.36 Account Password Change

Use Case: 4.3.36 Account Password Change
Actors: Faculty
Initial Condition:
1. Faculty logged in
Basic Flow:
1. Navigate to Account Settings.
2. Select Change Password.
3. Enter current password.
4. Enter a valid new password.
5. System validates new password.
6. System updates the password successfully.
Exit Condition:
1. Faculty can now log in with the new password.

4.3.37 Unsuccessful Password Change - Using Existing Password

Use Case: 4.3.37 Unsuccessful Password Change - Using Existing Password
Actors: Faculty
Initial Condition:
1. Faculty logged in
Basic Flow:
1. Navigate to Account Settings.
2. Select Change Password.
3. Enter current password.
4. Enter a new password that is identical to the old password.
5. System validates and detects same password.
Exit Condition:
1. System displays error message: New password cannot be the same as the current password.
2. Password remains unchanged.

4.3.38 Unsuccessful Password Change - Not Meeting Requirements

Use Case: 4.3.38 Unsuccessful Password Change - Not Meeting Requirements
Actors: Faculty
Initial Condition:
1. Faculty logged in
Basic Flow:
1. Navigate to Account Settings.
2. Select Change Password.
3. Enter current password.
4. Enter a new password that fails to meet security requirements (e.g., length, complexity).
5. System validates the password and detects rule violations.
Exit Condition:
1. System displays error message: Password does not meet security requirements.
2. Password remains unchanged.

4.3.39 Assign Grades for a Course-Section

Use Case: 4.3.39 Assign Grades for a Course-Section
Actors: Faculty
Initial Condition:
1. Faculty is logged in and assigned to a course-section
Basic Flow:
1. Navigate to Faculty Console.
2. Select Semester Schedule.
3. Choose the course-section.
4. Navigate to Grade Assignment.
5. Enter grades for midterm or final.
6. Save or submit grades.
Exit Condition:
1. Grades successfully assigned.

4.3.40 Unsuccessful Assigning Grades - Outside Time Window

Use Case: 4.3.40 Unsuccessful Assigning Grades - Outside Time Window
Actors: Faculty
Initial Condition:
1. Faculty logged in, outside grading period
Basic Flow:
1. Navigate to Semester Grades.
2. Attempt to assign/update grades.
3. System prevents changes and displays error.
Exit Condition:
1. Grades not recorded or updated.

4.3.41 Unsuccessful Assign Grades - Not Own Course Section

Use Case: 4.3.41 Unsuccessful Assign Grades - Not Own Course Section
Actors: Faculty
Initial Condition:
1. Faculty is logged in.
2. Faculty attempts to access a course section not assigned to them.
Basic Flow:
1. Navigate to Semester Grades.
2. Select a course section.
3. System verifies course-section ownership.
4. System detects that the selected course section does not belong to the faculty member.
Exit Condition:
1. System displays error message: Cannot assign grades - not authorized for this course section.
2. Grades are not recorded or updated.

4.3.42 Grades Update for a Course-Section

Use Case: 4.3.42 Grades Update for a Course-Section
Actors: Faculty
Initial Condition:
1. Faculty is logged in and grades have been previously assigned
Basic Flow:
1. Navigate to Faculty Console.
2. Select Semester Schedule.
3. Choose the course-section.
4. Navigate to Grade Update.
5. Edit existing grades.
6. Save changes.
Exit Condition:
1. Grades updated successfully.

4.3.43 Unsuccessfully Update Grades for a Course-Section

Use Case: 4.3.43 Unsuccessfully Update Grades for a Course-Section
Actors: Faculty
Initial Condition:
1. Faculty logged in, within allowed update period
Basic Flow:
1. Navigate to Semester Grades.
2. Select course-section.
3. Click Update Grade.
4. Receive Error Cannot Update Grade Outside Time Window.
Exit Condition:
1. Grades are not updated.
2. User logs out.

4.3.44 Record Attendance for a Course-Section

Use Case: 4.3.44 Record Attendance for a Course-Section
Actors: Faculty
Initial Condition:
1. Faculty is logged in and assigned to a course-section
Basic Flow:
1. Navigate to Faculty Console.
2. Select Semester Schedule.
3. Choose the course-section.
4. Navigate to Attendance Recording.
5. Mark attendance for students.
6. Save records.
Exit Condition:
1. Attendance successfully recorded.

4.3.45 Unsuccessful Recording of Attendance at a Different Time

Use Case: 4.3.45 Unsuccessful Recording of Attendance at a Different Time
Actors: Faculty
Initial Condition:
1. Faculty logged in, outside scheduled class time
Basic Flow:
1. Navigate to Attendance.
2. Attempt to mark attendance.
3. System prevents recording and displays error.
Exit Condition:
1. Attendance not recorded.
2. User logs out.

4.3.46 Attendance Update for a Course-Section

Use Case: 4.3.46 Attendance Update for a Course-Section
Actors: Faculty
Initial Condition:
1. Faculty is logged in and attendance has been previously recorded
Basic Flow:
1. Navigate to Faculty Console.
2. Select Semester Schedule.
3. Choose the course-section.
4. Navigate to Attendance Update.
5. Edit attendance records as needed.
6. Save changes.
Exit Condition:
1. Attendance updated successfully.

4.3.47 Unsuccessful Updating Attendance - Outside Time Window

Use Case: 4.3.47 Unsuccessful Updating Attendance - Outside Time Window
Actors: Faculty
Initial Condition:
1. Faculty logged in, outside allowed attendance update period
Basic Flow:
1. Navigate to Attendance.
2. Attempt to update previous attendance.
3. System prevents changes and displays error.
Exit Condition:
1. Attendance not updated.

4.3.48 Send Email Messages/Notifications to Students

Use Case: 4.3.48 Send Email Messages/Notifications to Students
Actors: Faculty
Initial Condition:
1. Faculty is logged in
Basic Flow:
1. Navigate to Messaging or Notifications.
2. Select student(s) or course-section.
3. Compose message or select template.
4. Send message or notification.
5. Confirm delivery.
Exit Condition:
1. Message/notification successfully sent.

4.3.49 Unsuccessful Send Email - Invalid Recipients/System Error

Use Case: 4.3.49 Unsuccessful Send Email - Invalid Recipients/System Error
Actors: Faculty
Initial Condition:
1. Faculty is logged in
Basic Flow:
1. Navigate to Messaging or Notifications.
2. Select student(s) or course-section.
3. Compose message or choose a template.
4. Click Send.
5. System validates recipients.
6. System detects invalid email addresses, unauthorized recipients, or encounters a system error.
Exit Condition:
1. System displays error message such as: Invalid recipient(s). Unable to send message due to system error.
2. Message/notification is not sent.

----------------------------------------
4.4 Admin

4.4.1 View Home Pages

Use Case: 4.4.1 View Home Pages
Actors: Administrator
Initial Condition:
1. Administrator logs in with Admin Credentials
Basic Flow:
1. From Admin Dashboard, navigate to available home pages.
2. Admin Home.
3. Student Management Home.
4. Faculty Management Home.
5. System Settings Home.
6. System loads the selected home page.
7. Admin views available options and tools.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.2 View Login Page

Use Case: 4.4.2 View Login Page
Actors: Administrator
Initial Condition:
1. Administrator logs in with Admin Credentials
Basic Flow:
1. User navigates to the system URL or attempts to access a restricted resource.
2. System redirects to the Login Page.
3. Login fields (username and password) are displayed.
Exit Condition:
1. Login page successfully displayed.

4.4.3 View Student Information

Use Case: 4.4.3 View Student Information
Actors: Administrator
Initial Condition:
1. Administrator logs in with Admin Credentials
Basic Flow:
1. Admin logs in with credentials.
2. Navigate to Student Management Student Profile.
3. View student information.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.4 View Student Schedule

Use Case: 4.4.4 View Student Schedule
Actors: Administrator
Initial Condition:
1. Administrator logs in with Admin Credentials
Basic Flow:
1. Admin logs in.
2. Navigate to Student Console Registration Semester Schedule.
3. Select semester (Fall 2026 / Spring 2027).
4. View student's semester schedule.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.5 View Student Semester Grades

Use Case: 4.4.5 View Student Semester Grades
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Admin navigates to Academics Semester Grades.
2. View student grades for selected semester.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.6 View Student Unofficial Transcript

Use Case: 4.4.6 View Student Unofficial Transcript
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Navigate to Academics Unofficial Transcript.
2. View transcript.
3. View student information.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.7 View Student Degree Audit

Use Case: 4.4.7 View Student Degree Audit
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Navigate to Academics Degree Audit.
2. View audit.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.8 View Student Holds

Use Case: 4.4.8 View Student Holds
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Navigate to Registration Holds.
2. View active holds.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.9 View Student Advisor

Use Case: 4.4.9 View Student Advisor
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Navigate to Advisement Page.
2. View student's assigned advisor.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.10 View Faculty Semester Schedule

Use Case: 4.4.10 View Faculty Semester Schedule
Actors: Administrator
Initial Condition:
1. Administrator logs in with Admin Credentials
Basic Flow:
1. Administrator logs in with valid credentials.
2. Navigate to Faculty Information Semester Schedule.
3. Select a faculty member and semester (Fall 2026 / Spring 2027).
4. View faculty semester schedule.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.11 View Faculty Course-Sections

Use Case: 4.4.11 View Faculty Course-Sections
Actors: Administrator
Initial Condition:
1. Administrator logged in
Basic Flow:
1. Navigate to Faculty Information Course Sections.
2. Select faculty member.
3. View list of assigned course-sections.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.12 View Course Section Roster

Use Case: 4.4.12 View Course Section Roster
Actors: Administrator
Initial Condition:
1. Administrator logged in
Basic Flow:
1. Navigate to Faculty Information Course Sections.
2. Select a course section.
3. View enrolled student roster.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.13 View Course Section Grades

Use Case: 4.4.13 View Course Section Grades
Actors: Administrator
Initial Condition:
1. Administrator logged in
Basic Flow:
1. Navigate to Faculty Information Course Sections.
2. Select a course section.
3. View submitted student grades.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.14 View Course Attendance

Use Case: 4.4.14 View Course Attendance
Actors: Administrator
Initial Condition:
1. Administrator logged in
Basic Flow:
1. Navigate to Faculty Information Course Sections.
2. Select a course section.
3. Navigate to Attendance Records.
4. View submitted attendance for students in the selected section.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.15 View All Advisors

Use Case: 4.4.15 View All Advisors
Actors: Administrator
Initial Condition:
1. Administrator logged in
Basic Flow:
1. Navigate to Faculty Information Advisors.
2. View list of all assigned advisors.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.16 View Advisor's Advisees

Use Case: 4.4.16 View Advisor's Advisees
Actors: Administrator
Initial Condition:
1. Administrator logged in
Basic Flow:
1. Navigate to Faculty Information Advisors.
2. Select an advisor.
3. View list of assigned advisees (students).
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.17 View Courses

Use Case: 4.4.17 View Courses
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Navigate to Course Catalog Programs.
2. View departments, courses, majors, and minors.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.18 View Courses by Department

Use Case: 4.4.18 View Courses by Department
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Navigate to Course Catalog Departments.
2. Select a department.
3. System displays all courses offered by the selected department.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.19 View Majors by Department

Use Case: 4.4.19 View Majors by Department
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Navigate to Course Catalog Departments.
2. Select a department.
3. System displays all majors offered by the selected department.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.20 View Minors by Department

Use Case: 4.4.20 View Minors by Department
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Navigate to Course Catalog Departments.
2. Select a department.
3. System displays all minors offered by the selected department.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.21 View Course Requirements - Major/Minor

Use Case: 4.4.21 View Course Requirements - Major/Minor
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Navigate to Course Catalog Programs View Program.
2. View requirements for selected major/minor.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.22 View Semester Master Schedule

Use Case: 4.4.22 View Semester Master Schedule
Actors: Administrator
Initial Condition:
1. Admin logged in
Basic Flow:
1. Navigate to Registration Master Schedule.
2. View schedule by department, course, faculty, time slot, building/room, available seats.
Exit Condition:
1. Admin logs out.
2. Browser closes.

4.4.23 Create Student User

Use Case: 4.4.23 Create Student User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin
Basic Flow:
1. Administrator navigates to Student Management Create Student.
2. Enter required student details (name, ID, program, contact info, etc.).
3. Save new student user record.
Exit Condition:
1. Student user successfully created.

4.4.24 Unsuccessful Creating a User

Use Case: 4.4.24 Unsuccessful Creating a User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Administrator navigates to User Management Create User.
2. Attempts to create a user but enters incomplete or invalid information.
3. System validates input.
Exit Condition:
1. System displays error message, user creation not completed.

4.4.25 Create Faculty User

Use Case: 4.4.25 Create Faculty User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Faculty Management Create Faculty.
2. Enter faculty details (name, ID, department, designation, etc.).
3. Save faculty record.
Exit Condition:
1. Faculty user created successfully.

4.4.26 Unsuccessful Creating a User

Use Case: 4.4.26 Unsuccessful Creating a User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Administrator navigates to User Management Create User.
2. Attempts to create a user but enters incomplete or invalid information.
3. System validates input.
Exit Condition:
1. System displays error message, user creation not completed.

4.4.27 Create Administrator User

Use Case: 4.4.27 Create Administrator User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Admin Management Create Administrator.
2. Enter administrator details (name, ID, email, role, access level, etc.).
3. Save administrator record.
4. System validates entered details.
Exit Condition:
1. Administrator user created successfully.

4.4.28 Unsuccessful Creating a User

Use Case: 4.4.28 Unsuccessful Creating a User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Administrator navigates to User Management Create User.
2. Attempts to create a user but enters incomplete or invalid information.
3. System validates input.
Exit Condition:
1. System displays error message, user creation not completed.

4.4.29 Student Account Information Update

Use Case: 4.4.29 Student Account Information Update
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Management Student Profile.
2. Select student account.
3. Update personal or academic information.
4. Save changes.
Exit Condition:
1. Student account information updated.

4.4.30 Unsuccessful Student Account Information Update

Use Case: 4.4.30 Unsuccessful Student Account Information Update
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Management Student Profile.
2. Select a student account.
3. Attempt to update restricted or sensitive fields (e.g., government ID).
4. System detects unsafe/unauthorized modification attempt.
Exit Condition:
1. System prevents update.
2. Error message displayed: This field cannot be modified.

4.4.31 Faculty Account Information Update

Use Case: 4.4.31 Faculty Account Information Update
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Faculty Management Faculty Profile.
2. Select faculty account.
3. Update personal/academic detail.
4. Save changes.
Exit Condition:
1. Faculty account updated.

4.4.32 Unsuccessful Faculty Account Information Update

Use Case: 4.4.32 Unsuccessful Faculty Account Information Update
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Faculty Management Faculty Profile.
2. Select a faculty account.
3. Attempt to modify restricted or protected fields (e.g., government ID).
4. System detects unauthorized or unsafe modification attempt.
Exit Condition:
1. System prevents the update.
2. Error message displayed: This field cannot be modified.

4.4.33 Administrator Account Information Update

Use Case: 4.4.33 Administrator Account Information Update
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Admin Management Admin Profile.
2. Select an administrator account (including own account).
3. Update personal or system-related details.
4. Save changes.
5. System processes the request.
Exit Condition:
1. Admin account information updated.

4.4.34 Unsuccessful Administrator Account Information Update

Use Case: 4.4.34 Unsuccessful Administrator Account Information Update
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Admin Management Admin Profile.
2. Select an admin account.
3. Attempt to modify restricted fields (admin ID, system-generated identifiers, audit-sensitive fields).
4. System detects unauthorized modification attempt.
Exit Condition:
1. Update prevented.
2. System displays error message: This field cannot be modified.

4.4.35 Deactivate Student User

Use Case: 4.4.35 Deactivate Student User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Management Deactivate Student.
2. Select student user.
3. Confirm deactivation.
Exit Condition:
1. Student user deactivated.

4.4.36 Unsuccessful Deactivate Student User

Use Case: 4.4.36 Unsuccessful Deactivate Student User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Management Deactivate Student.
2. Select a student user.
3. Attempt to confirm deactivation.
4. System checks for active course enrollments.
5. System detects one or more active enrollments and blocks the action.
Exit Condition:
1. Student user not deactivated.
2. Error displayed: Cannot deactivate user with active enrollments.

4.4.37 Deactivate Faculty User

Use Case: 4.4.37 Deactivate Faculty User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Faculty Management Deactivate Faculty.
2. Select faculty user.
3. Confirm deactivation.
Exit Condition:
1. Faculty user deactivated.

4.4.38 Unsuccessful Deactivate Faculty User

Use Case: 4.4.38 Unsuccessful Deactivate Faculty User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Faculty Management Deactivate Faculty.
2. Select faculty user.
3. Attempt to confirm deactivation.
4. System checks assignment records.
5. Active teaching assignments detected.
6. System blocks the deactivation.
Exit Condition:
1. Faculty user not deactivated.
2. Error displayed: Cannot deactivate faculty with active assignments.

4.4.39 Deactivate Administrator User

Use Case: 4.4.39 Deactivate Administrator User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Admin Management Deactivate Administrator.
2. Select administrator account.
3. Confirm deactivation.
4. System verifies deactivation rules (e.g., cannot deactivate last active admin).
Exit Condition:
1. Administrator user deactivated.

4.4.40 Unsuccessful Deactivate Administrator User

Use Case: 4.4.40 Unsuccessful Deactivate Administrator User
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Admin Management Deactivate Administrator.
2. Select administrator account.
3. Attempt to confirm deactivation.
4. System checks if:
5. User is attempting to deactivate their own account.
6. User is the last remaining active administrator.
7. System prevents deactivation.
Exit Condition:
1. Administrator user not deactivated.
2. Error displayed: Cannot deactivate own account OR Cannot deactivate last active administrator.

4.4.41 Change Any Account Password

Use Case: 4.4.41 Change Any Account Password
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to User Management.
2. Select Student / Faculty / Administrator account.
3. Choose the option Change Password.
4. Enter new password or auto-generate a secure password.
5. System validates password requirements.
6. Save changes.
7. System updates the user's password successfully.
Exit Condition:
1. Password for the selected user updated successfully.
2. User will log in using the new password.

4.4.42 Unsuccessful Change Password - Using Existing Password

Use Case: 4.4.42 Unsuccessful Change Password - Using Existing Password
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Account Settings Change Password.
2. Enter current password.
3. Enter new password that is identical to the current password.
4. System compares new password with existing password.
5. System detects that the new password matches the old one.
6. Password change is blocked.
Exit Condition:
1. Password not changed.
2. Error displayed: New password cannot be the same as the current password.

4.4.43 Unsuccessful Change Password - Not Meeting Requirements

Use Case: 4.4.43 Unsuccessful Change Password - Not Meeting Requirements
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Account Settings Change Password.
2. Enter current password.
3. Enter new password that does not meet system requirements.
4. Too short.
5. Missing special characters/numbers.
6. Weak password pattern.
7. System validates new password.
8. System rejects the password due to failing security rules.
Exit Condition:
1. Password not changed.
2. Error displayed: Password does not meet security requirements.

4.4.44 Add Student to Major

Use Case: 4.4.44 Add Student to Major
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student is eligible to add a major.
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Add Major option.
3. Choose an available major.
4. Assign the student to the selected major.
5. Save changes.
6. System updates student's program information.
Exit Condition:
1. Major added successfully.

4.4.45 Unsuccessful Add Student to Major - Student Already in this

Use Case: 4.4.45 Unsuccessful Add Student to Major - Student Already in this
Actors: Administrator
Initial Condition:
1. Administrator is logged in.
2. Student already has an existing major.
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Add Major.
3. Attempt to assign a new major.
4. System checks program records.
5. System detects student already has a major.
Exit Condition:
1. Major not added.
2. Error displayed: Student already in this major.

4.4.46 Unsuccessful Add Student to Major - More Than 2 Majors

Use Case: 4.4.46 Unsuccessful Add Student to Major - More Than 2 Majors
Actors: Administrator
Initial Condition:
1. Administrator is logged in.
2. Student already has two declared majors.
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Add Major.
3. Attempt to assign a third major.
4. System checks program record.
5. System detects major limit exceeded.
Exit Condition:
1. Major not added.
2. Error displayed: Cannot add more than 2 majors.

4.4.47 Unsuccessful Add Student to Major - Exceeds 2 Major/Minor

Use Case: 4.4.47 Unsuccessful Add Student to Major - Exceeds 2 Major/Minor
Actors: Administrator
Initial Condition:
1. Administrator is logged in.
2. Student already has a combination of two majors/minors (e.g., 1 major + 1 minor, 2 minors, etc.).
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Add Major.
3. Attempt to assign a new major.
4. System checks total major/minor count.
5. System detects combined limit exceeded.
Exit Condition:
1. Major not added.
2. Error displayed: Cannot exceed the 2 major/minor combined limit.

4.4.48 Remove Student from Major/Minor

Use Case: 4.4.48 Remove Student from Major/Minor
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Remove Minor option.
3. System displays the student's assigned minor.
4. Administrator confirms removal.
5. System updates the student's program information.
6. Save changes.
Exit Condition:
1. Minor removed successfully.
2. Confirmation message displayed.

4.4.49 Unsuccessful Remove Student from Major/Minor - Student

Use Case: 4.4.49 Unsuccessful Remove Student from Major/Minor - Student
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Remove Minor option.
3. System checks if the student has an assigned minor.
4. System detects no minor assigned.
5. System blocks the removal action.
Exit Condition:
1. Error displayed: Student has no major/minor assigned - cannot remove.

4.4.50 Successfully Change Student Major

Use Case: 4.4.50 Successfully Change Student Major
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Change Major option.
3. Choose a valid new major from the list.
4. System verifies the major selection.
5. Assign the new major to the student.
6. Save changes.
Exit Condition:
1. Student major changed successfully.
2. Confirmation message displayed.

4.4.51 Unsuccessful Change Student Major - Invalid Major Selection

Use Case: 4.4.51 Unsuccessful Change Student Major - Invalid Major Selection
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Change Major option.
3. Administrator selects an invalid major (e.g., inactive, nonexistent, restricted).
4. System validates the selection.
5. Validation fails.
Exit Condition:
1. Error displayed: Invalid major selection - please choose a valid major.

4.4.52 Unsuccessful Change Student Major - Same as Current Major

Use Case: 4.4.52 Unsuccessful Change Student Major - Same as Current Major
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Change Major option.
3. Administrator selects the same major already assigned to the student.
4. System compares current and new selections.
5. System detects no change.
Exit Condition:
1. Error displayed: Selected major is the same as the current major - no changes applied.

4.4.53 Add Student to Minor

Use Case: 4.4.53 Add Student to Minor
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Add Minor option.
3. Choose a valid minor from the list.
4. Assign the student to the selected minor.
5. Save changes.
Exit Condition:
1. Minor added successfully.
2. Confirmation message displayed.

4.4.54 Unsuccessful Add Student to Minor - Student Already Has

Use Case: 4.4.54 Unsuccessful Add Student to Minor - Student Already Has
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Add Minor option.
3. System checks if the student already has a minor assigned.
4. Detection of an existing minor prevents addition.
Exit Condition:
1. Error displayed: Student already has a minor assigned - cannot add another.

4.4.55 Unsuccessful Add Student to Minor - No Major Declared

Use Case: 4.4.55 Unsuccessful Add Student to Minor - No Major Declared
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Add Minor option.
3. System checks if the student has a major declared.
4. Detection of no major prevents minor addition.
Exit Condition:
1. Error displayed: Student has no major declared - cannot add a minor.

4.4.56 Unsuccessful Add Student to Minor - Exceeds 2 Major/Minor

Use Case: 4.4.56 Unsuccessful Add Student to Minor - Exceeds 2 Major/Minor
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Add Minor option.
3. System counts the total number of majors and minors assigned to the student.
4. Detection of total exceeding 2 prevents minor addition.
Exit Condition:
1. Error displayed: Cannot add minor - student exceeds maximum allowed 2 combined majors/minors.

4.4.57 Remove Student from Minor

Use Case: 4.4.57 Remove Student from Minor
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student has an assigned minor.
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Remove Minor option.
3. System displays the assigned minor.
4. Administrator confirms the removal.
5. System removes the minor from the student's profile.
6. Save changes.
Exit Condition:
1. Minor removed successfully.
2. Success message displayed.

4.4.58 Unsuccessful Remove Student from Minor - Student Has No

Use Case: 4.4.58 Unsuccessful Remove Student from Minor - Student Has No
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student has no assigned minor.
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Remove Minor option.
3. System checks if the student has any assigned minor.
4. System finds no minor assigned.
5. System displays an error message: No minor assigned to remove.
Exit Condition:
1. Minor not removed.
2. Error message displayed to the administrator.

4.4.59 Change Student Minor

Use Case: 4.4.59 Change Student Minor
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student currently has an assigned minor.
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Change Minor option.
3. Select a new valid minor.
4. Assign the new minor.
5. Save changes.
Exit Condition:
1. Student minor changed successfully.
2. Success message displayed.

4.4.60 Unsuccessful Change Student Minor - Invalid Minor Selection

Use Case: 4.4.60 Unsuccessful Change Student Minor - Invalid Minor Selection
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Change Minor option.
3. Administrator selects a minor not available / not valid.
4. System validates the selection.
5. System identifies the selected minor as invalid.
6. System displays an error message.
Exit Condition:
1. Student minor not changed.
2. Error message displayed: Invalid minor selection.

4.4.61 Unsuccessful Change Student Minor - Same as Current Minor

Use Case: 4.4.61 Unsuccessful Change Student Minor - Same as Current Minor
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student already has an assigned minor.
Basic Flow:
1. Navigate to Student Profile Programs.
2. Select Change Minor option.
3. Administrator selects the same minor the student already has.
4. System checks for changes.
5. System identifies no change in minor.
6. System displays a notification or error.
Exit Condition:
1. Student minor not updated.
2. Error message displayed: Selected minor is the same as the current minor.

4.4.62 Add Student to Course-Section

Use Case: 4.4.62 Add Student to Course-Section
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student is eligible for registration.
Basic Flow:
1. Navigate to Student Registration Add Course Section.
2. Select course section and student.
3. Confirm registration.
4. System validates all requirements.
5. System registers the student in the selected course section.
Exit Condition:
1. Student successfully registered in the course section.
2. Success message displayed.

4.4.63 Unsuccessful Add Student - Undergraduate to Graduate

Use Case: 4.4.63 Unsuccessful Add Student - Undergraduate to Graduate
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student level = Undergraduate.
Basic Flow:
1. Navigate to Student Registration Add Course Section.
2. Select a graduate-level course for an undergraduate student.
3. Confirm registration.
4. System checks course level eligibility.
5. System blocks the registration.
Exit Condition:
1. Student not registered.
2. Error displayed: Undergraduate students cannot register for graduate-level courses.

4.4.64 Unsuccessful Add Student - Graduate to Undergraduate

Use Case: 4.4.64 Unsuccessful Add Student - Graduate to Undergraduate
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student level = Graduate.
Basic Flow:
1. Navigate to Student Registration Add Course Section.
2. Select an undergraduate-level course.
3. Confirm registration.
4. System checks eligibility.
5. System blocks registration.
Exit Condition:
1. Student not registered.
2. Error displayed: Graduate students cannot register for undergraduate-level courses.

4.4.65 Unsuccessful Add Student - Exceeding Credit Limits -

Use Case: 4.4.65 Unsuccessful Add Student - Exceeding Credit Limits -
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student status = Full-Time.
Basic Flow:
1. Navigate to Student Registration Add Course Section.
2. Select course section.
3. Confirm registration.
4. System calculates total credits after adding the course.
5. Total exceeds 16 credits.
6. Registration rejected.
Exit Condition:
1. Student not registered.
2. Error displayed: Full-time students cannot exceed 16 credit hours.

4.4.66 Unsuccessful Add Student - Exceeding Credit Limits -

Use Case: 4.4.66 Unsuccessful Add Student - Exceeding Credit Limits -
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student status = Part-Time.
Basic Flow:
1. Navigate to Student Registration Add Course Section.
2. Select course section.
3. Confirm registration.
4. System calculates total credits.
5. Total exceeds 11 credits.
6. Registration rejected.
Exit Condition:
1. Student not registered.
2. Error displayed: Part-time students cannot exceed 11 credit hours.

4.4.67 Unsuccessful Add Student - Already Taken and Passed

Use Case: 4.4.67 Unsuccessful Add Student - Already Taken and Passed
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student has previously taken and passed the course.
Basic Flow:
1. Navigate to Student Registration Add Course Section.
2. Select course section.
3. Confirm registration.
4. System checks academic history.
5. System detects course already passed.
6. Registration blocked.
Exit Condition:
1. Student not registered.
2. Error displayed: Student has already taken and passed this course.

4.4.68 Unsuccessful Add Student - Missing Prerequisites

Use Case: 4.4.68 Unsuccessful Add Student - Missing Prerequisites
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Course has prerequisites.
Basic Flow:
1. Navigate to Student Registration Add Course Section.
2. Select course section.
3. Confirm registration.
4. System checks completed prerequisites.
5. Missing prerequisites found.
6. System blocks registration.
Exit Condition:
1. Student not registered.
2. Error displayed: Missing prerequisite course(s).

4.4.69 Unsuccessful Add Student - Time Slot Conflict

Use Case: 4.4.69 Unsuccessful Add Student - Time Slot Conflict
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student already has courses assigned.
Basic Flow:
1. Navigate to Student Registration Add Course Section.
2. Select course section.
3. Confirm registration.
4. System checks timetable.
5. Time conflict with existing course detected.
6. Registration blocked.
Exit Condition:
1. Student not registered.
2. Error displayed: Time conflict with another registered course.

4.4.70 Unsuccessful Add Student - No Available Seats

Use Case: 4.4.70 Unsuccessful Add Student - No Available Seats
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Course section is full.
Basic Flow:
1. Navigate to Student Registration Add Course Section.
2. Select course section.
3. Confirm registration.
4. System checks seat availability.
5. No available seats found.
6. Registration denied.
Exit Condition:
1. Student not registered.
2. Error displayed: No available seats in this course section.

4.4.71 Unsuccessful Add Student - Outside Time Window

Use Case: 4.4.71 Unsuccessful Add Student - Outside Time Window
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Registration window is closed (before opening or after closing date).
Basic Flow:
1. Navigate to Student Registration Add Course Section.
2. Select course section.
3. Confirm registration.
4. System checks registration period.
5. System detects that the attempt is outside the allowed window.
6. Registration blocked.
Exit Condition:
1. Student not registered.
2. Error displayed: Registration not allowed outside the designated time window.

4.4.72 Drop Student from Course-Section

Use Case: 4.4.72 Drop Student from Course-Section
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student is enrolled in the selected course section.
Basic Flow:
1. Navigate to Student Registration Drop Course Section.
2. Select course section and student.
3. Confirm drop action.
4. System verifies enrollment.
5. System processes the drop request.
Exit Condition:
1. Student successfully dropped from the course section.
2. Success message displayed.

4.4.73 Unsuccessful Drop Student - Student Not Enrolled in Section

Use Case: 4.4.73 Unsuccessful Drop Student - Student Not Enrolled in Section
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student is not enrolled in the selected course section.
Basic Flow:
1. Navigate to Student Registration Drop Course Section.
2. Select course section and student.
3. Confirm drop action.
4. System checks enrollment status.
5. System finds that the student is not enrolled in the course section.
6. Drop request is rejected.
Exit Condition:
1. Student not dropped.
2. Error displayed: Student is not enrolled in this course section.

4.4.74 Unsuccessful Drop Student - Outside Time Window

Use Case: 4.4.74 Unsuccessful Drop Student - Outside Time Window
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Drop period is closed (past deadline or before opening).
Basic Flow:
1. Navigate to Student Registration Drop Course Section.
2. Select course section and student.
3. Confirm drop action.
4. System checks drop period.
5. System detects that the attempt is outside the allowed drop window.
6. Drop request is blocked.
Exit Condition:
1. Student not dropped.
2. Error displayed: Drop not allowed outside the designated drop period.

4.4.75 Update a Student's Grade

Use Case: 4.4.75 Update a Student's Grade
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Academics Grades Management.
2. Select student and course section.
3. Enter updated grade.
4. Save changes.
Exit Condition:
1. Student grade updated successfully.

4.4.76 Unsuccessful Updating Grades Outside Allowed Grade

Use Case: 4.4.76 Unsuccessful Updating Grades Outside Allowed Grade
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Academics Grades Management.
2. Select course section and student.
3. Attempt to update grade after grade change deadline.
4. System checks allowed grade update window.
Exit Condition:
1. System displays error message, grade update not completed.

4.4.77 Add Student to Advisor

Use Case: 4.4.77 Add Student to Advisor
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student does not currently have an assigned advisor.
Basic Flow:
1. Navigate to Student Profile Advisor Assignment.
2. Select student and advisor.
3. Confirm advisor assignment.
4. System validates advisor availability and department.
5. System assigns the advisor to the student.
Exit Condition:
1. Student successfully assigned to advisor.
2. Success message displayed.

4.4.78 Unsuccessful Add Student to Advisor - Student Already Has

Use Case: 4.4.78 Unsuccessful Add Student to Advisor - Student Already Has
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student already has an existing advisor assigned.
Basic Flow:
1. Navigate to Student Profile Advisor Assignment.
2. Select student and advisor.
3. Confirm advisor assignment.
4. System checks current advisor status.
5. System detects that student already has an advisor.
6. System prevents assignment.
Exit Condition:
1. Student not assigned to new advisor.
2. Error displayed: Student already has an assigned advisor.

4.4.79 Unsuccessful Add Student to Advisor - Advisor Not in Same

Use Case: 4.4.79 Unsuccessful Add Student to Advisor - Advisor Not in Same
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Student is associated with a specific academic department.
3. Selected advisor belongs to a different department.
Basic Flow:
1. Navigate to Student Profile Advisor Assignment.
2. Select student and advisor.
3. Confirm advisor assignment.
4. System checks department alignment.
5. System detects advisor is not in the same department as the student.
6. System blocks the assignment.
Exit Condition:
1. Student not assigned to advisor.
2. Error displayed: Advisor must be from the student's department.

4.4.80 Update Semester Master Schedule

Use Case: 4.4.80 Update Semester Master Schedule
Actors: Administrator
Initial Condition:
1. Administrator is logged in.
2. Semester has not yet started.
3. Master schedule exists.
Basic Flow:
1. Navigate to Edit Schedule.
2. Apply necessary updates.
3. System verifies no conflicts.
4. Save changes.
Exit Condition:
1. Master schedule updated successfully.
2. Success message displayed.

4.4.81 Unsuccessful Update Master Schedule - Semester Already

Use Case: 4.4.81 Unsuccessful Update Master Schedule - Semester Already
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Semester has already started.
Basic Flow:
1. Navigate to Edit Schedule.
2. Attempt to modify schedule.
3. System checks semester timeline.
4. System identifies semester already in progress.
5. System restricts updates.
Exit Condition:
1. Master schedule not updated.
2. Error displayed: Cannot update schedule after the semester has started.

4.4.82 Unsuccessful Update Master Schedule - Conflicts with

Use Case: 4.4.82 Unsuccessful Update Master Schedule - Conflicts with
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Master schedule exists.
Basic Flow:
1. Navigate to Edit Schedule.
2. Apply updates (course, faculty, room, time changes).
3. System validates updated schedule.
4. System detects conflict:
5. Faculty conflict.
6. Room conflict.
7. Course time overlap.
8. Duplicate section issue.
9. System blocks the update.
Exit Condition:
1. Master schedule not updated.
2. Error displayed: Update conflicts with existing schedule.

4.4.83 Create Course Section

Use Case: 4.4.83 Create Course Section
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Course Catalog Add Course.
2. Enter course details (code, name, credits, prerequisites, etc.).
3. Validate inputs.
4. Save new course.
Exit Condition:
1. New course section created successfully.
2. Success message displayed.

4.4.84 Unsuccessful Create Course Section - Time/Date Conflict

Use Case: 4.4.84 Unsuccessful Create Course Section - Time/Date Conflict
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Proposed course section has overlapping time/date with existing sections.
Basic Flow:
1. Navigate to Add Course.
2. Enter course details including time/date.
3. System validates schedule.
4. Conflict detected.
5. Creation blocked.
Exit Condition:
1. Course section not created.
2. Error displayed: Time/date conflict with existing course section.

4.4.85 Unsuccessful Create Course Section - Room Assignment

Use Case: 4.4.85 Unsuccessful Create Course Section - Room Assignment
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Selected room is already assigned for that time.
Basic Flow:
1. Enter course and room details.
2. System checks room availability.
3. Conflict detected.
4. Creation blocked.
Exit Condition:
1. Course section not created.
2. Error displayed: Room is already assigned during this time slot.

4.4.86 Unsuccessful Create Course Section - Faculty

Use Case: 4.4.86 Unsuccessful Create Course Section - Faculty
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Faculty availability does not match the course requirements.
Basic Flow:
1. Enter course and faculty details.
2. System validates faculty status.
3. Conflict detected.
4. Creation blocked.
Exit Condition:
1. Course section not created.
2. Error displayed: Faculty status conflicts with course assignment (part-time/full-time).

4.4.87 Unsuccessful Create Course Section - Faculty Department

Use Case: 4.4.87 Unsuccessful Create Course Section - Faculty Department
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Faculty assigned does not belong to the course's department.
Basic Flow:
1. Enter course and faculty details.
2. System validates faculty department.
3. Conflict detected.
4. Creation blocked.
Exit Condition:
1. Course section not created.
2. Error displayed: Faculty does not belong to the course department.

4.4.88 Edit Course Section - CRN

Use Case: 4.4.88 Edit Course Section - CRN
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Course section exists.
Basic Flow:
1. Navigate to Edit Course Section CRN.
2. Enter new CRN.
3. Validate uniqueness.
4. Save changes.
Exit Condition:
1. CRN updated successfully.
2. Success message displayed.

4.4.89 Unsuccessful Edit Course Section - CRN

Use Case: 4.4.89 Unsuccessful Edit Course Section - CRN
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. New CRN already exists in system.
Basic Flow:
1. Attempt to update CRN.
2. System checks uniqueness.
3. Duplicate detected.
4. Update blocked.
Exit Condition:
1. CRN not updated.
2. Error displayed: CRN already exists.

4.4.90 Edit Course Section - Course Name

Use Case: 4.4.90 Edit Course Section - Course Name
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Course section exists.
Basic Flow:
1. Navigate to Edit Course Section Course Name.
2. Enter new course name.
3. Save changes.
Exit Condition:
1. Course name updated successfully.
2. Success message displayed.

4.4.91 Unsuccessful Edit Course Section - Course Name

Use Case: 4.4.91 Unsuccessful Edit Course Section - Course Name
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Entered course name is invalid or does not exist.
Basic Flow:
1. Attempt to update course name.
2. System validates name.
3. Invalid name detected.
4. Update blocked.
Exit Condition:
1. Course name not updated.
2. Error displayed: Invalid course name.

4.4.92 Edit Course Section - Section ID

Use Case: 4.4.92 Edit Course Section - Section ID
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Course section exists.
Basic Flow:
1. Navigate to Edit Section ID.
2. Enter new Section ID.
3. Validate uniqueness.
4. Save changes.
Exit Condition:
1. Section ID updated successfully.
2. Success message displayed.

4.4.93 Unsuccessful Edit Course Section - Section ID

Use Case: 4.4.93 Unsuccessful Edit Course Section - Section ID
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. New Section ID already exists.
Basic Flow:
1. Attempt to update Section ID.
2. System checks uniqueness.
3. Duplicate detected.
4. Update blocked.
Exit Condition:
1. Section ID not updated.
2. Error displayed: Section ID already exists.

4.4.94 Edit Course Section - Faculty

Use Case: 4.4.94 Edit Course Section - Faculty
Actors: Administrator
Initial Condition:
1. Administrator is logged in.
2. Faculty and course section exist.
Basic Flow:
1. Navigate to Edit Faculty Assignment.
2. Select new faculty.
3. Validate availability, department, and status.
4. Save changes.
Exit Condition:
1. Faculty assignment updated successfully.
2. Success message displayed.

4.4.95 Unsuccessful Edit Course Section - Faculty - Time Conflict

Use Case: 4.4.95 Unsuccessful Edit Course Section - Faculty - Time Conflict
Actors: Administrator
Initial Condition:
1. Faculty has conflicting scheduled time.
Basic Flow:
1. Attempt to assign faculty.
2. System checks timetable.
3. Conflict detected.
4. Update blocked.
Exit Condition:
1. Faculty not updated.
2. Error displayed: Faculty has a time conflict.

4.4.96 Unsuccessful Edit Course Section - Faculty Department

Use Case: 4.4.96 Unsuccessful Edit Course Section - Faculty Department
Actors: Administrator
Initial Condition:
1. Faculty does not belong to course department.
Basic Flow:
1. Attempt to assign faculty.
2. System checks department alignment.
3. Conflict detected.
4. Update blocked.
Exit Condition:
1. Faculty not updated.
2. Error displayed: Faculty not in course department.

4.4.97 Unsuccessful Edit Course Section - Faculty

Use Case: 4.4.97 Unsuccessful Edit Course Section - Faculty
Actors: Administrator
Initial Condition:
1. Faculty status does not match course assignment.
Basic Flow:
1. Attempt to assign faculty.
2. System validates faculty status.
3. Conflict detected.
4. Update blocked.
Exit Condition:
1. Faculty not updated.
2. Error displayed: Faculty status conflicts with course assignment.

4.4.98 Edit Course Section - Time Slot

Use Case: 4.4.98 Edit Course Section - Time Slot
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Course section exists.
Basic Flow:
1. Navigate to Edit Time Slot.
2. Enter new time slot.
3. Validate conflicts.
4. Save changes.
Exit Condition:
1. Time slot updated successfully.
2. Success message displayed.

4.4.99 Unsuccessful Edit Course Section - Time Slot - Room

Use Case: 4.4.99 Unsuccessful Edit Course Section - Time Slot - Room
Actors: Administrator
Initial Condition:
1. Selected room already assigned at new time.
Basic Flow:
1. Attempt to update time slot.
2. System checks room availability.
3. Conflict detected.
4. Update blocked.
Exit Condition:
1. Time slot not updated.
2. Error displayed: Room conflict detected at this time slot.

4.4.100 Unsuccessful Edit Course Section - Time Slot - Faculty

Use Case: 4.4.100 Unsuccessful Edit Course Section - Time Slot - Faculty
Actors: Administrator
Initial Condition:
1. Faculty has another course at new time.
Basic Flow:
1. Attempt to update time slot.
2. System checks faculty schedule.
3. Conflict detected.
4. Update blocked.
Exit Condition:
1. Time slot not updated.
2. Error displayed: Faculty conflict detected at this time slot.

4.4.101 Edit Course Section - Building/Room

Use Case: 4.4.101 Edit Course Section - Building/Room
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Course section exists.
Basic Flow:
1. Navigate to Edit Building/Room.
2. Enter new room assignment.
3. Validate conflict.
4. Save changes.
Exit Condition:
1. Room assignment updated successfully.
2. Success message displayed.

4.4.102 Unsuccessful Edit Course Section - Building/Room - Time

Use Case: 4.4.102 Unsuccessful Edit Course Section - Building/Room - Time
Actors: Administrator
Initial Condition:
1. Room is already booked for another course at the selected time.
Basic Flow:
1. Attempt to update room.
2. System checks availability.
3. Conflict detected.
4. Update blocked.
Exit Condition:
1. Room not updated.
2. Error displayed: Time conflict detected for room assignment.

4.4.103 Edit Course Section - Available Seats

Use Case: 4.4.103 Edit Course Section - Available Seats
Actors: Administrator
Initial Condition:
1. Room is already booked for another course at the selected time.
2. Course section exists.
Basic Flow:
1. Navigate to Edit Available Seats.
2. Enter new available seats (>= current enrollment).
3. Save changes.
Exit Condition:
1. Available seats updated successfully.
2. Success message displayed.

4.4.104 Unsuccessful Edit Course Section - Available Seats

Use Case: 4.4.104 Unsuccessful Edit Course Section - Available Seats
Actors: Administrator
Initial Condition:
1. Administrator is logged in.
2. New seat count < current enrolled students.
Basic Flow:
1. Attempt to update available seats.
2. System validates against current enrollment.
3. Conflict detected.
4. Update blocked.
Exit Condition:
1. Available seats not updated.
2. Error displayed: Available seats cannot be less than current enrollment.

4.4.105 Cancel Course Section

Use Case: 4.4.105 Cancel Course Section
Actors: Administrator
Initial Condition:
1. Administrator is logged in.
2. Course section exists.
3. Cancellation period is active.
Basic Flow:
1. Navigate to Course Section Cancel.
2. Confirm cancellation.
3. System processes cancellation.
Exit Condition:
1. Course section cancelled successfully.
2. Success message displayed.

4.4.106 Unsuccessful Cancel Course Section

Use Case: 4.4.106 Unsuccessful Cancel Course Section
Actors: Administrator
Initial Condition:
1. Cancellation period is closed.
Basic Flow:
1. Navigate to Course Section Cancel.
2. Attempt to cancel.
3. System checks time window.
4. Outside allowed period detected.
5. Cancellation blocked.
Exit Condition:
1. Course section not cancelled.
2. Error displayed: Cannot cancel course section outside allowed time window.

4.4.107 Assign Faculty to Departments and Course Sections

Use Case: 4.4.107 Assign Faculty to Departments and Course Sections
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Faculty is available and qualified.
Basic Flow:
1. Navigate to Faculty Assignment.
2. Select faculty member.
3. Assign to department and/or course sections.
4. Validate availability and qualifications.
5. Save assignment.
Exit Condition:
1. Faculty successfully assigned.
2. Success message displayed.

4.4.108 Unsuccessful Assign Faculty - Faculty Not

Use Case: 4.4.108 Unsuccessful Assign Faculty - Faculty Not
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Faculty is not qualified for the course or is unavailable.
Basic Flow:
1. Navigate to Faculty Assignment.
2. Select faculty member.
3. Attempt to assign to department or course sections.
4. System checks faculty qualifications and availability.
5. Assignment blocked due to conflict.
Exit Condition:
1. Faculty not assigned.
2. Error displayed: Faculty is not qualified or unavailable for this assignment.

4.4.109 Unsuccessful Assign Faculty - Department Mismatch

Use Case: 4.4.109 Unsuccessful Assign Faculty - Department Mismatch
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Faculty belongs to a different department than the course/section.
Basic Flow:
1. Navigate to Faculty Assignment.
2. Select faculty member.
3. Attempt to assign to department/course sections.
4. System checks department alignment.
5. Assignment blocked.
Exit Condition:
1. Faculty not assigned.
2. Error displayed: Faculty does not belong to the selected department.

4.4.110 Remove Faculty from Course Section

Use Case: 4.4.110 Remove Faculty from Course Section
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Faculty Assignment Course Sections.
2. Select course section with assigned faculty.
3. Remove faculty assignment.
4. Save changes.
Exit Condition:
1. Faculty successfully removed from course section.

4.4.111 Unsuccessful Remove Faculty - Active Course Section with

Use Case: 4.4.111 Unsuccessful Remove Faculty - Active Course Section with
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Faculty is currently assigned to a course section that has enrolled students.
Basic Flow:
1. Navigate to Faculty Assignment Course Sections.
2. Select course section with assigned faculty.
3. Attempt to remove faculty assignment.
4. System checks for enrolled students in the course section.
5. System detects active enrollment and blocks removal.
Exit Condition:
1. Faculty not removed.
2. Error displayed: Cannot remove faculty: course section has active students enrolled.

4.4.112 Create a New Course

Use Case: 4.4.112 Create a New Course
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select create new program/department/course/major/minor/prerequisite.
3. Enter details and save.
4. System validates and creates record.
Exit Condition:
1. New entity created successfully.

4.4.113 Unsuccessful Create a New Course

Use Case: 4.4.113 Unsuccessful Create a New Course
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Attempted new entity may already exist or have invalid details.
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select create new program/department/course/major/minor/prerequisite.
3. Enter details and attempt to save.
4. System validates data.
5. System detects either:
6. Duplicate record exists.
7. Required fields missing or invalid.
Exit Condition:
1. New entity not created.
2. Error displayed: Cannot create record: duplicate or invalid data.

4.4.114 Create New Department

Use Case: 4.4.114 Create New Department
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Department Management Add Department.
2. Enter department details (name, code, description).
3. Save department.
Exit Condition:
1. Department created successfully.

4.4.115 Unsuccessful Create a New Department

Use Case: 4.4.115 Unsuccessful Create a New Department
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Attempted new entity may already exist or have invalid details.
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select create new program/department/course/major/minor/prerequisite.
3. Enter details and attempt to save.
4. System validates data.
5. System detects either:
6. Duplicate record exists.
7. Required fields missing or invalid.
Exit Condition:
1. New entity not created.
2. Error displayed: Cannot create record: duplicate or invalid data.

4.4.116 Create New Program

Use Case: 4.4.116 Create New Program
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Program Management Add Program.
2. Enter program details (name, type: UG/PG, requirements).
3. Save new program.
Exit Condition:
1. Program created successfully.

4.4.117 Unsuccessful Create a New Program

Use Case: 4.4.117 Unsuccessful Create a New Program
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Attempted new entity may already exist or have invalid details.
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select create new program/department/course/major/minor/prerequisite.
3. Enter details and attempt to save.
4. System validates data.
5. System detects either:
6. Duplicate record exists.
7. Required fields missing or invalid.
Exit Condition:
1. New entity not created.
2. Error displayed: Cannot create record: duplicate or invalid data.

4.4.118 Create Prerequisites

Use Case: 4.4.118 Create Prerequisites
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Course Catalog Prerequisites Add.
2. Select course.
3. Add required prerequisite courses.
4. Save prerequisites.
Exit Condition:
1. Prerequisites added.

4.4.119 Unsuccessful Create a New Prerequisite

Use Case: 4.4.119 Unsuccessful Create a New Prerequisite
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Attempted new entity may already exist or have invalid details.
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select create new program/department/course/major/minor/prerequisite.
3. Enter details and attempt to save.
4. System validates data.
5. System detects either:
6. Duplicate record exists.
7. Required fields missing or invalid.
Exit Condition:
1. New entity not created.
2. Error displayed: Cannot create record: duplicate or invalid data.

4.4.120 Update Course Information

Use Case: 4.4.120 Update Course Information
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select an existing entry.
3. Edit information (requirements, details, etc.).
4. Save changes.
Exit Condition:
1. Program/department/course/major/minor/prerequisite updated successfully.

4.4.121 Unsuccessful Update Course Information

Use Case: 4.4.121 Unsuccessful Update Course Information
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Existing entity is selected for update.
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select an existing entry.
3. Edit information (requirements, details, etc.).
4. Attempt to save changes.
5. System validates data.
6. System detects either:
7. Invalid input (missing required fields, wrong format).
8. Duplicate entry already exists in the system.
Exit Condition:
1. Changes not saved.
2. Error displayed: Cannot update record: invalid or duplicate data.

4.4.122 Update Department

Use Case: 4.4.122 Update Department
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select an existing entry.
3. Edit information (requirements, details, etc.).
4. Save changes.
Exit Condition:
1. Program/department/course/major/minor/prerequisite updated successfully.

4.4.123 Unsuccessful Update Department

Use Case: 4.4.123 Unsuccessful Update Department
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Existing entity is selected for update.
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select an existing entry.
3. Edit information (requirements, details, etc.).
4. Attempt to save changes.
5. System validates data.
6. System detects either:
7. Invalid input (missing required fields, wrong format).
8. Duplicate entry already exists in the system.
Exit Condition:
1. Changes not saved.
2. Error displayed: Cannot update record: invalid or duplicate data.

4.4.124 Update Program Major/Minor

Use Case: 4.4.124 Update Program Major/Minor
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Program Management Programs.
2. Select program.
3. Edit details.
4. Save changes.
Exit Condition:
1. Program updated.

4.4.125 Unsuccessful Update Program Major/Minor

Use Case: 4.4.125 Unsuccessful Update Program Major/Minor
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Program Management Programs.
2. Select program.
3. Edit details.
4. User enters Invalid Data or Catalog is Locked.
5. Error Displays on Screen.
Exit Condition:
1. Program is not updated.

4.4.126 Update Prerequisites

Use Case: 4.4.126 Update Prerequisites
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Course Catalog Prerequisites.
2. Select course.
3. Update prerequisites list.
4. Save changes.
Exit Condition:
1. Prerequisites updated.

4.4.127 Unsuccessful Update Prerequisites - Effects Current Students

Use Case: 4.4.127 Unsuccessful Update Prerequisites - Effects Current Students
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Course Catalog Prerequisites.
2. Select course.
3. Update prerequisites list.
4. Error is Displayed Would affect Current Students.
Exit Condition:
1. Prerequisites are not updated.

4.4.128 Update Course Catalog Information

Use Case: 4.4.128 Update Course Catalog Information
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select an existing entry.
3. Edit information (requirements, details, etc.).
4. Save changes.
Exit Condition:
1. Program/department/course/major/minor/prerequisite updated successfully.

4.4.129 Unsuccessful Update Course Catalog Information

Use Case: 4.4.129 Unsuccessful Update Course Catalog Information
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Existing entity is selected for update.
Basic Flow:
1. Navigate to Catalog/Program Management.
2. Select an existing entry.
3. Edit information (requirements, details, etc.).
4. Attempt to save changes.
5. System validates data.
6. System detects either:
7. Invalid input (missing required fields, wrong format).
8. Duplicate entry already exists in the system.
Exit Condition:
1. Changes not saved.
2. Error displayed: Cannot update record: invalid or duplicate data.

4.4.130 Assign Building Rooms

Use Case: 4.4.130 Assign Building Rooms
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Room is available for assignment.
Basic Flow:
1. Navigate to Scheduling Rooms.
2. Select course section and assign to an available room.
3. Validate room availability and time slot.
4. Save assignment.
Exit Condition:
1. Room assigned successfully.
2. Success message displayed.

4.4.131 Unsuccessful Assign Building Rooms - Time Conflict

Use Case: 4.4.131 Unsuccessful Assign Building Rooms - Time Conflict
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Room has a time conflict with another assigned course.
Basic Flow:
1. Navigate to Scheduling Rooms.
2. Attempt to assign room to course section.
3. System validates time slot.
4. Conflict detected.
5. Assignment blocked.
Exit Condition:
1. Room not assigned.
2. Error displayed: Time conflict detected for the selected room.

4.4.132 Unsuccessful Assign Building Rooms - Room Not Available

Use Case: 4.4.132 Unsuccessful Assign Building Rooms - Room Not Available
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Selected room is unavailable.
Basic Flow:
1. Navigate to Scheduling Rooms.
2. Attempt to assign room to course section.
3. System checks room availability.
4. Room unavailable.
5. Assignment blocked.
Exit Condition:
1. Room not assigned.
2. Error displayed: Selected room is not available.

4.4.133 Assign Time Slots

Use Case: 4.4.133 Assign Time Slots
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Time slot is available and valid.
Basic Flow:
1. Navigate to Scheduling Time Slots.
2. Select course section.
3. Assign available time slot.
4. Validate against existing schedules.
5. Save assignment.
Exit Condition:
1. Time slot assigned successfully.
2. Success message displayed.

4.4.134 Unsuccessful Assign Time Slots - Conflict with Existing

Use Case: 4.4.134 Unsuccessful Assign Time Slots - Conflict with Existing
Actors: Administrator
Initial Condition:
1. Administrator is logged in.
2. Selected time slot conflicts with another course or faculty assignment.
Basic Flow:
1. Navigate to Scheduling Time Slots.
2. Select course section.
3. Attempt to assign time slot.
4. System validates schedule.
5. Conflict detects.
6. Assignment blocked.
Exit Condition:
1. Time slot not assigned.
2. Error displayed: Conflict with existing schedule detected.

4.4.135 Unsuccessful Assign Time Slots - Invalid Time Slot

Use Case: 4.4.135 Unsuccessful Assign Time Slots - Invalid Time Slot
Actors: Administrator
Initial Condition:
1. Administrator is logged in.
2. Selected time slot is not valid (outside allowed hours or format incorrect).
Basic Flow:
1. Navigate to Scheduling Time Slots.
2. Attempt to assign invalid time slot.
3. System validates input.
4. Invalid time slot detected.
5. Assignment blocked.
Exit Condition:
1. Time slot not assigned.
2. Error displayed: Invalid time slot selected.

4.4.136 Assign Available Seats

Use Case: 4.4.136 Assign Available Seats
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Selected course section exists.
3. Seat count is valid (>= current enrollment and <= room capacity).
Basic Flow:
1. Navigate to Scheduling Course Sections.
2. Select section.
3. Assign or update available seat count.
4. Validate against current enrollment and room capacity.
5. Save changes.
Exit Condition:
1. Available seats updated successfully.
2. Success message displayed.

4.4.137 Unsuccessful Assign Available Seats - Below Current Enrollment

Use Case: 4.4.137 Unsuccessful Assign Available Seats - Below Current Enrollment
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Selected course section exists.
3. Attempted seat count < current enrolled students.
Basic Flow:
1. Navigate to Scheduling Course Sections.
2. Select section.
3. Enter seat count below current enrollment.
4. System validates against current enrollment.
5. Update blocked.
Exit Condition:
1. Available seats not updated.
2. Error displayed: Seat count cannot be less than current enrollment.

4.4.138 Unsuccessful Assign Available Seats - Exceeds Room Capacity

Use Case: 4.4.138 Unsuccessful Assign Available Seats - Exceeds Room Capacity
Actors: Administrator
Initial Condition:
1. Administrator is logged in with Admin Credentials.
2. Selected course section exists.
3. Attempted seat count > assigned room capacity.
Basic Flow:
1. Navigate to Scheduling Course Sections.
2. Select section.
3. Enter seat count exceeding room capacity.
4. System validates against room capacity.
5. Update blocked.
Exit Condition:
1. Available seats not updated.

----------------------------------------
4.5 Statistics Office

4.5.1 View Personal Account Information

Use Case: 4.5.1 View Personal Account Information
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials
Basic Flow:
1. From the Statistics Office home page, navigate to Profile.
2. View Statistics Office Account Information.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.5.2 Update Personal Account Information

Use Case: 4.5.2 Update Personal Account Information
Actors: Statistics Office
Initial Condition:
1. User logs in with Statistics Office credentials
Basic Flow:
1. From the Statistics Office home page, navigate to the Profile.
2. Select to update information.
3. Make necessary updates to information.
4. Save updates.
Exit Condition:
1. Statistics Office user makes updates to personal account information.
2. Statistics Office user logs out of account.
3. Web browser closes.

4.5.3 Change Personal Account Password

Use Case: 4.5.3 Change Personal Account Password
Actors: Statistics Office
Initial Condition:
1. User logs in with Statistics Office credentials
Basic Flow:
1. From the Statistics Office home page, navigate to the Account.
2. Make necessary changes to password.
3. Save updates.
Exit Condition:
1. Statistics Office user makes change to personal password.
2. Statistics Office user logs out of account.
3. Web browser closes.

4.5.4 Unsuccessfully Change an Account Password

Use Case: 4.5.4 Unsuccessfully Change an Account Password
Actors: Statistics Office
Initial Condition:
1. The Statistics Office attempts to change a password to a new one that does not meet the requirements outlined for passwords.
Basic Flow:
1. The Statistics Office tries to change a user's password.
2. The system rejects any change to this information and informs the Statistics Office that the password does not meet the necessary requirements.
Exit Condition:
1. The Statistics Office leaves the website.
2. The Statistics Office tries again with another combination of characters.

4.5.5 Unsuccessfully Change a Account Password

Use Case: 4.5.5 Unsuccessfully Change a Account Password
Actors: Statistics Office
Initial Condition:
1. The Statistics Office attempts to change a password to the same password.
Basic Flow:
1. The Statistics Office tries to change a user's password to the same password.
2. The system rejects any change to this information and informs the Statistics Office that the password is the same as current password.
Exit Condition:
1. The Statistics Office leaves the website.
2. The Statistics Office tries again with another combination of characters.

4.5.6 View Master Schedule

Use Case: 4.5.6 View Master Schedule
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials.
Basic Flow:
1. From the Statistics Office Home Page, Navigate to Statistics Office Console.
2. Navigate to Master Schedule.
3. View Master Schedule.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.5.7 View Available Majors/Minors

Use Case: 4.5.7 View Available Majors/Minors
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials.
Basic Flow:
1. From the Statistics Office Home Page, Navigate to Statistics Office Console.
2. Navigate to Course Catalog.
3. Navigate to Programs.
4. View Programs.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.5.8 View Majors/Minors Requirements

Use Case: 4.5.8 View Majors/Minors Requirements
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials.
Basic Flow:
1. From the Statistics Office Home Page, Navigate to Statistics Office Console.
2. Navigate to Course Catalog.
3. Navigate to Programs.
4. View Programs.
5. Select a Major/Minor.
6. View Major/Minor Requirements.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.5.9 View a Course's Prerequisites

Use Case: 4.5.9 View a Course's Prerequisites
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials.
Basic Flow:
1. From the Statistics Office Home Page, Navigate to Statistics Office Console.
2. Navigate to Course Catalog.
3. Navigate to Courses.
4. Select a Course to view.
5. View Prerequisites.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.5.10 Search Department

Use Case: 4.5.10 Search Department
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials.
Basic Flow:
1. From the Statistics Office Home Page, Navigate to Statistics Office Console.
2. Navigate to Course Catalog.
3. Navigate to Departments.
4. Search Departments by School.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.5.11 Search Master Schedule by Period

Use Case: 4.5.11 Search Master Schedule by Period
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials.
Basic Flow:
1. From the Statistics Office Home Page, Navigate to Statistics Office Console.
2. Navigate to Master Schedule.
3. View Master Schedule.
4. Select one or more Periods from Pick List.
5. View Course Sections available.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.5.12 Search Master Schedule by Day

Use Case: 4.5.12 Search Master Schedule by Day
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials.
Basic Flow:
1. From the Statistics Office Home Page, Navigate to Statistics Office Console.
2. Navigate to Master Schedule.
3. View Master Schedule.
4. Select one or more Days from Pick List.
5. View Course Sections available.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.5.13 Search Master Schedule by Professor

Use Case: 4.5.13 Search Master Schedule by Professor
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials.
Basic Flow:
1. From the Statistics Office Home Page, Navigate to Statistics Office Console.
2. Navigate to Master Schedule.
3. View Master Schedule.
4. Type Faculty in search.
5. View Course Sections available taught by that Faculty.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.5.14 Search Master Schedule by Course Name

Use Case: 4.5.14 Search Master Schedule by Course Name
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials.
Basic Flow:
1. From the Statistics Office Home Page, Navigate to Statistics Office Console.
2. Navigate to Master Schedule.
3. View Master Schedule.
4. Type Course Name in search.
5. View Course Sections available of that Course Name.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

4.5.15 View Statistics

Use Case: 4.5.15 View Statistics
Actors: Statistics Office
Initial Condition:
1. User logs in to website using Statistics Office credentials.
Basic Flow:
1. From the Statistics Office Home Page, Navigate to Statistics.
2. View ANONYMOUS/UNIDENTIFYING statistics.
Exit Condition:
1. User logs out of account.
2. Web browser is closed.

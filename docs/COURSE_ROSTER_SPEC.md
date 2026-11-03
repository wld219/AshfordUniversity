# Course Roster Management — Functional Requirement Specification

## 1. Purpose
Ensure course rosters are strictly mapped to assigned sections and that faculty can only view/access rosters for sections officially assigned to them.

## 2. Data Relationships
- `Course(course_ID, dept_ID, course_Name, course_Credits, course_Type)`
- `Course_Section(CRN PK, course_ID FK, section_No, faculty_ID FK, time_Slot_ID FK, lecture_ID FK, lab_ID FK, semester_ID FK, available_Seats, is_Cancelled)`
- `Enrollment(student_ID FK, CRN FK, semester_ID FK, Grade)` — composite PK (student_ID, CRN)
- `Faculty(faculty_ID PK, faculty_Type, ...)`
- `User(user_ID PK, first_Name, last_Name, ...)`
- `Faculty_Department(faculty_ID FK, dept_ID FK, ...)`

Roster for a section = all `Enrollment` rows where `Enrollment.CRN = Course_Section.CRN`, joined to `User` for student names.

## 3. Role-Based Access Control
- **Faculty**: may view rosters only for sections where `Course_Section.faculty_ID = session faculty_ID` and `is_Cancelled = 0`.
- **Admin**: full access to all rosters (read; write via separate actions).
- **StatStaff**: read-only aggregate access (no PII) — cannot view individual rosters.
- **Student**: no roster access.

## 4. Permission Logic
- Every roster query MUST include `AND Course_Section.faculty_ID = :facultyID` (server-side, never client-supplied).
- Section selection (`crn` GET param) is validated: the section must belong to the logged-in faculty; otherwise return 403/empty roster.
- Roster writes (grades, attendance) require the same ownership check plus CSRF token and write access.
- Admin overrides are logged in `Audit_Log`.

## 5. Security Constraints
- Server-side enforcement only; never trust client-supplied faculty/section identifiers.
- Prepared statements for all SQL; output escaped via `e()`.
- Session-based identity (`$_SESSION['user_id']`); role check via `require_role(['Faculty'])`.
- No roster data in client-side caches or URLs beyond the validated CRN.
- Audit trail for any grade/attendance mutation.

## 6. Functional Requirements
- FR-1: Faculty roster page lists only sections assigned to the logged-in faculty.
- FR-2: Selecting a section displays enrolled students sorted by last name, first name.
- FR-3: Cross-faculty access attempt returns no data and is logged.
- FR-4: Roster includes student ID, name, grade, attendance status.
- FR-5: Grade/attendance updates are transactional and ownership-checked.

## 7. Acceptance Criteria
- A faculty member requesting another faculty's section CRN receives an empty roster (no PII leak).
- Roster query contains the faculty ownership predicate.
- Admin can view any roster; StatStaff cannot view individual rosters.

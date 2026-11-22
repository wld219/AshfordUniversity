# Faculty consistency repair — final verification

Project: Ashford-TestV2  
Database: `ashford_waleed`  
Verified: 2026-11-20

## Database repair verification

- `Faculty`: 422 records; stored types: 322 Full-Time and 100 Part-Time.
- `Full_Time_Faculty`: 322; `Part_Time_Faculty`: 100.
- All 422 Faculty records have exactly one subtype row matching `Faculty.faculty_Type`; wrong, missing, or duplicate subtype memberships: 0.
- A fresh connection sees the repaired rows. No active InnoDB transaction was visible in the follow-up check. I did not commit or roll back another user's connection.
- Faculty foreign-key orphan checks: 0 across the checked dependent tables.
- All 422 Faculty rows have a Faculty-role `User`. There are 350 Faculty `Login` rows and 72 Faculty rows without Login rows (IDs 400011–400082). Those 72 cannot be verified through password login and were not provisioned.

## Assignment and workload audit

Workload is the sum of actual course credits for unique, active sections in each semester. Canceled sections are excluded. The calculated 12-credit FT/PT threshold is kept separate from the stored, semester-independent employment field.

| Semester | Active sections | Calculated workload FT | Calculated workload PT | Faculty with zero sections | Stored FT below 12 credits | Stored PT at 12+ credits |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| Fall 2026 (ID 1) | 197 | 1 | 421 | 227 | 321 | 0 |
| Spring 2027 (ID 2) | 198 | 0 | 422 | 278 | 322 | 0 |

Zero assignments are not by themselves proof of missing work: the schema and available project requirements do not define a minimum teaching load for every Full-Time employee. All active sections have an instructor and linked room. No confirmed active assignment falls outside the instructor's declared department affiliations. Specialty is free text, with no qualification-to-course mapping table, so specialty/course-level eligibility cannot be conclusively certified from this database.

### Confirmed section-limit exception

The existing application rule allows at most two active sections per Full-Time faculty member per semester and one per Part-Time faculty member. Fall 2026 has one exception:

| Faculty | Stored type | Current sections | Credits | Sections |
| --- | --- | ---: | ---: | --- |
| 200072 — Gabriel Thomas | Full-Time | 3 | 12 | CRN 140025 (CS1001), CRN 140026 (CS1002), CRN 140027 (CS1004) |

No Spring 2027 section-limit exceptions were found. No faculty or classroom time-overlap pairs were found in either semester.

No assignment was changed. There are no unstaffed active sections to assign. A possible review candidate for reducing the confirmed exception is reassignment of Fall CRN 140027 (CS1004, Database Systems, 4 credits, 7 enrolled students) from faculty 200072 to faculty 200172 (Isaiah Martinez): 200172 has a valid Faculty login, Computer Engineering specialty, a Computer Science department affiliation, zero Fall sections, and passes the existing department/capacity/time-conflict checks. The existing CRN, students, room, and meeting time would remain unchanged. This is only a candidate for academic and administrative approval: the database has no formal qualification mapping, and roster/attendance authorization would transfer with the instructor. No change was applied.

## Other existing data issues

- Spring 2027 has 116 student schedule-conflict pairs affecting 19 students. Fall 2026 has none. These were not changed.
- Spring CRN 20012 (CS2007) and CRN 20069 (BU1002) each have 11 enrolled students in a linked room of capacity 10.
- Ten Fall sections have stale `available_Seats` counters; no Fall room over-capacity was identified.
- Spring has 10 Enrollment rows linked to canceled sections. They were preserved and excluded from active-section faculty views.
- Spring waitlist has one row for student 100002 on full CRN 20001; the other active CS1001 Spring section is also full.

Detailed supporting inventories are in `faculty_assignment_inventory_20261120.csv`, `faculty_employment_workload_mismatches_20261120.csv`, `faculty_without_active_assignments_20261120.csv`, and `faculty_section_capacity_review_20261120.csv`.

## Application change and verification

`faculty/dashboard.php` now labels `Faculty.faculty_Type` as **Employment Type** and displays it independently from calculated semester Teaching Credits and Assigned Sections. Workload continues to come from active Course_Section/Course records; `Faculty.no_of_Classes` is not used as semester workload. The page keeps its zero-section empty state and uses the authenticated session Faculty ID.

Read-only session-fixture HTTP rendering against the live DB, with a corrected router that preserves the application's relative include convention:

- 422/422 Faculty Overview renders matched the database for identity, stored employment type, departments, specialty, workload metrics, and Fall schedule rows.
- 422/422 Faculty My Schedule renders passed.
- 844/844 Faculty roster semester views (422 Faculty × 2 semesters) showed only the instructor's active assigned sections and correct empty states.
- 395/395 active-section roster pages matched the current same-CRN/same-semester Enrollment student membership.
- Student-to-Faculty and Faculty-to-Student role attempts returned 403; unauthenticated Faculty Overview redirected to `/index.php` (302).
- PHP runtime warning/error scan: none in the corrected render run.
- `php -l faculty/dashboard.php`: passed.

These are session-fixture tests, not password-login tests. They do not verify passwords for the 350 accounts; the 72 records without Login rows cannot authenticate. No POST/write paths were called, and no database records were changed.

## Backup

Before the PHP adjustment, a full `ashford_waleed` dump was created and verified at `backups/faculty-consistency-20261120/ashford_waleed_pre_faculty_overview_update.sql`. The pre-edit PHP copy is `backups/faculty-consistency-20261120/dashboard.php.pre-employment-type-update`.

## Status

- Subtype repair: **PASS** (current state consistent; not rerun).
- Faculty portal read-only rendering and roster consistency: **PASS** in session-fixture scope.
- Employment type vs. workload display separation: **PASS**.
- All Faculty password logins: **NOT VERIFIED**.
- Specialty-based qualification validation: **UNVERIFIABLE** with current schema.
- Automatic teaching assignment corrections: **NOT APPLIED**; one candidate needs approval.
- Overall database readiness: **NOT FULLY CLEAR** because of unresolved Spring student conflicts, two Spring room-capacity overruns, stale Fall seat counters, missing Login rows, and the Fall section-limit exception.

## Student-protection snapshot comparison

The pre-change dump was restored to the isolated schema `ashford_waleed_faculty_validation_20261120` for regression testing. The live database was compared with that snapshot across 26 student-linked tables (109,058 rows). `Student`, Student-linked `User` and `Login`, Student_History, Attendance, holds, major/minor relationships, grade tables, and other checked student-linked tables match the snapshot. Two tables differ by additions only:

- `Enrollment`: two live Spring 2027 rows for student 100013, CRNs 20021 and 20132, both ungraded.
- `Waitlist`: one live row, ID 2, student 100013, Spring 2027 CRN 20066, position 1 (created 2026-11-20 12:58:40).

No corresponding rows were missing from the live database. These additions were present in the live database during comparison but absent from the earlier 12:43 backup. All portal checks were GET-only; no write path was called. Their provenance cannot be established by this audit, so they are reported as external/unattributed changes rather than attributed to the audit. The Student row count, Student user rows, and Student Login rows are each 1,620 and match the restored snapshot exactly.

Against the restored copy, 12 read-only Student page renders passed: Dashboard, My Schedule, Master Schedule, and Course Registration for students 100013, 100002, and 100120 (a Fall 2026 zero-enrollment case). A Faculty Overview render for 200072 also passed. All 13 responses were HTTP 200 with no detected PHP/SQL runtime errors. This is representative regression coverage, not an exhaustive render of every Student account.

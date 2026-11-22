# Full Academic Data Correction — Execution Report

Database: `ashford_waleed`  
Date: 2026-11-19  
Mode: targeted transactional corrections with precondition checks.

## Backup

A new full database dump was created before updates and verified with `gzip -t` and a full gzip read. It contains 75 table definitions and 71 insert statements, is mode 0600, and has SHA-256 `d2e17892e2c162d5700668dc7ad40289620ed1834f958777b566d53068f0c042`.

## Applied corrections

- **139 `Student.major_ID` rows** changed from a catalog program explicitly labeled Minor to the sole non-minor `Student_Major` candidate. Each candidate was rechecked live and had published `Major_Requirement` rows. Existing `Student_Major` and `Student_Minor` rows were preserved.
- **3 `Course_Section.course_ID` rows** changed: CRN 30005 `CS1001 → BU1001`, CRN 30015 `EG2009 → ED2009`, and CRN 30020 `EN3010 → HS3010`. All Student_History and Faculty_History course/term values on each CRN agreed; there were no Enrollment, Attendance, demo-enrollment, Grade_Exception, Section_Cancellation, or Waitlist rows. The section semester IDs were already correct and were not changed.
- **142 database rows changed total.** No `Student_History`, `Faculty_History`, `Enrollment`, grades, Attendance, schema, or UI rows/files were changed.

Per-row original and new values are in `before_after_changes.csv`. `planned_changes.csv` records both applied and deferred proposals.

## Before and after validation

- Student population remained 1,620 (1,220 Undergraduate; 400 Graduate).
- Undergraduate major displays flagged for review: 380 before and 380 after; the composition changed from 139 minor-labeled stored values + 359 conflicts + 19 stored-only + 2 minor-only records to 359 conflicts + 19 stored-only + 2 minor-only records.
- Student program display mismatches across pages: 0 after correction. Student/User/Login relationship issues: 0.
- Target-semester course sets agreed across student-facing paths for all 1,620 students; schedule-page omissions: 0.
- Student_History course_ID mismatches declined from 436 to 408 rows. Semester_ID mismatches remain 48. These history rows were not edited.
- Fall 2026 active enrollments: 1,619; duplicate groups 34 (53 extra rows), prerequisite failures 522, prior-pass retakes 69, course-level mismatches 237; credit-limit violations 0; timetable conflicts 0.
- Spring 2027 active enrollments: 1,861; duplicate groups 136 (245 extra rows), prerequisite failures 729, prior-pass retakes 322, course-level mismatches 460; 28 over-credit students (1,288 excess credits); 116 timetable conflict pairs across 19 students.

A per-validation pass/flagged count is in `validation_summary.csv`; the complete after-audit dataset and summary are linked from the parent audit folder.

## Deferred for academic or registrar review

- 359 non-minor primary/declaration conflicts have no primary marker or effective-date evidence.
- 19 stored-only primary values have no Student_Major row; they remain visible with a review flag.
- Students 100001 and 100632 remain unresolved because only minor-labeled program records support their major fields. No major was invented.
- 37 of the 40 historical CRNs proposed previously have mixed Student_History course/term values on the same section. Their section metadata was left unchanged. The 48 semester mismatches are among these cases. The 100 active Fall 2026 mismatches are also unchanged because they have Enrollment/Attendance dependencies.
- Ten Spring 2027 enrollments remain on canceled CRN 20077. Three have grades and attendance; all ten were left in place.
- Duplicate registrations, prerequisite exceptions, prior-pass retakes, course-level mismatches, Spring 2027 overloads, and Spring schedule conflicts were not changed. The schedule conflicts have posted grades and attendance; duplicate groups without protected rows do not establish which section to retain.
- For CRNs 30005, 30015, and 30020, Faculty_History course/term values corroborate the updated course values, but its faculty_ID differs from Course_Section.faculty_ID. No faculty assignment was changed; that relationship remains a separate review item.

Detailed unresolved student program IDs are in `reports/student_consistency_audit_20261119_program_resolver/unresolved_programs.csv`. Remaining Student_History mismatches are in `history_mismatches.csv`; canceled-section registrations are in `canceled_enrollments.csv`.

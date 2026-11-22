# Academic validation of proposed schedule corrections

Database: `ashford_waleed`  
Generated: 2026-11-19T09:15:02-05:00  
Scope: read-only validation of the current maximum-retention conflict proposal for Fall 2026 and Spring 2027.

## Findings

- Proposed removal rows reviewed: **166** across **81** students.
- Same-course replacements recommended by a capacity-aware, schedule-checked allocation: **6**. Recommendations are not reservations and require revalidation immediately before any approved transfer.
- Proposed rows with grade/dependent-record or registration blockers: **101** (see row-level manual review column).
- Proposed rows failing the current prerequisite rule: **85**.
- Stored primary-major links whose `Major.major_Name` contains “Minor”: **17 rows**; this is a data classification warning, not grounds to rewrite student declarations.
- Courses mapped to declared major/minor requirements: **47**.
- Rows where the enrolled course appears in a not-yet-satisfied major/minor checklist: **33**.
- General Education requirement mapping: **not configured in this database**. No `General_Education_Requirement` table was found.
- Corequisite/concurrent enrollment rules: **not configured**; no `Course_Corequisite` table was found. The app's current prerequisite validator requires a qualifying `Student_History` grade and does not allow an in-progress concurrent prerequisite to satisfy that rule.
- `Room` has no explicit capacity column; candidate section `available_Seats` plus any linked Lecture seat / Lab workstation values are included in the options file. The current application uses `Course_Section.available_Seats` for seat eligibility.

## Method and limitations

- Primary major comes from `Student.major_ID`. Additional majors come from `Student_Major`, excluding duplicates of the primary major. Minors come separately from `Student_Minor`. Course checklist requirements come from `Major_Requirement` and `Minor_Requirement`; completion is evaluated using `Student_History` and existing grade comparison rules.
- Prerequisites come from `Course_Prerequisite` and are checked using the best recorded `Student_History` grade against each minimum. A prerequisite only enrolled concurrently does not pass the current application rule.
- Replacement recommendations use the same course and semester, non-canceled sections, current positive `available_Seats`, published prerequisite eligibility, and actual meeting times. They must not overlap any retained enrollment or another replacement allocated to that student. TBA/unresolved candidate schedules are not recommended as confirmed conflict-free.
- Rows with a posted final/midterm grade, Student_History, Attendance, Grade_Exception, Waitlist link, applicable hold, closed registration/drop window, or prior completed course are not automatically recommended for transfer. These need manual review to protect academic/dependent records.
- Credit/status columns show current totals, totals if all proposed drops occurred without replacements, and totals if all recommendations were accepted. Same-course replacements preserve credits and ordinarily preserve the displayed classification; no database values were changed.
- This is a planning allocation, not a transfer transaction. A section's seat count or student schedule can change before execution. No enrollment, grade, history, attendance, requirement, or capacity record was changed.

## Files

- `academic_validation.csv`: requested student/course-level report.
- `same_course_replacement_options.csv`: candidate section screening results and rejection reasons.
- Existing `conflict_pairs.csv` and `proposed_removals.csv` remain the source conflict details.

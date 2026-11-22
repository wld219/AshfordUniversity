# Student Time Conflict Audit — Fall 2026 and Spring 2027

Database: `ashford_waleed`
Generated: `2026-11-19T09:46:18-05:00`
Scope: read-only analysis of active Enrollment rows joined to Course_Section, Course, Semester, Time_Slot_Day, Time_Slot_Period, Period, and Day.

## Results

- Enrollment removals executed: **0**.
- Active enrollments analyzed: **3480**; canceled and semester-mismatched rows excluded: **10**.
- Conflict pairs: **116** across **19** student-semester pairs.
- Proposed removals in maximum-cardinality schedules: **98**; flags requiring manual review: **98**.
- Active enrollment rows with no day/period data: **0**; treated as unknown/TBA, not a conflict.

## Semester detail

| Semester | Active enrollments | Conflicting pairs | Affected student-term pairs | Proposed removals | Drop action window |
|---|---:|---:|---:|---:|---|
| Fall 2026 | 1618 | 0 | 0 | 0 | Yes |
| Spring 2027 | 1862 | 116 | 19 | 98 | Yes |

## Proposal rules and limitations

- The proposal keeps the maximum number of non-overlapping enrolled sections per student and semester. Back-to-back intervals are allowed (`start < existing end` and `existing start < end`).
- Conflicts are based on a shared day and strict interval overlap derived from the actual periods. Sections with missing day or period data are not treated as confirmed conflicts.
- Tied maximum schedules prioritize rows with grades, Student_History, attendance, grade exceptions, midterm records, or waitlist links for retention. Any proposed removal with these records is flagged for manual review.
- Enrollment has no registration timestamp, so the report cannot determine which registration was actually earlier. Available Waitlist.enrolled_At is only a partial timestamp; CRN ascending is a disclosed deterministic fallback, not a registration-time claim.
- The existing drop action also requires its semester drop window to be open, a null final Grade, and no Student_History row. The report flags rows that the application would block. Attendance and Grade_Exception dependencies are surfaced for review because the current drop action does not guard them.
- Course_Section stores one time_Slot_ID. Lecture_ID and Lab_ID identify room resources, not distinct meeting intervals; the schema cannot distinguish a separate lab timetable.

## Files

- `proposed_removals.csv`: every proposed row with course, section, semester, conflict reason, linked-record flags, and existing-drop eligibility.
- `conflict_pairs.csv`: all detected overlapping enrollment pairs and meeting interval intersections.
- `affected_student_credits.csv`: current and proposed course counts, registered credits, and FT/PT classification for affected student-term pairs.
- `summary.json`: machine-readable counts, solver details, semester statuses, and limitations.

**No enrollment rows were removed. Review and approve specific proposed removals before any execution.**

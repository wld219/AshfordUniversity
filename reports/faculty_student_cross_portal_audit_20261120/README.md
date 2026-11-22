# Faculty–Student Cross-Portal Audit — 2026-11-20

Read-only audit of `ashford_waleed`; no academic records changed.

- 350 Faculty login accounts route-rendered for Fall and Spring (700 dashboard renders); all passed semester/section-count assertions.
- 1,620 Student records checked for matching Student, User, and Login relationships; all have valid Student logins.
- Detailed discrepancy rows are in the CSV files in this folder.
- Cross-portal discrepancy CSV rows: 1246. Attendance review rows, with overlapping reason categories: 135266.
- `cross_portal_discrepancies.csv` contains Faculty ID, Student ID, semester, CRN and related CRN where applicable.
- `attendance_integrity_review.csv` is diagnostic only; no attendance records were edited.

## Key findings

- Spring 2027: 56 active CRNs assigned to 53 Faculty records without a Faculty Login; those sections have 541 enrollment rows involving 322 distinct students.
- 298 Advisor rows point to Faculty records without a Login; the Student Overview can show the Faculty User name, while Student My Advisor uses an inner Login join and reports no advisor.
- Spring 2027: 10 Enrollment rows remain on canceled CRN 20077; student and faculty active-course views exclude it.
- Active timetable overlaps: 0 Fall pairs and 116 Spring student conflict pairs. Active duplicate student/course groups: 34 Fall and 136 Spring.
- Spring 2027 has 28 Undergraduate students above 16 active registered credits.
- Attendance: 1,242 stored course IDs differ from the CRN's Course_Section course; 70,292 rows have neither a matching Enrollment nor Student_History pair; 63,290 Spring rows are future-dated as of the audit date; 442 rows reference canceled sections. Treat these as review items and preserve the records.
- Student My Schedule and Faculty My Schedule read the same CRN/semester/course section source, but Student My Schedule does not visibly print CRNs; Student Registration does.
- Course Rosters has a confirmed PHP-tag issue: the partial-loading statements are emitted as text instead of being executed, so the roster view does not render. A temporary fix was reverted to honor this read-only audit; the original PHP file is restored. Its syntax check passes, but the page remains functionally broken until an approved fix.
- No database, enrollment, grade, attendance, schema, or permission changes were made.

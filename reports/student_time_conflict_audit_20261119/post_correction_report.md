# Student schedule conflict correction — post-change validation

Database: `ashford_waleed`  
Validated: 2026-11-19T09:46:36-05:00

## Executed corrections

- Same-course section transfers: **4**.
- Safe conflict enrollment removals: **64** (including one planned transfer rejected by the live course-level rule).
- Verified registrations in unmet published major/minor requirements: **11**.
- Blocked operations: **0**.
- Verified audit log entries: **83** (72 conflict corrections plus 11 requirement-course registrations).
- Active Fall/Spring enrollments after correction: **3480**; total Enrollment rows across all semesters: **3490**. Net row change from pre-correction: **−53** (68 source removals, 4 same-course transfer destinations, 11 requirement-course additions).
- Database/schema and historical grade, attendance, and Student_History records were not modified.

## Remaining conflicts

- Fall 2026: **0** pairs across **0** students; all affected Fall schedules are now conflict-free.
- Spring 2027: **116** pairs across **19** students. The unresolved student IDs are 100505, 100506, 100507, 100508, 100509, 100510, 100517, 100518, 100519, 100520, 100521, 100522, 100529, 100531, 100532, 100533, 100534, 100546, and 100570.
- Every remaining pair is in Spring 2027. In all **116 pairs, both conflicting enrollments have posted final grades and attendance**, so they were left unchanged under the safeguards. Some also match unsatisfied major/minor requirements. Details are in `post_correction_unresolved_conflicts.csv`.

## Credits and enrollment status

Updated credits and FT/PT classification are in `post_correction_student_credits.csv` for all 85 initially affected student-semester pairs. Final classifications are **50 FT, 16 PT, and 19 Overloaded**. Eleven students who fell from FT to PT received a verified required course and returned to FT; 8 remain PT because no safe qualifying requirement section was available. Status uses the existing undergraduate categories (12–16 FT, 4–10 PT, 1–3 below minimum, 11 review, 0 not enrolled, above 16 overloaded) and the provisional 9-credit graduate threshold.

## Verification and limitations

- Each executed source row was checked after the transaction: deleted source CRNs are absent; transferred target CRNs are present; unsuccessful operations would remain unchanged. Operation checks failed: **0**.
- Registration validation was used before every section transfer and requirement-course registration; target sections were rechecked for cancellation, capacity, prerequisites, holds, credit limits, duplicates, and schedule conflicts. No new conflicts were introduced. Spring’s remaining protected pairs are unresolved pre-existing conflicts, not overlaps created by these corrections.
- All 4 same-course transfers passed the application validator. One additional planned transfer for student 100033 (SS5006, CRN 10298 → 10299) was rejected because the course is classified Graduate while the student is Undergraduate; its unlinked conflicting enrollment was safely dropped instead. The 11 added courses are actual unmet requirements and introduced no conflicts.
- General Education and corequisite policies remain unconfigured in the database. The corrections preserve all published unsatisfied major/minor requirements.
- `execution_results.csv` records the 68 conflict operations; `ft_requirement_addition_results.csv` and `post_correction_requirement_additions.csv` record the 11 required-course additions and verification. Only Enrollment rows, Course_Section.available_Seats, and Audit_Log were changed. No grades, attendance, Student_History, declarations, or requirement rows were changed. No schema changes were made and nothing was pushed to GitHub.

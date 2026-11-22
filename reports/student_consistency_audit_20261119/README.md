# All-Student Academic Consistency Audit

**Database:** `ashford_waleed`  
**Audit date:** 2026-11-19  
**Scope:** 1,620 Student records, Fall 2026 (ID 1), Spring 2027 (ID 2), current Student portal PHP data paths.  
**Safety:** full database dump created and verified before audit; the audit ran in a `START TRANSACTION READ ONLY` transaction. No database rows or application pages were changed.

## Files

- `all_1620_student_page_consistency.csv`: one row per student with identity relationships; current Student, Student_Major, and Student_Minor values; the major each relevant page resolves; semester registrations/credits/statuses; course-set visibility; duplicate, prerequisite, course-level, credit-limit, and schedule-conflict counts; and Student_History link counts.
- `summary.json`: counts, page paths reviewed, assumptions, and limitations.
- `../academic_program_alignment_audit_20261119_integrity/reconciliation_findings.md`: detailed program/history correction proposal, with row-level IDs and values.
- `../../scripts/audits/student_consistency_readonly.php`: repeatable read-only process. Run from the project root with `php scripts/audits/student_consistency_readonly.php`. It reads `.env` but does not print credentials or run write statements.

## Current result

- All 1,620 students have matching Student, User, and Login records and Student roles.
- All 1,620 students’ active Fall 2026 and Spring 2027 Enrollment CRN sets match across Overview, Degree Audit, Registration, My Schedule, and Transcript query paths. The My Schedule joins omitted no active enrollments.
- Undergraduate program presentation differs across pages for 500 students: Student Information uses raw `Student.major_ID`; Overview and Transcript resolve a sole non-minor `Student_Major` value; Degree Audit has two minor-only edge cases. Details are in the per-student CSV. These 500 plus all 400 Graduates (whose `Graduate_Student.Program` is omitted on Student Information) leave **720 students with consistent displayed program fields and 900 requiring program-display source review**.
- Student Information does not display declared minors or `Graduate_Student.Program`; 400 Graduate records therefore lack their program on that page. Overview, Degree Audit, and Transcript use the declared minor relationship; those displays agree.
- **1,199 students have at least one program-display, data-integrity, or enrollment review flag; 421 have no flag in the checks run.** The 421 are not certified as fully degree-ready: published General Education/elective mappings are absent, and some programs have no published requirements.
- Among active Fall 2026 registrations: 34 duplicate student/course groups (53 extra rows), 522 prerequisite failures, 69 prior-pass retakes, and 237 course-level mismatches. No Fall 2026 student time conflicts or credit-limit overruns were found.
- Among active Spring 2027 registrations: 136 duplicate student/course groups (245 extra rows), 729 prerequisite failures, 322 prior-pass retakes, 460 course-level mismatches, 28 students over current credit limits (1,288 excess credits in total), and 116 overlapping student schedule pairs.
- No active registration had a missing meeting time or room link in the target semesters. The schema gives each section one time slot; Lecture/Lab records point to room resources, so this schema cannot represent separate lecture and lab meeting times for one section.

An enrollment that does not match a published major/minor checklist is reported as possibly General Education/elective. It is not a drop recommendation.

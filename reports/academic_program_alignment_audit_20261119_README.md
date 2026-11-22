# Ashford major/minor enrollment alignment audit

Read-only audit date: 2026-11-19. Source database: `ashford_waleed`. All 1,620 Student records are included in the student summary.

## Files

- `academic_program_alignment_audit_20261119_students.csv`: one row per student, sorted by unmatched-to-published-major/minor count and registration review flags.
- `academic_program_alignment_audit_20261119_registrations.csv`: every Enrollment row from Fall 2026 or Spring 2027, with section validity, program mapping, status, prerequisite result, duplicate indication, and eligibility flags.
- `academic_program_alignment_audit_20261119_requirements.csv`: published major/minor requirement rows per applicable Undergraduate student, with best-history grade and status.
- `academic_program_alignment_audit_20261119_next_requirements.csv`: unmet published requirements with satisfied prerequisites and no active Fall/Spring registration.
- `academic_program_alignment_audit_20261119_data_issues.csv`: declaration conflicts, missing requirement mappings, invalid/canceled sections, history mismatches, prerequisite/level issues, and duplicate course registrations.
- `academic_program_alignment_audit_20261119_summary.json`: counts, table coverage, and limitations.
- `academic_program_alignment_audit_20261119_overview.md`: concise findings and interpretation.

## Interpretation

For Undergraduates, a course absent from their declared Major_Requirement and Minor_Requirement rows is a **possible** mismatch only. The live schema has no General Education or elective mapping, so such courses may legitimately contribute outside the major/minor. Graduate registrations are **unverified** because Graduate_Student.Program has no corresponding requirement table; no Undergraduate requirement rows are applied to Graduate students.

Requirement progress uses Student_History's best known grade for each course compared to each published minimum grade, matching the current Degree Audit policy. Future-dated history grades are included in that existing logic if present and are separately flagged. Prerequisite checks use Course_Prerequisite and Student_History under the current minimum-grade registration rule. The schema has no corequisite table.

An active registration requires the Enrollment semester to match its Course_Section semester and the section not be canceled. Invalid/canceled rows remain in the registration CSV but are excluded from active course and credit counts. Eligibility issue counts can overlap on the same registration. No database or PHP source records were changed.

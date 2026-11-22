# Academic Program and Enrollment Alignment Audit

**Database:** `ashford_waleed`  
**Audit date:** 2026-11-19  
**Scope:** Every Student record; Fall 2026 and Spring 2027 Enrollment rows. Read-only; no records or source files changed.

## Student-by-student report files

- [Student summary — 1,620 rows, sorted by review priority](academic_program_alignment_audit_20261119_students.csv)
- [Fall 2026 and Spring 2027 registration detail — 3,490 Enrollment rows](academic_program_alignment_audit_20261119_registrations.csv)
- [Published major/minor requirements and completion status — 17,093 rows](academic_program_alignment_audit_20261119_requirements.csv)
- [Unmet requirements with eligible next courses — 2,761 rows](academic_program_alignment_audit_20261119_next_requirements.csv)
- [Declaration, registration, history, and requirement data issues](academic_program_alignment_audit_20261119_data_issues.csv)
- [Machine-readable totals and audit limitations](academic_program_alignment_audit_20261119_summary.json)
- [Method and interpretation](academic_program_alignment_audit_20261119_README.md)

## Findings

| Area | Live-database finding |
|---|---|
| Student population | 1,220 Undergraduate and 400 Graduate students (1,620 total). |
| Requirement tables | `Major_Requirement`: 156 course rows across 12 majors. `Minor_Requirement`: 61 rows across 12 minors. No General Education, elective, or graduate degree requirement tables were found. |
| Active target-semester registrations | Fall 2026: 1,619. Spring 2027: 1,861. Ten additional Spring Enrollment rows point to canceled sections and are flagged/excluded from active counts. |
| Published major/minor course fit | 966 active registrations match at least one published requirement for the student's declared Undergraduate major/minor: 560 Fall and 406 Spring. |
| Possible Undergraduate mismatch | 2,508 active Undergraduate registrations do not match a published major/minor requirement: 1,053 Fall and 1,455 Spring, across 586 students. Of these, 332 students have no target-term registrations mapped to a published major/minor requirement; 254 have a mix. These are **possible** mismatches only: they may be General Education courses or electives, which the schema does not map. |
| Graduate registrations | Six active Fall registrations across six Graduate students are unverified because Graduate program requirements are not configured. No Undergraduate requirements were applied to Graduate students. No active Spring 2027 Graduate registrations were found. |
| Major declaration consistency | For 498 Undergraduate students, `Student.major_ID` does not match the student's `Student_Major` declaration. Nineteen Undergraduates have no `Student_Major` row, so the report shows their `Student.major_ID` as a legacy fallback. The audit uses `Student_Major` declarations when present and flags conflicts for review. |
| Unpublished Undergraduate requirements | 23 students have one or more declared majors with no `Major_Requirement` rows (25 program/student associations). The largest group is Women’s, Gender, and Sexuality Studies, B.A. (19 students). The student-level and issue files list each program. |
| Possible catalog classification issue | Three students' `Student_Major` records point to Major-table entries named “Congregational Leadership Minor,” “Accounting Minor,” or “Computer and Information Sciences Minor.” These records have no Major_Requirement rows. The report flags the table/category mapping for review; it does not relabel or alter declarations based on the names. |
| Prerequisites | 1,251 active registrations do not satisfy the published prerequisite minimum based on `Student_History` (522 Fall, 729 Spring). This applies the existing registration check and may be affected by the history-link anomalies below. |
| Course level | 697 active registrations conflict with the current registration rule: 696 Undergraduates in Graduate courses and one Graduate student in an Undergraduate course (237 Fall, 460 Spring). |
| Previously passed courses | 394 active registrations are for courses with a prior passing `Student_History` grade, which the existing registration logic blocks (69 Fall, 325 Spring). |
| Duplicate same-course registrations | 170 active student/course/semester groups contain duplicate sections: 34 Fall groups (53 extra rows) and 136 Spring groups (245 extra rows). |
| History links | 436 `Student_History.course_ID` values differ from their linked section's course; 48 `semester_ID` values differ from the linked section's semester. These can affect requirement matching and prerequisite checks. No future-dated `Student_History` grades were found in this run. |

The eligibility and duplicate counts overlap: a single registration may have multiple review reasons. The report does not call an unmapped course “unrelated” because General Education and elective mappings are absent. It also does not declare a program mismatch solely because `Student.major_ID` conflicts with `Student_Major`; it flags that declaration conflict separately.

## Calculation and matching rules

- Undergraduate declarations come from `Student_Major`; where that relation is absent, `Student.major_ID` is recorded as a legacy fallback. Minors come from `Student_Minor`.
- Graduate program names come from `Graduate_Student.Program`. Graduate records are not checked against Undergraduate `Major_Requirement` rows.
- Requirement completion uses `Student_History` and the best recorded grade per course, compared to each published `min_Grade_Req`, matching the current Degree Audit calculation.
- Prerequisite checks use `Course_Prerequisite` and prior `Student_History` grades with the stored minimum grade, matching the current registration validation. No corequisite table was found.
- “Eligible next course” rows are limited to unmet published requirements with satisfied prerequisites and no active Fall 2026/Spring 2027 registration. They are suggestions from existing mappings, not enrollment changes.
- Active registrations require `Enrollment.semester_ID = Course_Section.semester_ID` and an uncanceled section. Enrollment rows remain present in the report even when invalid or canceled.

**No enrollments, declarations, grades, Student_History, or requirement records were changed.** Corrections require your review and approval.

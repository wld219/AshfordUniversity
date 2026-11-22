# Proposed Correction Plan — Approval Required

## Backup

Created before this plan was prepared:

- File: `backups/ashford_waleed_full_20261119_161235.sql.gz`
- Scope: complete `ashford_waleed` dump with schema, data, routines, triggers, and events.
- Verification: gzip integrity check passed; dump contains 75 table definitions and 71 data insert statements.
- SHA-256: `7fce08a9c953770bce22de29d5c5e9a304b67d407f91d5f72e02ccc5ea7adb7e`
- File permissions: owner-only read/write.

## Proposed corrections, grouped by evidence

| Group | Scope | Proposed value/action | Evidence | Decision before execution |
|---|---:|---|---|---|
| Explicit minor stored as primary | 139 Undergraduates | Set `Student.major_ID` to that student’s sole `Student_Major.major_ID`, preserving the existing `Student_Major` and `Student_Minor` rows. | The current primary catalog name explicitly says Minor; the sole related major has published requirements; all 139 row-level proposals list IDs and requirement/course-fit evidence. | Confirm that `Student_Major` is the authoritative primary declaration for these cases. |
| Other primary/relation disagreements | 359 Undergraduates | No value change proposed until reviewed student by student. | Current `Student.major_ID` and relation differ, but these names can represent degrees, certificates, microcredentials, or additional programs. The relation lacks a primary marker. | Registrar review. Do not overwrite either value automatically. |
| Minor-only declarations | Students 100001 and 100632 | No primary-major replacement proposed. | 100001 has only Congregational Leadership Minor in both fields. 100632 has two minor-labeled relation values and no evidence for a degree major. | Manual review; preserve values. |
| Likely valid multiple programs | Students 100144 and 300002 | Preserve both sets of declarations. | Stored major appears in the relation set; records represent Accounting B.S./M.S. and Computer Science plus Computer & Information Sciences Degree. | No correction proposed. |
| Historical section course metadata | 336 `Student_History` rows across 40 CRNs | Correct `Course_Section.course_ID` to the unanimous Student_History/Faculty_History course value. Preserve Student_History, grades, Attendance, and enrollment. | History and faculty history agree; semester agrees; no Attendance or Enrollment is linked to these historical CRNs. | Review row-level proposal and approve. |
| Historical section term/course metadata | 48 rows across CRNs 30041–30048; 24 students | Correct the linked `Course_Section.course_ID` and `semester_ID` to the history/faculty value, Fall 2022 (ID 3). The current section says Spring 2023 (ID 4). | All 48 history rows and Faculty_History agree; no linked Attendance or Enrollment. These 48 are included within the 336 course-metadata rows above. | Review row-level proposal and approve. |
| Active history/section disagreements | 100 rows, CRNs 10001–10010 | No change proposed. | Current Fall 2026 Enrollment and Attendance exist; attendance course evidence is mixed. | Registrar/source-record review before any edit. |
| Unpublished program requirements | 25 program associations affecting 23 students under the completed audit’s declaration rule | Preserve declarations. Do not invent requirement rows. | Foreign-key-valid program records, but no `Major_Requirement` rows. The set includes 19 students without a Student_Major relationship and four students with additional/unconfigured programs. | Academic policy/catalog owner must provide approved mappings. |

## Existing enrollment issues — no removal proposal based only on program fit

- Fall 2026: 1,619 active enrollments; 34 duplicate course groups (53 extra rows), 522 prerequisite failures, 69 prior-pass retakes, 237 course-level mismatches. No schedule overlaps or students above 16 credits were found.
- Spring 2027: 1,861 active enrollments; 136 duplicate course groups (245 extra rows), 729 prerequisite failures, 322 prior-pass retakes, 460 course-level mismatches, 28 students over current credit limits by 1,288 credits total, and 116 schedule conflict pairs.
- Courses not mapped to published major/minor requirements remain possible General Education or electives. No enrollment removals are proposed from that signal alone.

After approval, apply only the approved declaration and historical **Course_Section metadata** corrections in a separately reviewed, transactional change plan. Re-run the read-only process and validate all students before making any enrollment changes. The Spring 2027 registration issues require their own student/CRN review; they are not included as approved removals here.

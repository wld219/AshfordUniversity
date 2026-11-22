# Academic Program and History Integrity Reconciliation

**Database:** `ashford_waleed`  
**Rechecked:** 2026-11-19  
**Mode:** Read-only; no database records, schema, or application files were changed. These files are proposals for review, not migration instructions to execute without approval.

## Evidence and source-of-truth limitation

The relevant schema has `Student.major_ID` (one FK to `Major`) and `Student_Major(student_ID, major_ID, date_Of_Choice)` (many-to-many, no `is_primary`, end date, or program type). `Major` contains degree, minor, certificate, microcredential, and graduate-program labels. Therefore, a mismatch alone cannot prove which value is the primary declaration. The saved enrollment-alignment audit used `Student_Major` as the declaration when present and `Student.major_ID` only as fallback. The previously approved Degree Audit rule instead designated `Student.major_ID` primary. That policy difference explains why its “23 unpublished programs” differs from the number obtained by treating every `Student.major_ID` as primary.

## Findings by issue

| Issue | Live recheck | Assessment | Proposed handling |
|---|---:|---|---|
| `Student.major_ID` vs `Student_Major` | 498 Undergraduates have a `Student_Major` row but no row equal to `Student.major_ID`. The raw non-equal join yields 501 rows because three students have additional program rows while their stored primary is already present. | The 498 are true source disagreements, but not all are proven bad; a relation row may represent an additional program. Of the 498, **139** stored `Student.major_ID` values are explicitly named “Minor”; each has one distinct `Student_Major` candidate with 13 published major requirement rows. Two additional students outside that conflict set have minor-labeled primary values: 100001 (same minor in both fields) and 100632 (two minor-labeled relation rows); neither has evidence identifying a degree major. The stored value has zero major requirement rows in all 498. Candidate major requirements are matched by Student_History for 396 students and by current target-semester registrations for 182. | **Strong correction candidates:** 139 “Minor” values; propose `Student.major_ID` = the sole `Student_Major.major_ID` shown in the CSV. Confirm through registrar declarations before applying because `Student_Major` has no primary flag. **Manual review:** other 359 disagreements; do not replace automatically. Full row-level values and evidence are in the CSVs. |
| Multiple program relationships | Three students have multiple `Student_Major` rows: 100144, 100632, and 300002. For 100144 (Accounting B.S. + M.S.) and 300002 (Computer Science + Computer & Information Sciences Degree), the stored value appears in the relation set and the extra degree may be legitimate. 100632 is different: the stored primary and both relation rows are minor-labeled catalog records. | Two likely valid multi-program declarations; one unresolved classification issue (100632). | Preserve 100144 and 300002. Manually review 100632; do not choose a primary from these records alone. See the separate review CSV. |
| `Student_History.course_ID` vs linked section | 436 history rows across 50 CRNs. 336 rows (40 historical CRNs) have unanimous Student_History and Faculty_History course values, matching term values, and no Attendance or Enrollment rows on the CRN. The remaining 100 rows are on 10 Fall 2026 CRNs (10001–10010) with linked Attendance and Enrollment; `Attendance.course_ID` evidence is mixed. | The 336 historical rows strongly indicate bad `Course_Section.course_ID` metadata, rather than bad Student_History. The active 100 rows cannot be safely resolved from these records alone. | For the 336 rows, propose correcting **Course_Section** course values to the unanimous Student_History/Faculty_History value; keep all Student_History untouched. For CRNs 10001–10010, manual review only; preserve section, enrollment, attendance, and history unchanged pending source documents. Exact row proposals are in `history_course_mismatches.csv`. |
| `Student_History.semester_ID` vs linked section | 48 rows, 8 CRNs (30041–30048), 24 students. All are within the 336 strongly supported historical section groups; Student_History and Faculty_History identify Fall 2022 (ID 3), while Course_Section is Spring 2023 (ID 4). No linked Attendance or Enrollment rows. | Strong evidence points to `Course_Section` semester metadata being wrong. All 48 also have a course-ID mismatch. | Propose Course_Section course and semester metadata matching Student_History and Faculty_History (course/term values are listed per row); preserve Student_History grades and IDs. This remains a proposal pending approval. |
| Programs without published requirements | Under the completed audit’s rule (Student_Major rows when present; Student.major_ID fallback only when no Student_Major rows), there are 25 program/student associations affecting 23 students. Separately, if `Student.major_ID` is treated as primary for every Undergraduate as the approved Degree Audit code does, **520** Undergraduates’ primary IDs have no Major_Requirement rows. | Missing requirement mappings are not proof that a student declaration is wrong. The catalog stores many real program labels without requirement rows. The two counts differ because the major source-of-truth rule differs. | Do not replace declarations or invent requirements. Preserve the 23 associations and publish approved mappings. Use the detailed CSV for IDs, source field, current values, and exact missing program. |

### Concrete major examples supported by current records

- **100002, David Joseph:** `Student.major_ID=118` (“Computer and Information Sciences Minor”); `Student_Major=2` (Cybersecurity); `Student_Minor=3` (Business Administration). Student_History and active registrations match Cybersecurity requirement courses. Proposed primary value: **2 — Cybersecurity**. This matches the earlier user-confirmed expectation; no row was changed.
- **100013, Grace Lopez:** `Student.major_ID=140` (“Pre Health Professions Minor”); `Student_Major=1` (Computer Science); `Student_Minor=10` (Education). Proposed primary value: **1 — Computer Science**. This matches the previously supplied expected major/minor, and the catalog candidate has published major requirements; no row was changed.

## Academic-system effects

- **Degree Audit:** A wrong primary ID can select no checklist or the wrong checklist; missing published requirements must show as unpublished. If the UI treats all `Student_Major` rows as primary, additional majors can be misclassified. Graduate programs must not be evaluated against undergraduate requirement rows.
- **Prerequisites:** Prior-course lookup depends on Student_History.course_ID and its grade. The 436 mismatches can make prerequisites appear satisfied or missing for the wrong course. The 48 term mismatches also distort chronological/prior-term interpretation.
- **Transcript:** Student_History is the completed-history source. The evidence supports preserving its course, semester, and grade values; correcting those rows would risk transcript/GPA history. The 100 active Fall CRNs need manual reconciliation because attendance/enrollment links conflict.
- **Registration/alignment reports:** Mapping registrations to degree requirements can be wrong when the major source is unresolved. The absence of General Education/elective requirement tables also means an unmatched course cannot be called unrelated.

## Proposed correction order

1. Registrar confirms the authoritative primary-program rule and confirms the 139 explicit “Minor” cases; if confirmed, update only the proposed `Student.major_ID` values, preserving all `Student_Major` and `Student_Minor` declarations.
2. Review the other 359 source disagreements student-by-student; check official declaration/catalog records before changing either field.
3. Approve the 336 historical `Course_Section.course_ID` metadata proposals and the 48 `Course_Section.semester_ID` proposals. Do not update `Student_History`.
4. Manually resolve Fall 2026 CRNs 10001–10010 using authoritative section/attendance/registrar records; do not infer corrections from the mismatched rows alone.
5. Keep the 23 unpublished program associations unchanged until approved requirement mappings or program-type/catalog evidence are supplied.

## Row-level files

- [498 unresolved primary-program conflicts with all existing IDs, candidate requirements, course-fit evidence, and proposed action](major_primary_conflicts_current.csv)
- [139 primary values explicitly classified as minors, with the proposed candidate ID](primary_values_named_minor.csv)
- [Two likely valid multiple-program records](valid_multiple_programs.csv)
- [Ambiguous minor-only declarations for student 100632](ambiguous_minor_classification.csv)
- [25 unpublished program associations affecting 23 students, with field source and current values](unpublished_programs_under_prior_audit_logic.csv)
- [436 course-ID mismatch rows, with proposed section metadata values where evidence is unanimous](history_course_mismatches.csv)
- [48 semester-ID mismatch rows and proposed section course/semester values](history_semester_mismatches.csv)
- [Machine-readable counts and CRN-level evidence](current_reconciliation_summary.json)

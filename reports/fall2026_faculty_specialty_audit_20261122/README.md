# Fall 2026 Faculty Specialty Audit — Consolidated Review

**Scope:** isolated test database `ashford_waleed_fall_test_20261121_232146`; Fall 2026 (`semester_ID=1`). The named project's `.env` was verified to point to live database `ashford_waleed`; all review queries and the temporary assignment test below used the isolated database. No live writes, PHP edits, schema edits, or Spring 2027 edits were made.

## Executive result

- Reviewed **178 active sections** across the requested ten departments (all 4 credits; 153 undergraduate, 25 graduate), plus rechecked the 19 Computer Science sections for the three specified provisional proposals.
- Existing department affiliation: **178/178 PASS**, no assigned faculty lacked a course-department affiliation.
- Current Fall faculty schedule-overlap scan: **0 conflicts** in the 197 active sections. Temporary combined proposal: **0 faculty conflicts, 0 room conflicts, 0 department mismatches**.
- Classification screen over the 178 sections: **37 supported by specialty or relevant teaching history; 12 plausible but not fully verified; 98 questionable specialty match; 31 require manual academic approval**. These are triage labels based on free-text specialty and history—not proof of credential or authorization.
- Candidate proposals: **43** across the ten departments and the **3 specified CS provisional moves** (46 combined). Every new candidate had a valid Login row and zero baseline Fall sections in this isolated snapshot. These pairings are **for academic review only**. Database schema does not provide formal course-specific qualifications, graduate teaching authorization, or verified credentials, so none is certified safe to apply.
- 55 questionable non-CS sections had no candidate meeting the conservative candidate screen. Graduate assignments and capstones are not proposed for transfer.

## Department-by-department results

| Department | Sections | Credits | FT affiliated | PT affiliated | Distinct current instructors | Specialty/history supported | Plausible, unverified | Questionable | Manual academic approval | Pairings proposed |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| Accounting, Taxation & Business Law (11) | 10 | 40 | 10 | 0 | 4 | 0 | 7 | 3 | 0 | 3 |
| Arts and Media (10) | 26 | 104 | 63 | 20 | 25 | 5 | 0 | 13 | 8 | 9 |
| Business (2) | 9 | 36 | 84 | 0 | 6 | 6 | 3 | 0 | 0 | 0 |
| Education (8) | 21 | 84 | 51 | 16 | 19 | 3 | 0 | 17 | 1 | 13 |
| Engineering (5) | 24 | 96 | 71 | 9 | 12 | 0 | 1 | 23 | 0 | 10 |
| English (6) | 20 | 80 | 59 | 6 | 10 | 7 | 1 | 9 | 3 | 0 |
| Health Sciences (9) | 20 | 80 | 58 | 16 | 19 | 2 | 0 | 14 | 4 | 2 |
| Mathematics (3) | 5 | 20 | 90 | 0 | 2 | 5 | 0 | 0 | 0 | 0 |
| Natural Sciences (4) | 22 | 88 | 80 | 13 | 8 | 4 | 0 | 10 | 8 | 1 |
| Social Sciences (7) | 21 | 84 | 62 | 20 | 19 | 5 | 0 | 9 | 7 | 5 |

The per-section facts and classification for every CRN are in `all_178_sections_review.csv`. `department_summary.csv` contains the machine-readable totals. Major recurring concerns include Engineering Mathematics/Graphics/Mechanics sections assigned to unrelated specialty labels; English literature and writing sections assigned to science/business specialties; Education foundations and special-education courses assigned to unrelated specialties; and Natural Sciences, Health Sciences, Social Sciences, and Arts/Media sections with similar cross-specialty concerns. The section CSV identifies each exact CRN, current instructor, affiliation, specialty, historical counts, enrollment, attendance, grades/history indicators, and any provisional candidate.

## Provisional candidate changes

The complete old/new list is in `proposed_changes_for_review.csv`. It contains 43 other-department candidates and the three specified Computer Science follow-ups:

- CRN 140051 CS1002: 200246 → 200160
- CRN 140052 CS1002: 200095 → 200072
- CRN 140061 CS3011: 200095 → 200164

All three proposed CS instructors have a Computer Science affiliation in the test database, but no same-course CS teaching history was found for them. Specialty labels are Digital Media/Community Health on the outgoing faculty and Information Systems/Computer Engineering/Discrete Mathematics on the candidates as applicable. These are unverified teaching-qualification proposals; they must receive academic review before any reassignment. The temporary test found no schedule/room conflict. The CS candidates were not assigned another section within these 197 current Fall sections before the test.

The other 43 candidates were screened for matching department affiliation, specialty-text alignment to the course title/subject, valid Login, zero baseline Fall sections, a soft planning load under 3 FT / 2 PT sections, and timetable non-overlap. The subject-to-specialty vocabulary is an audit heuristic; it is not an institutional qualification standard. The broadest concerns are candidates whose specialties could be adjacent but do not prove competency; consult the CSV and require academic approval.

## Workload impact if all 46 proposals were approved

- **Before:** FT: 45 instructors / 126 sections / 504 teaching credits; PT: 71 / 71 / 284. Active teaching Faculty: 116 of 422; unassigned: 306.
- **Temporary after:** FT: 70 instructors / 155 sections / 620 credits; PT: 42 / 42 / 168. Active teaching Faculty: 112 of 422; unassigned: 310.
- The proposed transfers shift 29 four-credit assignments from PT to FT instructors (credits remain 788 overall). The active teaching-faculty count decreases by 4 and the unassigned count increases by 4, because some current single-section instructors would lose their only section while multiple sections concentrate on fewer candidates. This is an observed trade-off, not a recommendation to approve all moves. FT/PT employment records were unchanged.
- Section-count distribution (all 422): before **0:306, 1:77, 2:1, 3:34, 4+:4**; temporary after **0:310, 1:67, 2:9, 3:32, 4+:4**.

## Records and conflict safeguards

The 178 section review records capture actual enrollments, attendance rows, nonblank enrollment-grade rows, Student_History references, Faculty_History references, grade exceptions, final-grade rows, and correction requests. Across the targeted sections the audit saw 1,467 enrollment links, 12,518 attendance rows, 1,199 Student_History references, 110 Faculty_History references, and 117 nonblank Enrollment grade values. The test database reported 0 Grade_Exception rows, 0 Final_Grade rows, and 0 Final_Grade_Correction_Request rows for the checked sections. Counts in the CSV are per CRN; the same underlying row may be linked only to its own section.

All 46 proposed instructor-only updates were applied inside one InnoDB transaction on the isolated test DB, then rolled back. While inside the transaction: 197 Fall active sections remained; no faculty or room conflicts, department mismatches, Saturday classes, or missing rooms were introduced; Fall Enrollment count remained 1,619; Spring active section count remained 198; the Student, Enrollment, Attendance, Student_History, Faculty_History, Waitlist, grade-related table signatures remained equal; non-instructor section fields were unchanged. Post-rollback instructor IDs matched the pre-test snapshot for every proposed CRN. `temporary_transaction_validation.json` records the checks. This is structural validation only and does not establish academic qualification.

## Faculty portal smoke tests

Twenty randomly selected Faculty records with valid Login rows were tested in an isolated copied PHP portal using session fixtures (11 Full-Time, 9 Part-Time, representing 15 affiliated departments). Dashboard, My Schedule, Course Rosters/grade controls, and Weekly Attendance rendered for Fall 2026: **80/80 pass**. Cross-faculty CRN authorization checks on Course Rosters and Attendance: **40/40 pass**. IDs and results are in `random_faculty_portal_tests.csv`.

These were **session-fixture rendering tests**, not password authentication tests. No real passwords were used. This smoke test covered Fall 2026; Spring 2027 was not rendered in the portal fixture. Its 198 active sections were only checksum-compared inside the temporary transaction and remained unchanged.

## Decision needed before any changes

1. Academic leadership must confirm which specialty/experience evidence is sufficient to assign each listed course, particularly graduate courses, capstones, Engineering, English, Education, and cross-discipline assignments.
2. Review the 43+3 provisional pairings and workload trade-off; approving all would leave 310 Faculty unassigned, four more than baseline, and would concentrate teaching load.
3. For the 55 questionable sections without screened candidates, decide whether to retain current assignments pending credentials or provide qualification/availability evidence for another instructor.
4. Confirm whether Accounting, Taxation & Business Law's ten `BU3071` sections belong in that department's teaching pool; existing data affirms department affiliation, but course title is Information Technology & Application.

## Artifacts

- `all_178_sections_review.csv` — every requested section and evidence fields.
- `department_summary.csv` — department totals.
- `proposed_changes_for_review.csv` — 43 other-department candidate moves plus 3 CS candidates.
- `random_faculty_portal_tests.csv` — 20 session-fixture accounts and 80 renders/40 authorization checks.
- `temporary_transaction_validation.json` — transaction and rollback checks.

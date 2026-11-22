# Ashford University — Fall 2026 Department and Faculty Audit

Read-only snapshot of live database `ashford_waleed`, semester_ID 1 (Fall 2026). Audit generated 2026-11-21. No database, application, student, or faculty records were changed.

## Summary

- Departments: 28; Faculty: 422 (322 Full-Time, 100 Part-Time); catalog courses: 922.
- Active Fall sections: 197; all are 4 credits; total teaching credits: 788. Cancelled: 0; sections without instructors: 0.
- Faculty with any department affiliation: 422/422. Login presence is listed per faculty account in `faculty_roster_fall2026.csv` (350 linked Login rows; 72 without Login rows).
- Assignment affiliation: 0 active section assignments fall outside the assigned faculty member's Faculty_Department affiliations. Faculty time-conflict pairs: 0; room conflict pairs: 0.
- Student enrollment rows: 1619 across 808 distinct students; duplicate same-student/course groups: 34 (53 extra rows). No student records were changed.
- Course levels: Undergraduate 172 sections / 688 credits; Graduate 25 sections / 100 credits. The schema has no faculty graduate qualification table, so graduate teaching eligibility cannot be independently verified.

## Workload model and feasibility

The requested target translates to 3 sections per Full-Time faculty and 2 per Part-Time faculty: 322×3 + 100×2 = 1166 distinct instructor-section assignments. Against 197 current sections, the arithmetic gap is **969 sections**. That is a unique faculty count model and is the most appropriate high-level target. It is not a recommendation to create that many sections: it greatly exceeds the existing catalog offerings and room/time/student demand is unverified. If the literal requirement is that every faculty member teach at least one section, the minimum arithmetic gap is 422−197=225 sections.

Per-department target arithmetic is shown below using department affiliation headcounts. It must not be summed as a unique staffing requirement: 219 faculty have two affiliations and 103 have three, while all their affiliation rows show `percent_Time=100`; the data does not identify primary department or fractional workload allocation. Department-level shortage values therefore overcount unique people and are planning indicators only.

Current section count distribution: Full-Time 0 sections=198; Full-Time 1 sections=123; Full-Time 3 sections=1; Part-Time 0 sections=29; Part-Time 1 sections=71. Employment type (`Faculty.faculty_Type`) is distinct from semester workload.

## Department-by-department inventory

| Department ID | Department | FT affiliated | PT affiliated | Catalog courses | Active Fall sections | Fall teaching credits | Catalog courses without Fall section | Raw target sections* | Raw shortage* | Examples of unsectioned catalog courses |
|---:|---|---:|---:|---:|---:|---:|---:|---:|---:|---|
| 1 | Computer Science | 74 | 0 | 25 | 19 | 76 | 17 | 222 | 203 | CS1003, CS2008, CS2009, CS3010, CS3012 |
| 2 | Business | 84 | 0 | 24 | 9 | 36 | 19 | 252 | 243 | BU1001, BU1003, BU1004, BU1005, BU2007 |
| 3 | Mathematics | 90 | 0 | 24 | 5 | 20 | 20 | 270 | 265 | MA1003, MA1005, MA2006, MA2007, MA2008 |
| 4 | Natural Sciences | 80 | 13 | 24 | 22 | 88 | 14 | 266 | 244 | NS1002, NS1003, NS1004, NS1005, NS2008 |
| 5 | Engineering | 71 | 9 | 24 | 24 | 96 | 17 | 231 | 207 | EG1003, EG1004, EG1005, EG2006, EG2008 |
| 6 | English | 59 | 6 | 73 | 20 | 80 | 65 | 189 | 169 | EL3010, EL3500, EL3510, EL3600, EL3610 |
| 7 | Social Sciences | 62 | 20 | 24 | 21 | 84 | 16 | 226 | 205 | SS1001, SS1002, SS1003, SS1005, SS2007 |
| 8 | Education | 51 | 16 | 24 | 21 | 84 | 15 | 185 | 164 | ED1002, ED1003, ED1005, ED3010, ED3011 |
| 9 | Health Sciences | 58 | 16 | 24 | 20 | 80 | 16 | 206 | 186 | HS1002, HS1004, HS1005, HS2006, HS2009 |
| 10 | Arts and Media | 63 | 20 | 24 | 26 | 104 | 13 | 229 | 203 | VD1002, VD2007, VD2008, VD2009, VD3011 |
| 11 | Accounting, Taxation & Business Law | 10 | 0 | 42 | 10 | 40 | 41 | 30 | 20 | acl601, BU3502, BU3511, BU3600, BU3605 |
| 12 | Adolescence Education | 5 | 0 | 8 | 0 | 0 | 8 | 15 | 15 | ED3742, ED3820, ED3900, ED4085, ED4230 |
| 13 | Biological Sciences | 3 | 0 | 58 | 0 | 0 | 58 | 9 | 9 | ACLS601, BLS501, BS2490, BS3400, BS3450 |
| 14 | Chemistry & Physics | 3 | 0 | 67 | 0 | 0 | 67 | 9 | 9 | BS2400, BS2401, BS2410, BS2411, BS6750 |
| 15 | Childhood Education & Literacy | 1 | 0 | 4 | 0 | 0 | 4 | 3 | 3 | CE1001, CE2001, CE3001, CE4001 |
| 16 | Exceptional Education & Learning | 1 | 0 | 4 | 0 | 0 | 4 | 3 | 3 | EE1001, EE2001, EE3001, EE4001 |
| 17 | History & Philosophy | 3 | 0 | 129 | 0 | 0 | 129 | 9 | 9 | AS2112, AS2122, AS2252, AS2262, AS2263 |
| 18 | Management, Marketing & Finance | 2 | 0 | 17 | 0 | 0 | 17 | 6 | 6 | BU4035, BU4120, BU4130, BU4720, BU4774 |
| 19 | Mathematics, Computer & Information Science | 3 | 0 | 19 | 0 | 0 | 19 | 9 | 9 | ANY4000, CS2511, CS3611, CS4100, CS4501 |
| 20 | Media & American Studies | 5 | 0 | 16 | 0 | 0 | 16 | 15 | 15 | AS3432, AS3745, AS3852, AS4220, AS4435 |
| 21 | Modern Languages | 2 | 0 | 45 | 0 | 0 | 45 | 6 | 6 | EL3560, HI3480, ML1000, ML1110, ML1400 |
| 22 | Politics, Economics & Law | 4 | 0 | 55 | 0 | 0 | 55 | 12 | 12 | AS4202, AS4232, BU4110, BU4780, BU5125 |
| 23 | Psychology | 4 | 0 | 38 | 0 | 0 | 38 | 12 | 12 | ONE4000, PE3750, PE4450, PE4610, PY2530 |
| 24 | Public Health | 1 | 0 | 30 | 0 | 0 | 30 | 3 | 3 | BS2100, BS2300, BS2310, PH2900, PH3200 |
| 25 | School of Business Graduate Programs | 1 | 0 | 20 | 0 | 0 | 20 | 3 | 3 | MA2000, PS2330, PS3130, PS3230, PS3260 |
| 26 | Sociology | 2 | 0 | 50 | 0 | 0 | 50 | 6 | 6 | CR1500, CR2090, CR3092, CR3093, CR3094 |
| 27 | Visual Arts | 3 | 0 | 26 | 0 | 0 | 26 | 9 | 9 | VA2010, VA2020, VA2030, VA2045, VA2320 |
| 28 | Women's, Gender, and Sexuality Studies | 2 | 0 | 4 | 0 | 0 | 4 | 6 | 6 | WGS1001, WGS2001, WGS3001, WGS4001 |

*Raw target = affiliated FT×3 + affiliated PT×2; shortage = max(raw target−current sections, 0). These numbers double-count multi-department faculty and are not safe new-section assignments.

Departments with zero active Fall offerings: Adolescence Education, Biological Sciences, Chemistry & Physics, Childhood Education & Literacy, Exceptional Education & Learning, History & Philosophy, Management, Marketing & Finance, Mathematics, Computer & Information Science, Media & American Studies, Modern Languages, Politics, Economics & Law, Psychology, Public Health, School of Business Graduate Programs, Sociology, Visual Arts, Women's, Gender, and Sexuality Studies.

## Faculty assignment and course fit findings

- Every current active Fall section has an instructor, and each instructor is affiliated with the course department.
- The Faculty table contains a free-text `Specialty`, but no structured course-qualification or graduate-authorization mapping. A reliable specialty fit decision cannot be derived from title matching alone. The all-account names, department affiliations, specialty, employment type, login presence, active CRNs, section counts, and credits are in `faculty_roster_fall2026.csv`.
- The Department catalog comparison is in `course_catalog_fall2026_availability.csv`; it contains all catalog rows and active Fall section counts. Unsectioned catalog courses are candidates for review only, not approved new offerings.
- The 197-section detailed inventory, including course level, assigned faculty, actual meeting days/times, room, enrollment count and stored available seats, is in `fall2026_active_sections.csv`.

## Resources and constraints

- Rooms: Lecture: 258, Office: 441, Lab: 44. Room table has no capacity field; capacity is represented through Lecture.avail_Seats and Lab.no_Of_Workstations (observed values 10). Existing active sections: 171 lecture-room sections, 26 lab-room sections, missing room 0. Current room-conflict scan: 0.
- Time slots: 36 definitions, including Saturday slots. The existing Fall-2026-forward policy prohibits Saturday; no active Fall section uses Saturday. There are 28 non-Saturday definitions; the four Friday 200-minute windows are slot IDs 33–36 (08:00–11:20, 11:30–14:50, 15:00–18:20, 18:30–21:50); all 28 current Friday sections use 200-minute windows. No active faculty time conflicts were found.
- Existing seat/capacity fields require per-section reconciliation before expansion. Current schedules show no enrollments above the 10-seat lecture/lab capacity, but the `available_Seats` values are not all consistent with capacity minus enrollments.
- Enrollment has no explicit status column; it cannot by itself distinguish dropped, waitlisted, or otherwise inactive registration. Do not use raw row counts as definitive demand without checking application rules and waitlist logic.
- Existing enrollment data includes 34 duplicate student/course groups (53 extra rows) and 237 student/course-level mismatches (236 undergraduate students in graduate courses; one graduate student in an undergraduate course). These require separate academic review before any redistribution; no student identities are included here.

## Safe expansion / redistribution recommendation

1. Do not move existing instructors based on workload targets alone. Current department affiliation checks and schedule-conflict scans pass. Faculty who teach zero sections may have legitimate duties; the database does not establish that every Full-Time employee must teach.
2. Do not propose exact new CRNs/instructors/rooms/times yet. While the catalog has courses without Fall sections, course demand, published program need, qualifications, graduate eligibility, instructor availability, registration eligibility and section-specific seat needs are not evidenced sufficiently to safely identify exact new offerings.
3. First resolve policy/data questions: define primary or workload-bearing department for multi-affiliated faculty; provide auditable course/graduate teaching qualifications; verify course demand and program requirements; clarify active enrollment status; confirm capacity semantics.
4. Then build a constrained candidate schedule using available qualified faculty, valid room/time slots, prerequisites, student timetables, seat limits, and the no-Saturday/Fri-200-minute rules. Validate on a restored database copy. Any live reassignment or new section should be a separately approved change.

## Report files

- `department_summary_fall2026.csv` — all department inventory and non-unique target arithmetic.
- `faculty_roster_fall2026.csv` — all 422 faculty and Fall assignment/affiliation details.
- `fall2026_active_sections.csv` — all 197 active section facts and meetings.
- `course_catalog_fall2026_availability.csv` — all catalog courses and active Fall section counts.

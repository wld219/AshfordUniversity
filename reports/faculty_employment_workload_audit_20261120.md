# Faculty employment classification and semester workload audit

Read-only live database audit of `ashford_waleed`, generated 2026-11-20. Teaching credits count unique active (`is_Cancelled = 0`) CRNs for each faculty member and semester, using `Course.course_Credits`; canceled sections are excluded. Calculated load is FT at 12+ credits and PT below 12, matching the existing helper. No data or application records were changed.

## Summary

- Faculty records: 422
- Stored `faculty_Type`: Full-Time 322; Part-Time 100
- Faculty records without a Login row: 72 (IDs 400011–400082)
- All 422 have a matching Faculty-role `User`; all 350 existing Login rows have the Faculty role and are unlocked
- Faculty records without a department affiliation: 0
- Active sections assigned outside the instructor's declared department affiliations: 0
- Active sections: 197 Fall 2026; 198 Spring 2027
- Every active section has an assigned Faculty record and a linked room; no section is unstaffed
- 53 faculty without Login accounts are assigned 56 active Spring sections; none of the missing-login faculty has an active Fall section

## Fall 2026 (semester_ID 1)

- Stored Full-Time records calculated PT: 321 (zero credits: 198; 1–11 credits: 123)
- Stored Part-Time records calculated FT (12+ credits): 0
- Stored/calculated workload mismatches: 321
- Calculated workload totals across all Faculty records: FT 1; PT 421
- Faculty with no active sections: 227

## Spring 2027 (semester_ID 2)

- Stored Full-Time records calculated PT: 322 (zero credits: 207; 1–11 credits: 115)
- Stored Part-Time records calculated FT (12+ credits): 0
- Stored/calculated workload mismatches: 322
- Calculated workload totals across all Faculty records: FT 0; PT 422
- Faculty with no active sections: 278

## Interpretation and recommendations

`Faculty.faculty_Type` is a single semester-independent enum (`Full-Time`/`Part-Time`), populated by an Admin creation form labeled `Type (faculty)`. The application also uses it for section-assignment limits and advising eligibility. It is therefore a stored faculty classification with operational consequences, not a semester workload calculation. The schema contains no separate HR employment source that can verify whether each stored classification reflects a current employment contract. Course load alone is not sufficient evidence to change it.

Keep the two concepts separate in Faculty Overview: show the stored faculty classification separately from selected-semester teaching workload (credits and calculated load). Do not overwrite `faculty_Type` to make it match the 12-credit workload label. Before any stored-type corrections, obtain an authoritative employment/appointment roster and evaluate effects on assignment limits, department constraints, and advising.

A separate assignment-rule exception also exists: Faculty 200072 is stored Full-Time and reaches 12 Fall credits through three sections (CRNs 140025, 140026, 140027), while the current section validation limits Full-Time assignments to at most two sections per semester. This is not a stored-vs-credit mismatch, but requires policy review.

The full 422-person inventory with departments, specialty, both semester workloads, courses, CRNs, course levels, and meeting schedules is in `faculty_assignment_inventory_20261120.csv`. Faculty with zero active sections by semester are in `faculty_without_active_assignments_20261120.csv`. The detailed stored-type/workload mismatches, including name, email when a Login exists, credits, active CRNs, and affiliations are in `faculty_employment_workload_mismatches_20261120.csv`. Missing Login records are listed in `faculty_missing_login_accounts_20261120.csv`.

## Current assignment, schedule, and capacity review

There are no active sections awaiting an instructor, so the database provides no existing unstaffed section CRNs to assign. No active faculty or room time overlaps were found. Current student schedule overlaps are 0 in Fall 2026 and 116 conflicting course pairs affecting 19 students in Spring 2027; see `student_time_conflict_audit_20261119/post_correction_unresolved_conflicts.csv` for detailed cases. These are existing student conflicts and no assignment changes are proposed.

There are no confirmed department-affiliation mismatches. All 422 Faculty specialty fields are populated, but specialty is free text and no Faculty qualification/certification mapping table exists; therefore specialty-to-course suitability cannot be reliably confirmed or rejected from database evidence. The active catalog sections include 321 Undergraduate and 74 Graduate sections, with no formal credential-to-course-level rule available.

Two Spring sections exceed their linked physical-room capacity: CRN 20012 (CS2007), 11 enrolled in capacity 10, and CRN 20069 (BU1002), 11 enrolled in capacity 10. Ten Fall sections have a stale `available_Seats` counter compared with room capacity minus matching enrollments; these are counter inconsistencies, not physical over-capacity. Details are in `faculty_section_capacity_review_20261120.csv`. The live `Waitlist` table has one row: student 100002 is position 1 for Spring CRN 20001 (CS1001). That section is full (10/10), and its only other active Spring CS1001 section (CRN 20002) is also full (10/10). This is one verified unmet seat request, but it does not by itself justify a full new section. No new section is proposed; first review a capacity/room option or confirm whether a new section is warranted by broader demand.

## Proposed actions before approval

No faculty assignment or new-section changes are proposed. First confirm from an authoritative employment/appointment source whether stored `faculty_Type` means employment status and whether every Full-Time appointment is expected to carry a minimum teaching load. If some faculty are not expected to teach, record that policy separately rather than inferring a correction from their zero course load. Review the two Spring room-capacity exceptions and student conflicts independently. Only after workload expectations and demand are confirmed should the university consider a specific section addition or reassignment; validate the chosen instructor, room, time, department, and enrollment consequences before applying it.

Additional schema consistency check: 250 stored Full-Time records have only a `Full_Time_Faculty` row, 16 stored Full-Time records have only a `Part_Time_Faculty` row (IDs 400067–400082), and 56 have neither subtype row. All 100 stored Part-Time records have only a `Part_Time_Faculty` row. This yields 72 Full-Time parent/subtype inconsistencies; review separately before any stored classification changes. The `Faculty.no_of_Classes` field is also not a semester workload source: it has values 0 for 39, 1 for 133, and 2 for 250 records, and application code only sets it during account creation; it does not recalculate it from semester assignments.

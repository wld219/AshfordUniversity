# Faculty–Student Course Assignment and Scheduling Audit

**Database:** `ashford_waleed`  
**Method:** live `START TRANSACTION READ ONLY` and SELECTs only; no database, application, enrollment, grade, attendance, or schema changes.  
**Semesters:** Fall 2026 = ID 1; Spring 2027 = ID 2.

## Schema and counting rules

- `Course_Section` has one `time_Slot_ID`, one `faculty_ID`, optional `lecture_ID` and `lab_ID`. Lecture and Lab reference Room IDs; they do not hold separate meeting day/time or instructor columns. Therefore, component conflicts are evaluated at the shared Course_Section time slot, and lecture/lab rooms are checked separately.
- Meeting days and intervals come from `Time_Slot_Day` / `Day` and `Time_Slot_Period` / `Period`. Overlap requires same semester, same day, and strict time intersection; back-to-back boundaries and different days are not conflicts. Rows without known day/time are excluded from conflict claims and listed as unscheduled.
- `Enrollment` has no status field. As in Student page logic, active means the Enrollment semester equals the linked Course_Section semester and the section is not canceled. Canceled or semester-mismatched rows are reported separately.
- Unique constraints: `Course_Section.CRN` is primary key; Enrollment is keyed by `(student_ID, CRN)`. Duplicate same-CRN enrollment rows cannot exist, but duplicate same-course registrations across multiple CRNs can.

## Faculty and student accounts

- Faculty table: **422** records. Faculty-role Login accounts: **350**. **72** Faculty records have no Login row; thus the 350 Faculty logins are a subset of the 422 staffing/academic Faculty records, not the same population. There are no role mismatches among records with Faculty login rows and no Faculty login lacking a Faculty row. All 350 Faculty logins and 1620 Student logins are active and unlocked. Details and IDs are in `account_coverage_all_faculty_and_students.csv`; 53 faculty records without logins are assigned to active target-semester sections.
- Student records: **1620**; Student-role Login rows verified: **1620**. All available students are included in account and roster checks.

## Summary by semester

| Semester | Sections all/active/canceled | Active enrollments | Faculty conflicts | Student conflicts | Room conflicts | Duplicate course groups | Enrollment/section semester mismatch | Canceled-section enrollments | Faculty sections without login | Faculty_History mismatches |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| Fall 2026 | 197/197/0 | 1619 | 0 | 0 | 0 | 34 | 0 | 0 | 0 | 110 / 110 |
| Spring 2027 | 201/198/3 | 1861 | 0 | 116 | 0 | 136 | 0 | 10 | 56 | 0 / 0 |

## Faculty/student/course roster consistency

- Student schedule conflicts: **116 pairs across 19 students**, all in Spring 2027; Fall 2026 has none.
- Duplicate course groups affect **32 Fall students** and **50 Spring students**.
- Target sections with faculty/User/Login assignment issues: **56**.
- Enrollment rows with section-semester mismatch: **0**.
- Enrollment rows on canceled sections: **10**. These remain in Enrollment but are not active under application filtering.
- Active section roster rows checked: **3480**. Student Registration and Faculty roster use matching CRN + semester joins; Student Schedule can omit rows only if its extra required Time_Slot/faculty User joins are absent. Omitted active enrollment rows found: **0**.
- `Faculty_History` comparison rows: **110**, mismatches to linked current sections: **110**. All 110 have a different faculty ID; 9 also have a different course ID; none have a semester-ID mismatch. These are historical signals only; `Course_Section.faculty_ID` is used as the current assignment.

## Five verified Faculty → Student → Course login examples

| Term | Faculty (ID) | Student (ID) | CRN / Course / Section | Meeting days and time |
|---|---|---|---|---|
| Fall 2026 | Gabriel Thomas (200072) | Liam Walker (100001) | 140025 / CS1001 / 1 | Monday 8:00 AM–9:40 AM; Wednesday 8:00 AM–9:40 AM |
| Fall 2026 | Nia Torres (200060) | Nia Roberts (100004) | 140080 / VD1001 / 3 | Monday 9:50 AM–11:30 AM; Wednesday 9:50 AM–11:30 AM |
| Spring 2027 | Noah Jackson (200232) | Joseph Etienne (100012) | 20143 / MA2007 / 1 | Tuesday 7:00 PM–8:40 PM; Thursday 7:00 PM–8:40 PM |
| Spring 2027 | Christopher Johnson (200227) | Matthew Gonzalez (100017) | 20138 / MA1004 / 4 | Tuesday 9:50 AM–11:30 AM; Thursday 9:50 AM–11:30 AM |
| Spring 2027 | David Baptiste (200163) | Aaron Green (100018) | 20014 / CS2007 / 3 | Tuesday 5:10 PM–6:50 PM; Thursday 5:10 PM–6:50 PM |


## Confirmed application behavior

- `faculty/dashboard.php` lists all assigned target-semester sections without filtering `Course_Section.is_Cancelled`. It therefore shows canceled Spring 2027 CRNs 20077, 140028, and 140035 in Faculty My Courses; Faculty Schedule/Rosters and Student Registration/Schedule exclude them. Exact rows are in `faculty_dashboard_canceled_sections.csv`. This is a PHP display/query inconsistency, not evidence to restore or alter canceled records.

## Recommended corrections

### Safe fixes

- No automatic database updates are supported by this audit. Core Course_Section/Enrollment foreign keys are intact; missing Faculty Login accounts are not safe to create without account authorization. The safe application-only correction is to filter canceled sections out of Faculty Dashboard My Courses, matching the other active-schedule pages; this was not applied.

### Manual review

- Manually resolve the 116 Spring student overlap pairs by checking course priority, prerequisites, and available nonconflicting same-course sections. No faculty or room overlap was detected.
- For duplicate courses, confirm intended CRN from registration/audit source; preserve rows linked to grades, attendance, or history.
- Review the 10 Enrollment rows on canceled Spring CRN 20077 before any transfer/drop; no Enrollment/section semester mismatches were found.
- Review Faculty_History differences as historical reconciliation only. Do not use it to overwrite current Course_Section assignments.
- No room collisions were detected in the available room/time data.

## Limitations

- Separate lecture and lab meeting times/instructors are not representable in this schema, so no independent lecture/lab schedule can be inferred.
- No add/drop, grade, or attendance write actions were executed. The audit is database-backed and code-path-aware; it did not log in through a browser as each person.

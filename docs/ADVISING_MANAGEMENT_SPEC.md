# Academic Advising Management System — Functional Specification

## 1. Purpose
Provide a role-based (Student, Faculty Advisor, Admin) system for managing academic advising appointments, enforcing departmental alignment between advisors and advisees.

## 2. Roles & Permissions
- **Student**: view advisor availability, request appointments, view status.
- **Faculty Advisor**: view pending requests, accept/deny, manage availability.
- **Admin**: manage advisor-student assignments, override alignment exceptions, audit.

## 3. Appointment Management (Faculty workflow)
- Advisor dashboard lists pending appointment requests with student name, major, requested date/time, and topic.
- Actions per request: **Accept** (confirms slot, notifies student), **Deny** (requires reason, notifies student), **Reschedule** (proposes alternate slot).
- State machine: `Pending -> Accepted | Denied | Rescheduled -> Completed | Cancelled`.
- Accepted appointments lock the slot; denied slots return to availability.

## 4. Student Scheduling
- Student views assigned advisor's available slots (calendar view) filtered to open, non-overlapping times.
- Request flow: select slot -> enter topic/notes -> submit -> status `Pending`.
- Validation: student must have an assigned advisor; slot must be free; no duplicate pending request for same advisor.
- Notifications (email/in-app) on submit, accept, deny, reschedule.

## 5. Advisor-Student Alignment Logic
- Rule: a faculty advisor may only be assigned to / advise students whose major department is in the advisor's department set (`Faculty_Department`).
- Example: Software Engineering faculty (dept. Computer Science/Mathematics) may advise Computer Science or Mathematics majors; may NOT advise Education majors.
- Enforcement points:
  - Assignment (admin or auto): reject if advisor's departments ∩ student's major department = ∅.
  - Appointment request: backend re-validates alignment before creating request.
  - UI: only aligned advisors shown as selectable.
- Exception: Admin may override with audit log entry (reason required).

## 6. Data Model (key entities)
- `Advisor(faculty_ID, student_ID, date_Of_Appnt)`
- `Faculty_Department(faculty_ID, dept_ID, percent_Time, date_Of_Appointment)`
- `Major(major_ID, dept_ID, major_Name)`
- `Student(student_ID, major_ID, ...)`
- `Appointment(appointment_ID, student_ID, faculty_ID, slot_start, slot_end, topic, status, reason, created_At, updated_At)`
- `Availability(faculty_ID, day_of_week, start_time, end_time, effective_from, effective_to)`

## 7. Business Rules
- BR-1: Part-time faculty may not advise.
- BR-2: Full-time faculty advise at most 15 students.
- BR-3: Advisor and advisee must share at least one department (alignment).
- BR-4: A student has at most one primary advisor; additional advisors allowed for cross-discipline (aligned) cases.
- BR-5: Appointment slot must fall within advisor availability and not overlap existing accepted appointments.
- BR-6: Denied appointments require a reason; reasons are visible to student.
- BR-7: Historical records are never deleted; cancellations are soft (status `Cancelled`).

## 8. Non-Functional
- Role-based access control; CSRF on all state-changing forms; prepared statements; audit logging of assignment changes.
- Performance: advisor dashboard loads pending requests in <2s for up to 500 advisees.

## 9. Acceptance Criteria
- Misaligned assignment attempt returns error and is not persisted.
- Student cannot request appointment with non-aligned advisor.
- Advisor accept/deny updates status and notifies student within the same transaction.

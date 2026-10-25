# Master Schedule Management System

## Where the schedule lives

The requirements document refers to `INFORMATION_RELATION` and a
`Master_Schedule` table. After inspecting this repository:

| Requirements term | Existing Ashford equivalent |
| --- | --- |
| `INFORMATION_RELATION` (academic semester, dates, restrictions) | `Semester` (`semester_Start`, `registration_Start`, `registration_End`, `drop_Deadline`, `grade_Deadline`) |
| `Course_Section` (section, faculty, room, time slot, seats) | `Course_Section` |
| `Master_Schedule` (schedule-level record) | **new table**, added by migration `0004_master_schedule.sql` |

The schedule itself is the set of `Course_Section` rows for a semester. The
new `Master_Schedule` table adds only what the existing schema could not
express:

- one row per semester (`semester_ID` is the primary key),
- `status` (`Draft` or `Published`),
- `version` for optimistic concurrency,
- creation / update / publish actors and timestamps.

No duplicate assignment data is stored.

## Pages

| Role | Page | Access |
| --- | --- | --- |
| Administrator | `admin/master-schedule.php` | `require_role(['Admin'])`; write actions need `Full-Access` |
| Student | `student/master-schedule.php` | `require_role(['Student'])`; read-only |
| Faculty | `faculty/master-schedule.php` | `require_role(['Faculty'])`; read-only |
| Statistics staff | `statstaff/reports.php` (Section capacity and utilization report) | `require_role(['StatStaff'])`; read-only, aggregate only |
| Public | `public/schedule.php` | unauthenticated; approved fields only |

## Administrator dashboard

`admin/master-schedule.php` shows live statistics computed by
`validate_semester_schedule()` (never hardcoded):

- Total sections, Scheduled, Unscheduled,
- Sections missing room, sections missing faculty,
- Room conflicts, faculty conflicts, other validation errors.

Definitions used consistently by backend, UI, and tests:

- **Scheduled** — section has a room, an authorized faculty member, and a
  valid meeting interval, and raises no validation errors.
- **Unscheduled** — any missing assignment or rule violation.
- **Room conflict** — two sections share the same room (lecture or lab) and
  overlap on both a meeting day and a time range.
- **Faculty conflict** — two sections share a faculty member and overlap on
  both a meeting day and a time range.

## Overlap rule

`includes/functions.php`:

```php
times_overlap($newStart, $newEnd, $existingStart, $existingEnd)
```

Implements `newStart < existingEnd && existingStart < newEnd`. Back-to-back
classes (one ending exactly when the next begins) do **not** overlap, and
the rule is applied only when meeting days intersect (`days_overlap()`).

## Workflows

### Create New Master Schedule

1. `POST action=create` -> `master_schedule_create()`.
2. Rejected when a schedule already exists for the semester.
3. The new record starts as `Draft` (a draft may contain unresolved issues
   and is never shown as conflict-free).

### Update Current Master Schedule

1. `POST action=update_section` for one CRN at a time.
2. The posted `version` must match the stored version (optimistic
   concurrency); stale saves are rejected.
3. `validate_section_assignment()` runs the full rule set before any write.
4. After the semester start date, room/faculty/meeting-time changes are
   locked; only seat counts may change.
5. Section change, version bump, and `Audit_Log` entry happen in one
   transaction.
6. Section edits made from `admin/sections.php` bump the same version.

### Publish

`POST action=publish` -> `master_schedule_publish()` only succeeds when
`validate_semester_schedule()` reports zero issues, and records the publish
event in `Audit_Log`.

## Validation rules

Per section (`validate_schedule_row`):

- room assigned (lecture room or lab, not both, must exist),
- faculty assigned and a member of the course's department,
- full-time faculty <= 2 sections, part-time <= 1 section,
- `available_Seats` <= room capacity,
- meeting interval exists and has positive length.

Per semester (`validate_semester_schedule`):

- all per-section rules,
- room conflicts across the semester,
- faculty conflicts across the semester.

Faculty overlap checks reuse `faculty_can_teach_section()` for create/update.

## Verification

The validation rules are implemented in `includes/functions/` and are
covered by the portal behaviour itself:

- `time.php` — `times_overlap()` implements the boundary rule
  (back-to-back classes do not overlap); `days_overlap()` applies it only
  when meeting days intersect.
- `validation_row.php` / `validation_assignment.php` — the rules the
  dashboard reports and the update workflow enforces.
- `master_schedule.php` — creation, version checks, and the publish gate.

Manual verification path on a running instance:

1. Create a semester and sections; leave one section without a room.
2. Confirm the dashboard shows it under "Unscheduled" / "Missing room"
   and that Publish stays disabled.
3. Assign two sections to the same room and overlapping slot; confirm the
   room conflict appears and both CRNs are listed.
4. Save an edit from a stale page copy; confirm the version conflict
   message.
5. Resolve every issue; confirm Publish succeeds and writes the
   `Publish` audit event.

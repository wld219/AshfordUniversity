Ashford Admin User Management - Phase 2

Includes the prior 3-file UI/major-minor update plus:
- Search across all users (ID/name/email) and filter by role
- Pagination instead of showing only the first 25 users
- Student detail view (personal, academic, holds)
- Add/remove holds from within a student record using existing Student_Hold and Hold tables
- More consistent contrast, focus states, and student detail layout

Not yet implemented: full transcript, grade, enrollment, degree audit, advisor views; these require validating their exact schema/permissions.
Install over Ashford-New after backup. Test on a local copy first. No SQL schema changes.

ASHFORD UNIVERSITY — MASTER SCHEDULE STEPS 1 & 2

Includes:
 admin/master_schedule_data.php
 admin/partials/master_schedule_header.php
 admin/partials/master_schedule_filters.php

Changes:
 - Semester dropdown limited to Fall 2026 and Spring 2027, Fall 2026 default
 - Changing semester preserves search filters, resets pagination and edit selection
 - Two-column search/filter layout; responsive on smaller screens
 - Meeting Days checkbox alignment; no database changes
 - Existing search field names, server-side filtering, and 50-row pagination preserved

INSTALL (from ~/Downloads/Ashford-New):
 mkdir -p ~/Downloads/ashford_master_schedule_backup
 cp admin/master_schedule_data.php admin/partials/master_schedule_header.php admin/partials/master_schedule_filters.php ~/Downloads/ashford_master_schedule_backup/
 unzip -o ~/Downloads/ashford_master_schedule_steps_1_2.zip

NOTES:
 These files are based on the previously shared Ashford-New project and
 master_schedule_data.php from ashford_complete_master_schedule_fix.zip.
 Your current Mac files were not available to compare; back up first.
 Requires existing Course_Section_Mode and Building_Display from previous steps.
 No Friday-only timetable or multi-select edit popup changes are included.

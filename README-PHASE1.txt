ASHFORD USER MANAGEMENT — PHASE 1: DIRECTORY TABLE

Scope: UI-only changes to the User Directory, using the existing server-side search,
role filter, and 25-record pagination. The source is based on the earlier
Ashford-Admin-User-Management-Final.zip package.

Files replaced:
 admin/partials/users_header.php
 admin/partials/users_list.php
 admin/partials/users_view.php

No database schema changes. No Master Schedule files changed.
Existing Edit, Unlock, and student View links remain. The View popup and
separate Student Holds tab are NOT implemented in this phase.

IMPORTANT: If your current users_view.php has newer customizations than the
previous Final package, compare the files before overwriting.

INSTALL (Mac):
 cd ~/Downloads/Ashford-New
 mkdir -p backups/users-phase1
 cp admin/partials/users_{header,list,view}.php backups/users-phase1/
 unzip -o ~/Downloads/Ashford-User-Directory-Phase1.zip
 php -l admin/partials/users_header.php
 php -l admin/partials/users_list.php
 php -l admin/partials/users_view.php

TEST:
 1. Open http://localhost:8001/admin/users.php
 2. Search a known user ID, then Clear.
 3. Filter Student, Faculty, Admin, Statistics Staff.
 4. Navigate Next/Previous pages.
 5. Verify Edit and student View still open.
 6. Verify Read-Only accounts do not see restricted actions.

ROLLBACK:
 cp backups/users-phase1/users_*.php admin/partials/

#!/bin/bash
set -euo pipefail
cd "$(dirname "$0")"
python3 - <<'PY'
from pathlib import Path
from datetime import datetime
p=Path("login.php")
if not p.exists():
    raise SystemExit("ERROR: Run this installer from the Ashford-New project folder.")
s=p.read_text()
old="""if (strcasecmp((string)$user['user_Email'], 'liamwalker@ashford.edu') === 0
    && (string)$user['user_Type'] === 'Student') {"""
new="""if (
    (
        strcasecmp((string)$user['user_Email'], 'liamwalker@ashford.edu') === 0
        && (string)$user['user_Type'] === 'Student'
    )
    ||
    (
        (int)$user['user_ID'] === 300008
        && (string)$user['user_Type'] === 'Admin'
    )
) {"""
if new in s:
    print("Already installed; no changes needed.")
elif s.count(old)==1:
    backup=p.with_name("login.php.before-waleed-2fa-"+datetime.now().strftime("%Y%m%d-%H%M%S"))
    backup.write_text(s)
    p.write_text(s.replace(old,new,1).replace(
        "// Local demonstration: require a one-time login code for Liam Walker only.\n// All other roles and users retain their existing login behavior.",
        "// Local demonstration: require a one-time login code for Liam Walker and Admin 300008.\n// All other accounts retain their existing login behavior."
    ))
    print("Updated login.php. Backup:",backup)
else:
    raise SystemExit("ERROR: Existing Liam 2FA condition not found exactly once. No changes made.")
PY
php -l login.php
echo "Waleed Admin 300008 now uses the existing Liam verification UI and logic."

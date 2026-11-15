#!/usr/bin/env bash
#
# import_database.sh - Build the Ashford University portal database locally.
#
# Usage:
#   ./database/import_database.sh                # uses default XAMPP mysql client
#   ./database/import_database.sh /usr/local/mysql/bin/mysql
#
# Prerequisites: a running MySQL/MariaDB server reachable as root (no password)
#                OR set MYSQL_CLI to your mysql client path.
#
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SQL_FILE="${PROJECT_ROOT}/database/import_all.sql"
MYSQL_CLI="${1:-}"

if [ -z "${MYSQL_CLI}" ]; then
	if [ -x /Applications/XAMPP/xamppfiles/bin/mysql ]; then
		MYSQL_CLI="/Applications/XAMPP/xamppfiles/bin/mysql"
	elif command -v mysql >/dev/null 2>&1; then
		MYSQL_CLI="mysql"
	else
		echo "ERROR: mysql client not found. Pass its path as an argument."
		echo "  Example: $0 /usr/local/mysql/bin/mysql"
		exit 1
	fi
fi

echo "==> Using mysql client: ${MYSQL_CLI}"
echo "==> Importing: ${SQL_FILE}"

"${MYSQL_CLI}" -u root -h 127.0.0.1 -P 3306 < "${SQL_FILE}"

echo "==> Import complete."
echo "==> The site is ready to run:"
echo "    ${PROJECT_ROOT}"
echo "    /Applications/XAMPP/xamppfiles/bin/php -S localhost:8000 -t \"${PROJECT_ROOT}\" router.php"
echo "    Then open: http://localhost:8000/"

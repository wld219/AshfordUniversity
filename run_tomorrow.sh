#!/usr/bin/env bash
set -euo pipefail
cd "$HOME/Downloads/Ashford-New"
STAMP=$(date +%Y%m%d_%H%M%S)
BACKUP="$HOME/Downloads/ashford_before_schedule_changes_${STAMP}.sql"
echo "Backing up database to $BACKUP"
mysqldump -u root -p ashford_university > "$BACKUP"
echo "Inspecting meeting patterns and shared time slots (read-only)."
mysql -u root -p ashford_university <<'SQL'
SELECT s.semester_Name, GROUP_CONCAT(d.day_Of_Week ORDER BY d.day_ID SEPARATOR '/') AS meeting_pattern, COUNT(DISTINCT cs.CRN) AS sections
FROM Course_Section cs
JOIN Semester s ON s.semester_ID=cs.semester_ID
JOIN Time_Slot_Day tsd ON tsd.time_Slot_ID=cs.time_Slot_ID
JOIN Day d ON d.day_ID=tsd.day_ID
WHERE cs.semester_ID IN (1,2) AND cs.is_Cancelled=0
GROUP BY s.semester_Name, cs.time_Slot_ID
ORDER BY s.semester_Name, meeting_pattern;
SELECT cs.semester_ID, cs.CRN, cs.time_Slot_ID,
GROUP_CONCAT(d.day_Of_Week ORDER BY d.day_ID SEPARATOR '/') AS days,
(SELECT COUNT(*) FROM Course_Section other WHERE other.time_Slot_ID=cs.time_Slot_ID AND other.is_Cancelled=0) AS sections_sharing_slot
FROM Course_Section cs
JOIN Time_Slot_Day tsd ON tsd.time_Slot_ID=cs.time_Slot_ID
JOIN Day d ON d.day_ID=tsd.day_ID
WHERE cs.semester_ID IN (1,2) AND cs.is_Cancelled=0
GROUP BY cs.semester_ID, cs.CRN, cs.time_Slot_ID
HAVING SUM(d.day_Of_Week='Friday')>0
ORDER BY cs.semester_ID, cs.CRN;
SQL
echo "Completed. No schedule changes made. Share the output before changing time slots."

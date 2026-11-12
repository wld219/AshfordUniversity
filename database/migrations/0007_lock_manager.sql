-- Migration 0007: lock-manager flag on administrators.
--
-- Adds the lock_manager column used by the Users page so the lock manager
-- can unlock accounts and edit administrator security levels. Safe to run
-- repeatedly (ADD COLUMN IF NOT EXISTS is not portable, so guard with
-- information_schema).

USE ashford_university;

SET @colExists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'Admin'
      AND COLUMN_NAME = 'lock_manager'
);

SET @sql := IF(@colExists = 0,
    'ALTER TABLE Admin ADD COLUMN lock_manager TINYINT(1) NOT NULL DEFAULT 0 AFTER employment_Status',
    'SELECT "lock_manager column already exists" AS status'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Backfill: every existing Full-Access admin is a candidate lock manager;
-- none are set by default so exactly one administrator must be designated.
UPDATE Admin SET lock_manager = 0 WHERE lock_manager IS NULL;

SELECT 'Migration 0007 complete.' AS status;

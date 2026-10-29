-- Run only after backing up ashford_university. Existing admins retain equivalent access.
-- Numeric levels: 1 Super Admin, 2 Admin, 3 Read-only.
-- Keep legacy security_Level for compatibility with other PHP endpoints.
ALTER TABLE `Admin` ADD COLUMN `security_Level_Number` TINYINT UNSIGNED NULL DEFAULT NULL;
UPDATE `Admin` SET `security_Level_Number` = CASE WHEN `security_Level` = 'Full-Access' THEN 1 ELSE 3 END WHERE `security_Level_Number` IS NULL;
ALTER TABLE `Admin` MODIFY COLUMN `security_Level_Number` TINYINT UNSIGNED NOT NULL DEFAULT 3;
-- To promote a specific existing administrator to Level 2 after reviewing permissions:
-- UPDATE `Admin` SET security_Level_Number = 2, security_Level = 'Full-Access' WHERE user_ID = YOUR_ID;
-- IMPORTANT: This column alone does not enforce privileges. See README.

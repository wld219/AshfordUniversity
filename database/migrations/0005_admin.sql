-- Migration 0005: Administrator access-control table.
--
-- Adds the Admin table used by every admin portal page to distinguish
-- Full-Access administrators from Read-Only ones. Databases created from
-- schema.sql already include this table; run this migration only on
-- databases restored from a pre-0005 backup.
--
-- Safe to run repeatedly: the table is created only if it does not exist.

CREATE TABLE IF NOT EXISTS Admin (
    user_ID INT PRIMARY KEY,
    security_Level ENUM('Full-Access', 'Read-Only') NOT NULL DEFAULT 'Read-Only',
    FOREIGN KEY (user_ID) REFERENCES User(user_ID)
);

-- Backfill: every existing Admin-type login becomes Full-Access.
INSERT INTO Admin (user_ID, security_Level)
SELECT l.user_ID, 'Full-Access'
FROM Login l
WHERE l.user_Type = 'Admin'
  AND NOT EXISTS (SELECT 1 FROM Admin a WHERE a.user_ID = l.user_ID);

-- Users with user_Type 'Admin' keep Full-Access; all other user_Type
-- values are simply absent from this table and therefore have no
-- administrator privileges until explicitly granted.

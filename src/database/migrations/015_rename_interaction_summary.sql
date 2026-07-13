-- 011_rename_interaction_summary.sql
-- Rename interactions.summary -> interaction_subject to match code + schema.sql.
--
-- Idempotent + portable: only renames if `summary` still exists, so it is safe to
-- run whether or not the column was already renamed. Uses CHANGE (not the MySQL
-- 8.0-only "RENAME COLUMN") so it also works on MySQL 5.7 / MariaDB.
SET @needs := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'interactions' AND COLUMN_NAME = 'summary');
SET @sql := IF(@needs > 0,
  'ALTER TABLE interactions CHANGE summary interaction_subject TEXT NOT NULL',
  'DO 0');
PREPARE _m011 FROM @sql;
EXECUTE _m011;
DEALLOCATE PREPARE _m011;

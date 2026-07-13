-- 012_add_notes_to_interactions.sql
-- Fixes schema drift: older DBs are missing the `notes` column on interactions,
-- but the code + schema.sql require it. Add it to match.
-- Detection (db_setup.php registry): col:interactions.notes
--   - Drifted DB (missing `notes`) -> runs.
--   - Correct DB (already has `notes`) -> skipped.
ALTER TABLE interactions ADD COLUMN notes TEXT NOT NULL AFTER interaction_subject;

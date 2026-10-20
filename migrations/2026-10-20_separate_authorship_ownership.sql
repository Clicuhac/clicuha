-- One-time migration. Back up nicknames first and pause application writes.
-- Deploy the matching PHP code only after this migration completes.
-- CHANGE preserves all legacy authors, including unknown authors (NULL).
ALTER TABLE nicknames
    CHANGE COLUMN user_id created_by BIGINT UNSIGNED NULL,
    ADD COLUMN owner_id BIGINT UNSIGNED NULL AFTER created_by,
    ADD INDEX idx_nicknames_owner_id (owner_id);

-- Legacy free rows remain free; anonymity and archived rows are unchanged.
START TRANSACTION;
UPDATE nicknames SET owner_id = created_by;
COMMIT;
-- Do not rerun the UPDATE after the application resumes writes.

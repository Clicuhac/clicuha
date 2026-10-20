-- One-time production migration for Issue #50.
-- Back up the database first. Keep the legacy user_id column during this release
-- so the currently deployed code continues to work until the new code is live.
ALTER TABLE nicknames
    ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER user_id,
    ADD COLUMN owner_id BIGINT UNSIGNED NULL AFTER created_by,
    ADD INDEX idx_nicknames_created_by (created_by),
    ADD INDEX idx_nicknames_owner_id (owner_id);

-- Preserve legacy semantics: an existing user_id is both author and owner;
-- legacy NULL rows remain free and have unknown authorship.
START TRANSACTION;
UPDATE nicknames
SET created_by = user_id,
    owner_id = user_id;
COMMIT;

-- IMPORTANT: deploy the matching PHP code immediately after verification.
-- The legacy user_id column is intentionally retained for rollback safety and
-- can be removed in a later cleanup migration after this release is stable.

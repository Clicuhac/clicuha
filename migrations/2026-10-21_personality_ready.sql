-- Apply once after backup and verification of SHOW CREATE TABLE nicknames.
-- nickname_id must match the existing signed INT nicknames.id; requires InnoDB.
ALTER TABLE nicknames
    ADD COLUMN first_published_at DATETIME NULL;
-- Publication history is unknown for existing cabinet/archive records. Treat
-- ALL pre-rollout records conservatively as having crossed the creator boundary.
-- Preserve is_published, deleted_at, created_by and owner_id exactly as they are.
UPDATE nicknames SET first_published_at = COALESCE(created_at, NOW());

CREATE TABLE clicuha_personalities (
    nickname_id INT NOT NULL,
    schema_version SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    preset_key VARCHAR(64) NULL,
    core_json JSON NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (nickname_id),
    CONSTRAINT fk_personality_nickname FOREIGN KEY (nickname_id)
        REFERENCES nicknames(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

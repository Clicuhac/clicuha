# Deploy Issue #50

Release checklist for PR #51 (`issue-50-authorship-ownership`).

## Before merge

1. Back up the production database.
2. In production phpMyAdmin record:
   - `SHOW CREATE TABLE nicknames;`
   - `SELECT COUNT(*) AS total, SUM(user_id IS NULL) AS free_rows FROM nicknames;`
   - foreign keys/indexes involving `user_id`.
3. Confirm `user_id` is compatible with `BIGINT UNSIGNED NULL`.

## Migration

Run `migrations/2026-10-20_separate_authorship_ownership.sql` exactly once.

This migration is intentionally additive: it keeps legacy `user_id`, adds `created_by` and `owner_id`, then backfills both from `user_id`. Keeping `user_id` avoids breaking the currently deployed code before the new code goes live and gives us a safer rollback path.

Immediately verify:

```sql
SELECT COUNT(*) AS total,
       SUM(user_id IS NULL) AS old_free_rows,
       SUM(created_by IS NULL) AS unknown_authors,
       SUM(owner_id IS NULL) AS free_rows,
       SUM(created_by <=> user_id) AS authors_match_legacy,
       SUM(owner_id <=> user_id) AS owners_match_legacy
FROM nicknames;
```

Expected for the current production snapshot: total `48`, old/free rows `6`, unknown authors `6`, free rows `6`, and both match counts `48`.

## Merge/deploy

After the migration verification succeeds, merge PR #51 into `main` immediately. A push to `main` triggers the production deploy hook.

## Post-deploy smoke checks

- Gallery loads and shows `Вільна` / `Вже належить` correctly.
- Existing owned Clicuhas still belong to their previous owners.
- Create one owned test Clicuha.
- Create one free test Clicuha; verify author is retained but it is absent from `Мої клікухи` as ownership.
- Check owner-only edit and avatar upload.
- Check archive/restore and admin pages.
- Confirm anonymous author display remains independent from ownership.

## Rollback

Because legacy `user_id` is retained during this release, the previous PHP code can be restored without first undoing the schema additions. Do not remove `user_id` until Issue #50 has been stable in production and a separate cleanup migration is reviewed.

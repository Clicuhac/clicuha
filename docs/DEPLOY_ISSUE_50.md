# Deploy Issue #50

Release checklist for PR #51 (`issue-50-authorship-ownership`).

## Before merge

1. Back up the production database, especially `nicknames`.
2. In production phpMyAdmin run and save the output of:
   - `SHOW CREATE TABLE nicknames;`
   - `SELECT COUNT(*) AS total, SUM(user_id IS NULL) AS free_rows FROM nicknames;`
3. Confirm the production `user_id` type is compatible with `BIGINT UNSIGNED NULL` and note any foreign keys/indexes involving `user_id`.
4. Pause application writes while migrating.

## Migration

Run `migrations/2026-10-20_separate_authorship_ownership.sql` exactly once.

Immediately verify:

```sql
SELECT COUNT(*) AS total,
       SUM(created_by IS NULL) AS unknown_authors,
       SUM(owner_id IS NULL) AS free_rows,
       SUM(owner_id = created_by) AS legacy_owned_rows
FROM nicknames;
```

The row count must match the pre-migration total. Legacy rows with `user_id IS NULL` must remain free (`owner_id IS NULL`).

## Merge/deploy

Only after the migration succeeds, merge PR #51 into `main`. A push to `main` triggers the repository's deploy hook, so do not merge first.

## Post-deploy smoke checks

- Gallery loads and shows `Вільна` / `Вже належить` correctly.
- Existing owned Clicuhas still belong to their previous owners.
- Create one owned test Clicuha.
- Create one free test Clicuha; verify author is retained but it is absent from `Мої клікухи` as ownership.
- Check owner-only edit and avatar upload.
- Check archive/restore and admin pages.
- Confirm anonymous author display remains independent from ownership.

## Rollback

Do not attempt a code-only rollback after the database migration: old code expects `nicknames.user_id`. If rollback is required, restore the database backup together with the previous code revision.

# Database migrations

Migration files in this directory are operational release steps, not automatically executed by deployment.

For Issue #50, follow `docs/DEPLOY_ISSUE_50.md` before merging PR #51. The PHP code in that PR expects the migrated `nicknames` schema and is not backward-compatible with the old `nicknames.user_id` column.

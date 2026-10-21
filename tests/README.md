# Personality checks

Use PHP 8.x with PDO MySQL, mbstring, fileinfo and a local MySQL/MariaDB server.
Use an isolated database ending `_test` with the post-#50 base schema, then apply
`migrations/2026-10-21_personality_ready.sql` once. Point the ignored `.env` to
that database. Never use production. The HTTP runner rejects non-loopback hosts
and the DB helper rejects database names not ending `_test` before writing.
Start PHP from the checkout with `php -S 127.0.0.1:8080 -t .`.

```sh
php tests/personality_validation.php
php tests/personality_migration.php
CLICUHA_PHP=php python tests/personality_http.py http://127.0.0.1:8080
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

The onboarding runtime provides `/workspace/setup/bin/php`; pass that path as
`CLICUHA_PHP` when using it instead of system PHP. Start its local MariaDB/PHP
with `bash /workspace/setup/start.sh`.

The migration test uses disposable tables and verifies exact preservation of
legacy fields, no invented core, and FK cascade. The HTTP suite exercises A–F,
CSRF, disabled publish UI, alternate creation, admin access, archive restoration,
and the permanent creator-rights boundary. Fixtures are deleted on exit. Preset
and avatar snapshot tests temporarily modify their data source files and restore
them in `finally`; use an isolated checkout without concurrent modifications.
The validation test rejects duplicate/unknown traits, blank text and unknown
schema versions and checks the creator/owner/admin access matrix.

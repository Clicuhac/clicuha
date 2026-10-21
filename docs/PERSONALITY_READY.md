# Personality and READY

Baseline: `b0941fc6b4f8867b6ad7ea6689e177e37a55d445` (main after merged
PR #51 / issue #50). `created_by` is provenance; `owner_id` is ownership.
Session `user_id` and audit `logs.user_id` identify actors, never nickname ownership.
The transitional `nicknames.user_id` is not read or written by new permissions.

## Independent layers

Character core → knowledge → social behavior → speech style → current state → memory / experience.
These are separate layers, not one personality score. Only the minimal character
core is implemented now. No knowledge engine, AI dialogues, memory engine,
autonomy, social simulation or personality evolution is part of this release.

Knowledge about each concept will have independent dimensions: recognition,
everyday understanding, practical experience, theoretical depth, and confidence
in one's own knowledge. There is no aggregate 0–5 knowledge level. An unspecified
value means **not assessed**, not zero. Absolute zero needs an explicit reason.
Confidence is independent of actual depth and can be inaccurate.

Speech style is separate from character: manners, vocabulary, rhythm, humor,
emotionality, regionality, favorite expressions and reactions to disagreement.
Social behavior describes interaction; current state is transient; memory and
experience describe events and learned context. Do not store these as character traits.

## Minimal core v1

`clicuha_personalities`: `nickname_id` (PK and FK), `schema_version`, nullable
`preset_key`, `core_json`, `updated_at`. No mutable `personality_ready` column.

Example actual stored core:

```json
{"traits":["curious","empathetic","independent"],"motivation":"Досліджувати світ","vulnerability":"Важко довіряти незнайомим"}
```

Server readiness requires three distinct known trait keys, nonblank motivation
and nonblank vulnerability. The small v1 vocabulary is curious, empathetic,
independent, creative, persistent, cautious, sociable, reflective. Labels are
localized in the form. Unknown traits do not count. Name, description, slug and
avatar do not count. Unknown schema versions and invalid JSON are not ready.
Both manual and preset input use the same validator. A preset copies its values
into `core_json`; `preset_key` is provenance only. Later preset edits never
change previously saved cores. Editing preset-derived values clears preset provenance.

## Lifecycle and permissions

Publication and readiness are independent: cabinet + not ready, cabinet + ready,
public + ready and public + legacy/no-core are valid states. Archive is represented
by `deleted_at`, and takes priority over `is_published`.

All creation paths create cabinet records (`is_published=0`). An active never
published draft can be formed by its creator, including when `owner_id IS NULL`.
Owners can manage their active records; administrators retain system management.
After first publication, creator-only editing ends permanently. There is no
`owner_id ?? created_by` fallback. `first_published_at` records this boundary,
including when an owner later hides the record. Free published records remain free.

`На формуванні` lists active never published drafts created by the actor; it is
separate from `Мої клікухи`, which remains an ownership list. Draft viewing permits
the creator, owner or administrator; anonymous users and unrelated users cannot
access drafts through profile, ROOM or direct URLs. Archived records are private
except for their creator, owner and administrators.

Every publication transition checks the actor, current lifecycle and current
persisted core on the server. The nickname row is locked while saving core or
publishing, so concurrent requests cannot publish a stale core. Incomplete core
cannot replace a ready core while the record is public; return it to cabinet first.
The disabled button is explanatory UI, not authorization. CSRF is required.
Archive/restore never creates a bypass: restore goes to cabinet, requiring READY
before the next publication. No purchases, transfers, gifts or claims are implemented.

## Legacy and rollout

Back up production and inspect `SHOW CREATE TABLE nicknames` first. Migration
`2026-10-21_personality_ready.sql` assumes InnoDB and the documented signed INT
nickname PK. Verify that assumption before applying. Apply the additive migration
before deploying PHP. It does not invent personality rows or modify ownership.
Existing public records get a conservative first-publication marker using their
creation time (historical publication time is unknown). Existing archived records
are also marked as previously published conservatively. Existing cabinet history
cannot be inferred: creator-only formation is enabled only for new records after
rollout; legacy drafts get a conservative marker too. Owners/admins can still form them.

Existing public records without a core remain visible and are not automatically
READY. They are not filtered out of the gallery. No mass trait backfill. Returning
one to cabinet means its next publication must pass READY. Rollback PHP can keep
additive schema, but old code would bypass READY: pause creation/publication during
rollback. Do not drop personality data to roll back.

## Verification

Integration scenarios A–F cover owned/free lifecycle, direct URL/POST denial after
free publication, direct NOT READY publication, legacy visibility, draft privacy,
and persisted preset snapshots. See `tests/personality_http.py`. Tests require a
separate local development database; never run them against production.

# Migrations

Plain SQL migrations, run by `bin/migrate`. Version-tracked in
`global_config` under keys `db_version` and `app_version`.

## Filename convention

```text
<semver>[-slug].sql
```

Examples: `1.0.1-add-audit-trail.sql`, `1.1.0.sql`, `2.0.0-rename-spi-tables.sql`.

Files are sorted by semver first, then by slug alphabetically. Anything that
doesn't start with `\d+\.\d+\.\d+` is ignored.

## Versioning

- `composer.json` top-level `version` field holds the current **app**
  version — bump it on every release, even code/seed-only releases that
  ship no schema changes.
- `global_config.db_version` holds the highest **migration** applied.
- `global_config.app_version` is stamped from `composer.json` by
  `bin/migrate`. After a clean run both DB keys should match `composer.json`.

`1.0.0` is the arbitrary baseline (repo is ~10 years old; everything before
this lives in the legacy `data/alter.sql` and is not re-played).

## Authoring a migration

1. Pick the next semver. Patch bump (`1.0.1`) for additive, no-risk changes;
   minor (`1.1.0`) for new tables/features; major (`2.0.0`) for breaking
   schema changes.
2. Create `migrations/<version>-<slug>.sql`.
3. Write plain SQL. The runner uses `PhpMyAdmin\SqlParser` to split, so
   multi-statement files, triggers, and procedures all work.
4. Bump `VERSION` to match the new file.
5. Run `php bin/migrate --dry-run` to preview, then `php bin/migrate`.

## Idempotency

`bin/migrate` auto-skips these when the target already exists:

- `ALTER TABLE … ADD COLUMN …`
- `ALTER TABLE … ADD INDEX/KEY …` / `CREATE INDEX …`
- `ALTER TABLE … ADD CONSTRAINT … FOREIGN KEY …`
- `ALTER TABLE … DROP COLUMN/INDEX/FOREIGN KEY …`
- `ALTER TABLE … ADD PRIMARY KEY …` (only when no PK exists; set
  `MIG_REPLACE_PK=1` to force-replace a differing PK)

Everything else (`CREATE TABLE`, `INSERT`, `UPDATE`, `DROP TABLE`, procedure
bodies, etc.) is sent as-is. Prefer:

- `CREATE TABLE IF NOT EXISTS …`
- `INSERT … ON DUPLICATE KEY UPDATE …` (when you have a unique key)
- `DROP TABLE IF EXISTS …`

Error codes are classified: `1050/1060/1061/1068/1091/1826` are treated as
benign `WARN` (idempotent re-run). `1054` (Unknown column) and `1146`
(Unknown table) are only `WARN` on schema-change statements; on
INSERT/UPDATE/DELETE/SELECT they `FAIL` loudly — a typo there would
otherwise silently skip a real change.

## Common commands

```bash
php bin/migrate                  # apply pending
php bin/migrate --status         # show what's pending
php bin/migrate --dry-run        # preview statements
php bin/migrate --verbose        # show per-statement PASS/SKIP/WARN
php bin/migrate --version=1.0.5  # replay from a specific version
```

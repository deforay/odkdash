-- 1.2.1 — Reset api_logs to the outbound-only schema.
--
-- 1.2.0 first shipped with an inbound-request shape (path, platform,
-- user_id, ip_address, etc.). That was wrong for this project — only
-- outbound calls to ODK Central need to land here — so the migration
-- was rewritten in place. Anyone who applied the early version is left
-- with a table whose columns don't match the new code (every page-load
-- on /api-logs blows up with "Unknown column 'api_logs.source'").
--
-- The table held no useful data (the logger swallows insert failures
-- and the old inbound listener was removed before the fix), so the
-- safest path is a clean re-create. Idempotent: DROP IF EXISTS + the
-- CREATE TABLE IF NOT EXISTS from 1.2.0 means re-runs are no-ops.

DROP TABLE IF EXISTS `api_logs`;

CREATE TABLE IF NOT EXISTS `api_logs` (
  `request_id`         CHAR(36) NOT NULL,
  `source`             VARCHAR(64) DEFAULT NULL,
  `method`             VARCHAR(10) NOT NULL,
  `url`                VARCHAR(1024) NOT NULL,
  `request_body_path`  VARCHAR(255) DEFAULT NULL,
  `response_body_path` VARCHAR(255) DEFAULT NULL,
  `response_code`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `duration_ms`        INT UNSIGNED NOT NULL DEFAULT 0,
  `error`              TEXT DEFAULT NULL,
  `created_at`         DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (`request_id`),
  KEY `created_at` (`created_at`),
  KEY `response_code` (`response_code`),
  KEY `source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

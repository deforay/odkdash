-- 1.2.0 — Outbound ODK Central API call log + viewer ACL.
--
-- One row per HTTP call this app makes to an ODK Central server,
-- typically from the sync-central-v3 and sync-central-v6 CLI commands.
-- Inbound requests (admin pages, /receiver pushes) are NOT logged here;
-- those have their own logs (event_log / form_dump). The point of this
-- table is to give admins a single place to inspect what we asked
-- Central for, how long it took, what we got back, and what failed.
--
-- request_id is a UUIDv7 minted by Guzzle middleware per HTTP call (NOT
-- the per-PHP-request id from RequestContext, since one sync command
-- makes hundreds of calls). UUIDv7 sorts chronologically, so the PK
-- doubles as a "newest first" index without a separate timestamp idx.
--
-- Bodies live on disk under var/api-logs/{requests,responses}/, gzipped
-- or zstd-compressed via ArchiveUtility. The cleanup-old-logs command
-- removes both the row and its body files together.

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

-- ACL: new admin-only resource. Same shape as 1.1.2 log-viewer ACL.
INSERT IGNORE INTO `resources` (`resource_id`, `display_name`) VALUES
('Application\\Controller\\ApiLogsController', 'Manage API Logs');

INSERT IGNORE INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES
('Application\\Controller\\ApiLogsController', 'index', 'Access'),
('Application\\Controller\\ApiLogsController', 'show', 'View Detail'),
('Application\\Controller\\ApiLogsController', 'stats', 'View Stats');

INSERT IGNORE INTO `roles_privileges_map` (`role_id`, `privilege_id`)
SELECT 1, `privilege_id`
FROM `privileges`
WHERE `resource_id` = 'Application\\Controller\\ApiLogsController';

-- 1.1.5 — Capture client/request metadata on every event_log row.
--
-- The Event Log view needs to surface who/where/how for each entry so
-- admins can investigate suspicious activity (failed logins from
-- unfamiliar IPs, etc.) and retire the now-redundant User Login History
-- page. All new columns are nullable so historical rows stay valid.
--
-- session_hash deserves a note: many users sit behind CGNAT and share a
-- single public IP, so IP alone is not a correlation key. Storing a
-- SHA-256 of PHP's session_id() gives us a stable per-browser-session
-- identifier without keeping the raw session ID (which would be a
-- hijack risk if the table ever leaked).

ALTER TABLE `event_log` ADD COLUMN `ip_address` VARCHAR(45) DEFAULT NULL;
ALTER TABLE `event_log` ADD COLUMN `user_agent` VARCHAR(500) DEFAULT NULL;
ALTER TABLE `event_log` ADD COLUMN `session_hash` CHAR(64) DEFAULT NULL;
ALTER TABLE `event_log` ADD COLUMN `request_id` CHAR(36) DEFAULT NULL;
ALTER TABLE `event_log` ADD COLUMN `request_uri` VARCHAR(500) DEFAULT NULL;
ALTER TABLE `event_log` ADD COLUMN `request_method` VARCHAR(10) DEFAULT NULL;
ALTER TABLE `event_log` ADD COLUMN `platform` VARCHAR(20) DEFAULT NULL;

ALTER TABLE `event_log` ADD KEY `ip_address` (`ip_address`);
ALTER TABLE `event_log` ADD KEY `session_hash` (`session_hash`);
ALTER TABLE `event_log` ADD KEY `request_id` (`request_id`);

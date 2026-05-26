-- 1.0.1 — Event log table for user-action audit feed.
--
-- Captures actor + action + subject + resource for each user-driven
-- mutation (login, user/role/facility/config edits, downloads, etc.).
-- Surfaced via EventController / event/index.phtml. Previously only
-- present in data/alter.sql, so fresh installs never got the table.

CREATE TABLE IF NOT EXISTS `event_log` (
  `event_id` INT(11) NOT NULL AUTO_INCREMENT,
  `actor` INT(11) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `event_type` VARCHAR(255) DEFAULT NULL,
  `action` VARCHAR(255) DEFAULT NULL,
  `resource_name` VARCHAR(255) DEFAULT NULL,
  `date_time` DATETIME DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `actor` (`actor`),
  KEY `event_type` (`event_type`),
  KEY `date_time` (`date_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `event_log`
  ADD CONSTRAINT `event_log_ibfk_1` FOREIGN KEY (`actor`) REFERENCES `users` (`id`) ON DELETE SET NULL;

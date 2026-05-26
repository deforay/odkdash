-- 1.1.1 — Make event_log.actor nullable and align FK with ON DELETE SET NULL.
--
-- Failed-login events have no authenticated user, so addEventLog needs to
-- write a NULL actor. Installs created from the legacy data/alter.sql have
-- `actor INT(11) NOT NULL` with a plain FK to users(id); installs created
-- via the 1.0.1 migration already match this end state. This migration
-- brings legacy installs in line so failed-login event logging stops
-- crashing the login page.

ALTER TABLE `event_log` MODIFY `actor` INT(11) DEFAULT NULL;

ALTER TABLE `event_log` DROP FOREIGN KEY `event_log_ibfk_1`;
ALTER TABLE `event_log` ADD CONSTRAINT `event_log_ibfk_1` FOREIGN KEY (`actor`) REFERENCES `users` (`id`) ON DELETE SET NULL;

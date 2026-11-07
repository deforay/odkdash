-- 1.2.8: the audit trail on every install.
--
-- audit_spi_form_v_3 and audit_spi_form_v_6 hold a copy of an audit each
-- time it is inserted, updated or deleted. The audit trail page reads them.
-- Until now data/audit-triggers.sql created them by hand on some servers.
--
-- The triggers copy the audit's columns by position: action, revision and
-- dt_datetime, then every column of the audit table in order. An audit
-- table whose columns differ from its source makes every insert, update
-- and delete of an audit fail. 1.2.4 added form_metadata and
-- session_hash to spi_form_v_6 only, which did that on those servers.
--
-- For each version, this migration:
--
-- 1. Creates the audit table when it is missing: the audit's columns with
--    no keys, so one audit can have many rows, after the action, its
--    revision and the time. MyISAM numbers each audit's revisions from 1.
--    The ALTER is skipped when the table already has `action`.
-- 2. Adds 1.2.4's two columns to an older audit_spi_form_v_6, at the end,
--    where 1.2.4 put them in spi_form_v_6. bin/migrate skips a column the
--    table already has.
-- 3. Creates the three triggers. A trigger that exists already is left as
--    it is: bin/migrate reports it as a WARN (1359). CREATE TRIGGER IF NOT
--    EXISTS would do the same, but MySQL before 8.0.29 cannot parse it.
--
-- A database user without the right to create triggers also gets a WARN
-- from bin/migrate, and no trail. A migration that changes the columns of
-- spi_form_v_3 or spi_form_v_6 must make the same change to its audit table.

-- V3

CREATE TABLE IF NOT EXISTS `audit_spi_form_v_3` ENGINE = MyISAM SELECT * FROM `spi_form_v_3` WHERE 1 = 0;

ALTER TABLE `audit_spi_form_v_3`
    ADD `action` VARCHAR(8) DEFAULT 'insert' FIRST,
    ADD `revision` INT NOT NULL AUTO_INCREMENT AFTER `action`,
    ADD `dt_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `revision`,
    MODIFY COLUMN `id` INT NOT NULL,
    ADD PRIMARY KEY (`id`, `revision`);

CREATE TRIGGER `spi_form_v_3_data__ai` AFTER INSERT ON `spi_form_v_3` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_3` SELECT 'insert', NULL, NOW(), d.*
    FROM `spi_form_v_3` AS d WHERE d.id = NEW.id;

CREATE TRIGGER `spi_form_v_3_data__au` AFTER UPDATE ON `spi_form_v_3` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_3` SELECT 'update', NULL, NOW(), d.*
    FROM `spi_form_v_3` AS d WHERE d.id = NEW.id;

CREATE TRIGGER `spi_form_v_3_data__bd` BEFORE DELETE ON `spi_form_v_3` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_3` SELECT 'delete', NULL, NOW(), d.*
    FROM `spi_form_v_3` AS d WHERE d.id = OLD.id;

-- V6

CREATE TABLE IF NOT EXISTS `audit_spi_form_v_6` ENGINE = MyISAM SELECT * FROM `spi_form_v_6` WHERE 1 = 0;

ALTER TABLE `audit_spi_form_v_6`
    ADD `action` VARCHAR(8) DEFAULT 'insert' FIRST,
    ADD `revision` INT NOT NULL AUTO_INCREMENT AFTER `action`,
    ADD `dt_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `revision`,
    MODIFY COLUMN `id` INT NOT NULL,
    ADD PRIMARY KEY (`id`, `revision`);

ALTER TABLE `audit_spi_form_v_6` ADD `form_metadata` JSON NULL DEFAULT NULL;
ALTER TABLE `audit_spi_form_v_6` ADD `session_hash` VARCHAR(16) NULL DEFAULT NULL;

CREATE TRIGGER `spi_form_v_6_data__ai` AFTER INSERT ON `spi_form_v_6` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_6` SELECT 'insert', NULL, NOW(), d.*
    FROM `spi_form_v_6` AS d WHERE d.id = NEW.id;

CREATE TRIGGER `spi_form_v_6_data__au` AFTER UPDATE ON `spi_form_v_6` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_6` SELECT 'update', NULL, NOW(), d.*
    FROM `spi_form_v_6` AS d WHERE d.id = NEW.id;

CREATE TRIGGER `spi_form_v_6_data__bd` BEFORE DELETE ON `spi_form_v_6` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_6` SELECT 'delete', NULL, NOW(), d.*
    FROM `spi_form_v_6` AS d WHERE d.id = OLD.id;

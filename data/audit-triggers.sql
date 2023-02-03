CREATE TABLE `audit_spi_form_v_3` SELECT * from `spi_form_v_3` WHERE 1=0;

ALTER TABLE `audit_spi_form_v_3` 
   MODIFY COLUMN `id` int(11) NOT NULL, 
   ENGINE = MyISAM, 
   ADD `action` VARCHAR(8) DEFAULT 'insert' FIRST, 
   ADD `revision` INT(6) NOT NULL AUTO_INCREMENT AFTER `action`,
   ADD `dt_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `revision`,
   ADD PRIMARY KEY (`id`, `revision`);

DROP TRIGGER IF EXISTS spi_form_v_3_data__ai;
DROP TRIGGER IF EXISTS spi_form_v_3_data__au;
DROP TRIGGER IF EXISTS spi_form_v_3_data__bd;

CREATE TRIGGER spi_form_v_3_data__ai AFTER INSERT ON `spi_form_v_3` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_3` SELECT 'insert', NULL, NOW(), d.* 
    FROM `spi_form_v_3` AS d WHERE d.id = NEW.id;

CREATE TRIGGER spi_form_v_3_data__au AFTER UPDATE ON `spi_form_v_3` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_3` SELECT 'update', NULL, NOW(), d.*
    FROM `spi_form_v_3` AS d WHERE d.id = NEW.id;

CREATE TRIGGER spi_form_v_3_data__bd BEFORE DELETE ON `spi_form_v_3` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_3` SELECT 'delete', NULL, NOW(), d.* 
    FROM `spi_form_v_3` AS d WHERE d.id = OLD.id;





CREATE TABLE `audit_spi_form_v_6` SELECT * from `spi_form_v_6` WHERE 1=0;

ALTER TABLE `audit_spi_form_v_6` 
   MODIFY COLUMN `id` int(11) NOT NULL, 
   ENGINE = MyISAM, 
   ADD `action` VARCHAR(8) DEFAULT 'insert' FIRST, 
   ADD `revision` INT(6) NOT NULL AUTO_INCREMENT AFTER `action`,
   ADD `dt_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `revision`,
   ADD PRIMARY KEY (`id`, `revision`);

DROP TRIGGER IF EXISTS spi_form_v_6_data__ai;
DROP TRIGGER IF EXISTS spi_form_v_6_data__au;
DROP TRIGGER IF EXISTS spi_form_v_6_data__bd;

CREATE TRIGGER spi_form_v_6_data__ai AFTER INSERT ON `spi_form_v_6` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_6` SELECT 'insert', NULL, NOW(), d.* 
    FROM `spi_form_v_6` AS d WHERE d.id = NEW.id;

CREATE TRIGGER spi_form_v_6_data__au AFTER UPDATE ON `spi_form_v_6` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_6` SELECT 'update', NULL, NOW(), d.*
    FROM `spi_form_v_6` AS d WHERE d.id = NEW.id;

CREATE TRIGGER spi_form_v_6_data__bd BEFORE DELETE ON `spi_form_v_6` FOR EACH ROW
    INSERT INTO `audit_spi_form_v_6` SELECT 'delete', NULL, NOW(), d.* 
    FROM `spi_form_v_6` AS d WHERE d.id = OLD.id;



-- 1.2.11 — Create the table the V3 sheet upload stages rows in.
--
-- /spi-v3/validate-spiv3-data reads an uploaded sheet into
-- spi_form_v_3_temp, and the rows the user accepts are copied into
-- spi_form_v_3. No migration created the table, so the upload failed on
-- every install that had not created it by hand. Its columns are the
-- sheet's headers, which are spi_form_v_3's columns, plus spi_data_status:
-- 1 when the instance ID is already stored, 0 when it is new.
--
-- Safe to re-run: CREATE TABLE IF NOT EXISTS leaves a table made by hand
-- alone, and bin/migrate skips ADD COLUMN when the column exists. LIKE
-- copies the columns and keys, not the triggers.

CREATE TABLE IF NOT EXISTS `spi_form_v_3_temp` LIKE `spi_form_v_3`;

ALTER TABLE `spi_form_v_3_temp` ADD COLUMN `spi_data_status` INT NULL DEFAULT NULL;

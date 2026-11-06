-- 1.2.7 — Record the archive each bulk download request produced.
--
-- The bulk PDF command writes one zip per request. file_name names it. The
-- list page reads it to show users the archives they asked for. audit_ids
-- lists the audits in the zip. The download link serves the zip only while
-- every one of those audits is still in the user's scope.
--
-- Safe to re-run: bin/migrate skips an ADD COLUMN whose column exists.

ALTER TABLE `r_spi_form_v_3_download` ADD COLUMN `file_name` varchar(255) DEFAULT NULL;
ALTER TABLE `r_spi_form_v_6_download` ADD COLUMN `file_name` varchar(255) DEFAULT NULL;
ALTER TABLE `r_spi_form_v_3_download` ADD COLUMN `audit_ids` mediumtext DEFAULT NULL;
ALTER TABLE `r_spi_form_v_6_download` ADD COLUMN `audit_ids` mediumtext DEFAULT NULL;

-- 1.2.5 — Restore the tables a removed audit is archived into.
--
-- Removing an audit copies it into spi_form_v_3_duplicate or
-- spi_form_v_6_duplicate before deleting it. Installs that never ran the old
-- data/alter.sql have neither table, and there removing an audit silently does
-- nothing: the archive step finds no columns to copy and gives up.
--
-- LIKE takes the audit table as it stands, so the archive has every column the
-- audit has. Installs that already have an archive table keep theirs.

CREATE TABLE IF NOT EXISTS `spi_form_v_3_duplicate` LIKE `spi_form_v_3`;
CREATE TABLE IF NOT EXISTS `spi_form_v_6_duplicate` LIKE `spi_form_v_6`;

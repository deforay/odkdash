-- 1.1.0 — Drop legacy SPI-RRT v5 schema.
--
-- v5 was an interim revision between v3 and v6 and is no longer
-- supported. All v5 application code (controller, models, views,
-- service methods, routes, ACL entries) has been removed in the same
-- release; this migration drops the matching DB objects so the schema
-- matches the code.

DROP TABLE IF EXISTS `r_spi_form_v_5_download`;
DROP TABLE IF EXISTS `spi_form_v_5_duplicate`;
DROP TABLE IF EXISTS `spi_v5_form_labels`;
DROP TABLE IF EXISTS `spi_form_v_5`;

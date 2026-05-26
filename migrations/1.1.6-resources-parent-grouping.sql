-- 1.1.6 — Add parent / sort_order columns to resources for visual grouping.
--
-- Lets resources stay 1:1 with controller classes (dispatch enforcement
-- in Module.php keeps working unchanged) while letting the role-edit
-- UI nest related resources under a single heading. Today we use it
-- to fold SPI-RT v3/v6 Reports under their corresponding SPI form
-- controller; future groupings are pure data — set parent_resource_id
-- and the view will pick it up.
--
-- sort_order lets us pin the row order explicitly instead of relying
-- on alphabetical display_name sort. Default 0 = current behaviour.

ALTER TABLE resources
  ADD COLUMN parent_resource_id VARCHAR(255) NULL DEFAULT NULL AFTER display_name,
  ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER parent_resource_id;

ALTER TABLE resources
  ADD CONSTRAINT fk_resources_parent
  FOREIGN KEY (parent_resource_id) REFERENCES resources(resource_id) ON DELETE SET NULL;

UPDATE resources
   SET display_name = 'SPI-RT (v3)'
 WHERE resource_id = 'Application\\Controller\\SpiV3Controller';

UPDATE resources
   SET display_name = 'SPI-RRT (v6)'
 WHERE resource_id = 'Application\\Controller\\SpiV6Controller';

UPDATE resources
   SET parent_resource_id = 'Application\\Controller\\SpiV3Controller',
       display_name = 'Reports'
 WHERE resource_id = 'Application\\Controller\\SpiV3ReportsController';

UPDATE resources
   SET parent_resource_id = 'Application\\Controller\\SpiV6Controller',
       display_name = 'Reports'
 WHERE resource_id = 'Application\\Controller\\SpiV6ReportsController';

-- 1.2.9 — Number user_location_map rows automatically.
--
-- data/alter.sql created the table with an id that has no AUTO_INCREMENT.
-- The user screens insert rows without an id, so the first row got id 0 and
-- every later insert failed on a duplicate key. A user could not be saved
-- with a location while any mapping row existed.
--
-- MODIFY renumbers the row with id 0 to the next free id.
--
-- Safe to re-run: the column is already AUTO_INCREMENT after the first run.

ALTER TABLE `user_location_map` MODIFY `id` int NOT NULL AUTO_INCREMENT;

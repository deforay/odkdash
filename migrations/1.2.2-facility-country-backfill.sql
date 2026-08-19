-- 1.2.2 — Backfill spi_rt_3_facilities.country.
--
-- f67ba667 (Dec 2024) added the column and, in the same release, started
-- filtering audits by it whenever a user is mapped through
-- user_location_map with mapping_type='country':
--
--   JOIN spi_rt_3_facilities f ON f.id=spiv6.facility OR f.facility_name=spiv6.facilityname
--   WHERE f.country IN (<mapped ids>)
--
-- The ALTER shipped as `NULL DEFAULT NULL` with no backfill, and
-- addFacilityBasedOnForm() never populated it either, so on any instance
-- whose facilities came in via ODK submissions every row sat at NULL. A
-- country-mapped user therefore matched nothing: the audit list rendered
-- "0 of 0 (filtered from N)" — the total count query carries no join, so
-- only the filtered side collapsed — and the v6 dashboard came up empty
-- through the same predicate in getPerformanceV6() and
-- getPerformanceLast30DaysV6().
--
-- A deployment only ever serves one country, so the country-name global
-- config is the source of truth. Rows are left alone when it does not name
-- a row in `countries` (instances that use it as a free-text label), which
-- is exactly the NULL they already hold.
--
-- Idempotent: the WHERE only touches rows that are still unset, and the
-- JOIN yields nothing when country-name does not resolve.

-- The column itself only ever existed in data/alter.sql (26-Dec-2024). Instances
-- were expected to have applied that by hand, and at least one had not, so this
-- backfill died on "Unknown column 'f.country'". Add it here first — bin/migrate
-- skips the ALTER where the column is already present. No AFTER clause on
-- purpose: position is cosmetic, and naming a column the instance is also
-- missing would fail the ALTER for the same reason.

ALTER TABLE `spi_rt_3_facilities` ADD `country` VARCHAR(255) NULL DEFAULT NULL;

UPDATE `spi_rt_3_facilities` f
  JOIN `countries` c
    ON c.country_name = (SELECT global_value FROM `global_config` WHERE global_name = 'country-name')
   SET f.country = c.country_id
 WHERE f.country IS NULL OR f.country = '';

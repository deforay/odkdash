-- 1.2.4 — Point spi_form_v_6.facility at the facility each audit belongs to.
--
-- Location filters joined facilities three different ways depending on which
-- query you landed in:
--
--   f.id = spiv6.facility                                        (dashboard)
--   f.id = spiv6.facility OR f.facility_name = spiv6.facilityname (audit list)
--   f.facility_id = spiv6.facilityid OR f.facility_name = ...     (reports)
--
-- Only the first is sound. facilityname is not unique — six names are shared by
-- more than one facility row — and facilityid is not a facility key at all:
-- 2616 appears under three different audit facility names and 3001 matches
-- three different facility rows. The OR forms also cost real time, because an
-- OR across two columns cannot use an index: the audit list spent 16s on a
-- plan without hash joins, and even with one it read 0.40s against 0.06s for
-- the same count expressed without the OR. Worse, they double-count: audit 1145
-- carries facility=247 while its facilityname matches row 438, so it was listed
-- twice and the page reported 3006 of 3009 when only 3005 were visible.
--
-- With every audit pointing at its facility the join collapses to a primary key
-- lookup, identical on any MySQL version, and duplicates become impossible.
--
-- Ties resolve to the lowest facility id, matching addFacilityBasedOnForm(),
-- which orders by id for exactly this reason. Audits that already carry a
-- facility are left alone — an explicit id beats a name match, which is what
-- settles 1145.
--
-- Audits with a blank facilityname stay NULL: there is nothing to resolve them
-- against, and they are the rows `bin/console audits:unlinked` reports.
--
-- Idempotent: only rows with no facility are touched, and bin/migrate skips the
-- index when it already exists.

UPDATE `spi_form_v_6` v
  JOIN (
      SELECT f.facility_name, MIN(f.id) AS facility_id
        FROM `spi_rt_3_facilities` f
       WHERE TRIM(COALESCE(f.facility_name, '')) <> ''
       GROUP BY f.facility_name
  ) pick ON pick.facility_name = v.facilityname
   SET v.facility = pick.facility_id
 WHERE v.facility IS NULL OR v.facility = 0;

CREATE INDEX `idx_v6_facility` ON `spi_form_v_6` (`facility`);

-- 1.2.3 — Create the missing spi_rt_3_facilities rows for v6 audits.
--
-- approveSpiV6FormStatus() called addFacilityBasedOnForm($id) without the
-- $formVersion argument, so it defaulted to 3 and looked the id up in
-- spi_form_v_3 instead of spi_form_v_6. Approving a v6 audit therefore never
-- created its facility. (No wrong rows were produced: spi_form_v_3 is empty on
-- v6 instances, so the lookup simply found nothing.) Audits that arrived
-- through the ODK receive path were unaffected — those call sites pass the
-- version explicitly — which is why only the early manually-approved backlog
-- is missing.
--
-- It matters because every location filter inner-joins facilities:
--
--   JOIN spi_rt_3_facilities f ON f.id=spiv6.facility OR f.facility_name=spiv6.facilityname
--
-- so an audit with no facility row is invisible to any mapped user, however
-- the mapping is scoped. On the Malawi instance that hid 383 approved audits.
--
-- This replays what addFacilityBasedOnForm() would have written, one row per
-- distinct facility name, taking facility_id and coordinates from the earliest
-- audit bearing that name.
--
-- Two deliberate departures from that function:
--
--   * province/district are left NULL rather than the literal 'Unknown' the
--     function writes when a form carries no district. None of these forms
--     carry one, the column otherwise holds numeric geo ids, and 'Unknown'
--     matches no province filter anyway — so it would add a magic string for
--     no gain.
--   * audits with a blank facilityname are skipped. There is nothing to key a
--     facility on, and inserting one empty-named row would silently link every
--     such audit to it.
--
-- Idempotent: the NOT EXISTS is the same predicate the application joins on,
-- so a second run matches nothing.

INSERT INTO `spi_rt_3_facilities`
    (`facility_id`, `facility_name`, `country`, `province`, `district`, `latitude`, `longitude`)
SELECT
    NULLIF(TRIM(COALESCE(src.facilityid, '')), ''),
    src.facilityname,
    (SELECT c.country_id
       FROM `countries` c
      WHERE c.country_name = (SELECT global_value
                                FROM `global_config`
                               WHERE global_name = 'country-name')),
    NULL,
    NULL,
    NULLIF(TRIM(COALESCE(src.Latitude, '')), ''),
    NULLIF(TRIM(COALESCE(src.Longitude, '')), '')
FROM (
    SELECT v.facilityname, MIN(v.id) AS first_id
      FROM `spi_form_v_6` v
     WHERE v.status <> 'deleted'
       AND TRIM(COALESCE(v.facilityname, '')) <> ''
       AND NOT EXISTS (
           SELECT 1 FROM `spi_rt_3_facilities` f
            WHERE f.id = v.facility OR f.facility_name = v.facilityname
       )
     GROUP BY v.facilityname
) pick
JOIN `spi_form_v_6` src ON src.id = pick.first_id;

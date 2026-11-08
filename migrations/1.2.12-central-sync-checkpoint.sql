-- 1.2.12 — Remember where each Central sync resumes.
--
-- The sync asks Central for submissions made on or after fetch_from, a UTC
-- submission date. A sync moves it forward only after a complete run: to
-- the earliest submission it could not save, or else to the newest one it
-- saw. A run that stops part way leaves it alone, so the next run fetches
-- the same submissions again and skips the ones already stored.
--
-- A project and form with no row is fetched from the start once.
--
-- Safe to re-run: CREATE TABLE IF NOT EXISTS leaves the table alone.

CREATE TABLE IF NOT EXISTS `central_sync_checkpoint` (
  `form_version` TINYINT NOT NULL,
  `central_project_id` INT NOT NULL,
  `central_form_id` VARCHAR(255) NOT NULL,
  `fetch_from` DATETIME(3) NOT NULL,
  `updated_on` DATETIME NOT NULL,
  PRIMARY KEY (`form_version`, `central_project_id`, `central_form_id`)
);

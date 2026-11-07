-- 1.2.10 — Record which run-once scripts have run.
--
-- bin/migrate runs each run-once/*.php script after the migrations. A
-- script that finishes adds its name here and is not run again. A script
-- that fails adds nothing, so the next bin/migrate retries it.
--
-- Safe to re-run: CREATE TABLE IF NOT EXISTS.

CREATE TABLE IF NOT EXISTS `s_run_once_scripts_log` (
  `script_name` varchar(255) NOT NULL,
  `execution_date` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`script_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

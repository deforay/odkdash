-- 1.2.13 — Index for counting recent failed logins.
--
-- UsersTable::login() counts the login-failed rows for one username (kept in
-- subject) in the last few minutes before it checks a password. Without this
-- index that count reads the whole event_log table on every login.
--
-- Safe to re-run: bin/migrate skips ADD KEY when the key already exists.

ALTER TABLE `event_log` ADD KEY `event_type_subject_date_time` (`event_type`, `subject`, `date_time`);

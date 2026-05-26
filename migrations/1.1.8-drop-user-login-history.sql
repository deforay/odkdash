-- 1.1.8 — Retire User Login History.
--
-- All IP / user-agent / OS data that user_login_history captured at login
-- time is now recorded on every event_log row (1.1.5 migration), so the
-- standalone page + table is fully redundant. Login successes and failures
-- already flow through EventLogTable::addEventLog and surface in the
-- Event Log timeline with full client context.
--
-- This migration removes the runtime traces: drops the ACL resource (and
-- its privileges + role-privilege mappings — cascaded by FK on
-- privilege_id), and drops the table itself. The PHP code paths
-- (controller, service, model, view, route, factory wiring, write call
-- sites in UsersTable) are removed in the same commit.

DELETE FROM `roles_privileges_map`
WHERE `privilege_id` IN (
    SELECT `privilege_id` FROM `privileges`
    WHERE `resource_id` = 'Application\\Controller\\UserLoginHistoryController'
);

DELETE FROM `privileges`
WHERE `resource_id` = 'Application\\Controller\\UserLoginHistoryController';

DELETE FROM `resources`
WHERE `resource_id` = 'Application\\Controller\\UserLoginHistoryController';

DROP TABLE IF EXISTS `user_login_history`;

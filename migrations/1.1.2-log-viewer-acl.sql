-- 1.1.2 — Seed ACL resource + privilege for the new Error Logs viewer.
--
-- Application\Controller\LogViewerController is the new admin-only log
-- viewer (index action lists var/log/ files; same action serves JSON for
-- the AJAX tail). Following the convention established by the
-- 2024-10-06 rename in data/alter.sql, the resource_id uses the full
-- controller class name including the `Controller` suffix.
--
-- INSERT IGNORE everywhere so re-runs are no-ops. Role 1 (superadmin)
-- gets the privilege automatically — other roles must be granted via
-- Manage Roles in the UI.

INSERT IGNORE INTO `resources` (`resource_id`, `display_name`) VALUES
('Application\\Controller\\LogViewerController', 'Manage Error Logs');

INSERT IGNORE INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES
('Application\\Controller\\LogViewerController', 'index', 'Access');

INSERT IGNORE INTO `roles_privileges_map` (`role_id`, `privilege_id`)
SELECT 1, `privilege_id`
FROM `privileges`
WHERE `resource_id` = 'Application\\Controller\\LogViewerController';

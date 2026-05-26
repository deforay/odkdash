-- 1.1.7 — Super Admin always gets every privilege.
--
-- The SA role is meant to bypass the ACL entirely, but the
-- roles_privileges_map only reflects whatever was explicitly granted
-- at role-create time, so newly seeded privileges (e.g. when a new
-- module adds resources) leave SA with gaps in the role-edit UI.
--
-- This migration is the data side of a three-part change:
--   * Acl.php now does $this->allow('SA') unconditionally as a
--     runtime safety net, so SA bypasses the map.
--   * RolesTable::mapRolesPrivileges short-circuits when roleCode = 'SA',
--     so the form can't accidentally remove privileges from SA.
--   * This SQL backfills the map with every (SA, privilege_id) row
--     that's missing, so the role-edit UI shows all checkboxes ticked
--     instead of looking like SA is missing privileges.

INSERT IGNORE INTO roles_privileges_map (role_id, privilege_id)
SELECT r.role_id, p.privilege_id
  FROM roles r
  CROSS JOIN privileges p
  WHERE r.role_code = 'SA';

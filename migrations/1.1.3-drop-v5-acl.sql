-- 1.1.3 — Remove leftover SPI-RT v5 ACL data.
--
-- The v5 code was dropped in migration 1.1.0 (and the corresponding
-- application/config commit), but the DashboardV5Controller /
-- SpiV5Controller / SpiV5ReportsController resources, their privileges,
-- the EmailController 'email-v5' privilege, and the role mappings for
-- all of the above were left behind in the resources / privileges /
-- roles_privileges_map tables — so 'Manage SPI V5 Form' still showed up
-- in the privileges UI.
--
-- Order matters: roles_privileges_map -> privileges -> resources, since
-- privileges.resource_id references resources(resource_id) and
-- roles_privileges_map.privilege_id references privileges(privilege_id).

DELETE rpm FROM roles_privileges_map rpm
  JOIN privileges p ON p.privilege_id = rpm.privilege_id
  WHERE p.resource_id IN (
        'Application\\Controller\\DashboardV5Controller',
        'Application\\Controller\\SpiV5Controller',
        'Application\\Controller\\SpiV5ReportsController'
      )
     OR (p.resource_id = 'Application\\Controller\\EmailController' AND p.privilege_name = 'email-v5');

DELETE FROM privileges
  WHERE resource_id IN (
        'Application\\Controller\\DashboardV5Controller',
        'Application\\Controller\\SpiV5Controller',
        'Application\\Controller\\SpiV5ReportsController'
      )
     OR (resource_id = 'Application\\Controller\\EmailController' AND privilege_name = 'email-v5');

DELETE FROM resources
  WHERE resource_id IN (
        'Application\\Controller\\DashboardV5Controller',
        'Application\\Controller\\SpiV5Controller',
        'Application\\Controller\\SpiV5ReportsController'
      );

<?php

declare(strict_types=1);

/*
 * Merges roles that share a role code.
 *
 * The roles screens did not check that a code was free, so a server can
 * have two roles with one code. The ACL knows a role by its code and gives
 * that code the privileges of every active role that has it. Saving a role's
 * privileges also found the role by its code, so it could change the wrong
 * role.
 *
 * For each code, this keeps one role: the lowest active role_id, or the
 * lowest role_id when none is active. The kept role gets the privileges of
 * every active role with the code, which is what the ACL already grants.
 * Users of the other roles move to the kept role. The other roles are then
 * deleted. Nobody's access changes.
 *
 * Codes match byte for byte, as in the ACL. The column's collation ignores
 * case, so a plain comparison would merge "sa" into "SA" and give its users
 * Super Admin.
 */

require __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

App\RunOnce::run(__FILE__, function (Connection $db): void {
    $codes = $db->fetchFirstColumn('SELECT MIN(role_code) FROM roles GROUP BY BINARY role_code HAVING COUNT(*) > 1');
    if ($codes === []) {
        echo "No role code is used twice.\n";
        return;
    }

    $db->transactional(function (Connection $db) use ($codes): void {
        foreach ($codes as $code) {
            $roles = $db->fetchAllAssociative(
                "SELECT role_id, role_name, status FROM roles WHERE BINARY role_code = BINARY ? ORDER BY status = 'active' DESC, role_id",
                [$code]
            );
            $keep = (int) $roles[0]['role_id'];
            $others = array_map('intval', array_column(array_slice($roles, 1), 'role_id'));
            $active = array_map('intval', array_column(array_filter($roles, fn($r) => $r['status'] === 'active'), 'role_id'));

            if ($active !== []) {
                $db->executeStatement(
                    'INSERT IGNORE INTO roles_privileges_map (role_id, privilege_id)
                    SELECT DISTINCT ?, privilege_id FROM roles_privileges_map WHERE role_id IN (?)',
                    [$keep, $active],
                    [ParameterType::INTEGER, ArrayParameterType::INTEGER]
                );
            }
            $db->executeStatement('UPDATE user_role_map SET role_id = ? WHERE role_id IN (?)', [$keep, $others], [ParameterType::INTEGER, ArrayParameterType::INTEGER]);
            $db->executeStatement('DELETE FROM roles_privileges_map WHERE role_id IN (?)', [$others], [ArrayParameterType::INTEGER]);
            $db->executeStatement('DELETE FROM roles WHERE role_id IN (?)', [$others], [ArrayParameterType::INTEGER]);

            $names = implode(', ', array_map(fn($r) => "{$r['role_id']} ({$r['role_name']})", array_slice($roles, 1)));
            echo "{$code}: kept role {$keep} ({$roles[0]['role_name']}), merged {$names}.\n";
        }
    });
});

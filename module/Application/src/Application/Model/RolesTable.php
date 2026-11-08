<?php

namespace Application\Model;

use App\Db\Dbal;
use Application\Session\Container;
use Application\Service\CommonService;
use Doctrine\DBAL\Connection;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class RolesTable
{
    private readonly Adapter $adapter;
    private readonly Connection $db;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->db = Dbal::connection($adapter);
    }

    public function addRolesDetails($params)
    {
        // Only the seeded Super Admin role may carry the SA code.
        if (trim((string) $params['roleCode']) === 'SA' || $this->codeTaken($params['roleCode'], 0)) {
            return 0;
        }
        if (trim($params['roleName']) != "") {
            $this->db->insert('roles', [
                'category_id' => 1,
                'role_name' => $params['roleName'],
                'role_code' => $params['roleCode'],
                'description' => $params['description'],
                'status' => 'active'
            ]);
            return (int) $this->db->lastInsertId();
        }
    }

    public function updateRolesDetails($params)
    {
        if (trim($params['roleName']) != "" && trim($params['roleId']) != "") {
            $roleId = (int) base64_decode($params['roleId']);
            // The Super Admin role cannot be renamed, deactivated or stripped,
            // and no other role can take its code: Acl.php grants SA everything.
            $currentCode = $this->db->fetchOne('SELECT role_code FROM roles WHERE role_id = ?', [$roleId]);
            if ($currentCode === false || $currentCode === 'SA' || trim((string) $params['roleCode']) === 'SA' || $this->codeTaken($params['roleCode'], $roleId)) {
                return 0;
            }
            $this->db->update('roles', [
                'role_name' => $params['roleName'],
                'role_code' => $params['roleCode'],
                'description' => $params['description'],
                'status' => $params['status']
            ], ['role_id' => $roleId]);
            return $roleId;
        }
    }

    /**
     * The ACL knows a role by its code, so two roles with one code would
     * share their privileges. The form checks this in JavaScript only.
     */
    private function codeTaken($roleCode, int $exceptRoleId): bool
    {
        return $this->db->fetchOne(
            'SELECT 1 FROM roles WHERE role_code = ? AND role_id <> ?',
            [trim((string) $roleCode), $exceptRoleId]
        ) !== false;
    }

    public function fetchAllRoleDetails($parameters, $acl)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('role_name', 'role_code', 'status');

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sOffset = $parameters['iDisplayStart'];
            $sLimit = $parameters['iDisplayLength'];
        }

        /*
         * Ordering
         */

        $sOrder = "";
        if (isset($parameters['iSortCol_0'])) {
            for ($i = 0; $i < (int) $parameters['iSortingCols']; $i++) {
                if ($parameters['bSortable_' . (int) $parameters['iSortCol_' . $i]] == "true" && isset($aColumns[(int) $parameters['iSortCol_' . $i]])) {
                    $sOrder .= $aColumns[(int) $parameters['iSortCol_' . $i]] . " " . ($parameters['sSortDir_' . $i]) . ", ";
                }
            }
            $sOrder = rtrim($sOrder, ", ");
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = CommonService::dataTablesSearch($aColumns, $parameters);

        /*
         * SQL queries
         * Get data to display
         */
        // The builder still writes the search and the sort. DBAL runs the SQL.
        $sql = new Sql($this->adapter);
        $sQuery = $sql->select()->from('roles')->where("role_code !='daemon'");
        if ($sWhere !== null) {
            $sQuery->where($sWhere);
        }

        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $rResult = $this->db->fetchAllAssociative($sql->buildSqlString($sQuery));

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $iFilteredTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM (' . $sql->buildSqlString($sQuery) . ') AS filtered');

        /* Total data set length */
        $iTotal = (int) $this->db->fetchOne("SELECT COUNT(*) FROM roles WHERE role_code != 'daemon'");

        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        $loginContainer = new Container('credo');
        $role = $loginContainer->roleCode;
        $update = (bool) $acl->isAllowed($role, 'Application\Controller\RolesController', 'edit');
        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = CommonService::escapeHtml(ucwords($aRow['role_name']));
            $row[] = CommonService::escapeHtml($aRow['role_code']);
            $row[] = CommonService::escapeHtml(ucwords($aRow['status']));
            if ($update) {
                if ($aRow['role_code'] === 'SA') {
                    // Super Admin is locked — show a non-actionable indicator
                    // instead of the Edit link. editAction also blocks direct URLs.
                    $row[] = '<span class="text-muted" title="Super Admin privileges are locked"><i class="fa fa-lock"></i> Locked</span>';
                } else {
                    $row[] = '<a href="/roles/edit/' . base64_encode($aRow['role_id']) . '" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>';
                }
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getRolesDetails($id)
    {
        $role = $this->db->fetchAssociative('SELECT * FROM roles WHERE role_id = ?', [(int) $id]);

        // The handler and the edit template read the role as $result->role_code.
        return $role === false ? null : new \ArrayObject($role, \ArrayObject::ARRAY_AS_PROPS);
    }

    public function fecthAllActiveRoles()
    {
        return $this->db->fetchAllAssociative("SELECT * FROM roles WHERE status = 'active' ORDER BY role_name");
    }

    public function fetchAllRoles()
    {
        $resources = $this->db->fetchAllAssociative('SELECT * FROM resources ORDER BY sort_order ASC, display_name ASC');

        $allPrivs = $this->db->fetchAllAssociative('SELECT * FROM privileges ORDER BY display_name');
        $privsByResource = [];
        foreach ($allPrivs as $p) {
            $privsByResource[$p['resource_id']][] = $p;
        }

        $byId = [];
        foreach ($resources as $r) {
            $r['privileges'] = $privsByResource[$r['resource_id']] ?? [];
            $r['children']   = [];
            $byId[$r['resource_id']] = $r;
        }

        $tree = [];
        foreach ($byId as $id => $r) {
            $parentId = $r['parent_resource_id'] ?? null;
            if ($parentId !== null && $parentId !== '' && isset($byId[$parentId])) {
                $byId[$parentId]['children'][] = $r;
            } else {
                $tree[] = &$byId[$id];
            }
        }
        unset($r);

        return $tree;
    }

    /**
     * Replaces the role's privileges with the ones the form allows. Runs
     * inside RoleService's transaction, so an error undoes the whole save.
     */
    public function mapRolesPrivileges(int $roleId, $params): void
    {
        // Super Admin's privileges are locked. Acl.php grants SA everything
        // at runtime, and the UI disables the privilege toggles for this
        // role. Refuse to change the map here so a crafted POST cannot strip
        // privileges either.
        if ($this->db->fetchOne('SELECT role_code FROM roles WHERE role_id = ?', [$roleId]) === 'SA') {
            return;
        }

        $this->db->delete('roles_privileges_map', ['role_id' => $roleId]);
        foreach ($params['resource'] ?? [] as $resourceName => $privileges) {
            foreach ($privileges as $privilegeName => $allowed) {
                if ($allowed !== 'allow') {
                    continue;
                }
                $privilegeId = $this->db->fetchOne(
                    'SELECT privilege_id FROM privileges WHERE resource_id = ? AND privilege_name = ?',
                    [$resourceName, $privilegeName]
                );
                // A privilege removed since the form loaded cannot be granted.
                if ($privilegeId !== false) {
                    $this->db->insert('roles_privileges_map', ['role_id' => $roleId, 'privilege_id' => $privilegeId]);
                }
            }
        }
    }

    public function fetchPrivilegesMapByRoleId($roleId)
    {
        if ($roleId == '') {
            return [];
        }
        return $this->db->fetchAllAssociative('SELECT * FROM roles_privileges_map WHERE role_id = ?', [$roleId]);
    }

    public function getAllPrivilegesMap()
    {
        return $this->db->fetchAllAssociative('SELECT role_id, privilege_id FROM roles_privileges_map');
    }

    public function getAllPrivileges()
    {
        return $this->db->fetchAllAssociative('SELECT privilege_id, resource_id, privilege_name FROM privileges');
    }
}

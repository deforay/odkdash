<?php

namespace Application;

use Application\Model\Acl;
use Application\Model\RolesTable;
use Application\Model\UsersTable;
use Application\Model\GlobalTable;
use Application\Model\EventLogTable;
use Application\Model\TempMailTable;
use Doctrine\DBAL\Connection;
use Application\Service\RoleService;
use Application\Service\UserService;
use Application\Model\AuditMailTable;
use Application\Model\CountriesTable;
use Application\Model\ResourcesTable;
use Application\Service\EventService;
use Application\Service\CommonService;
use Application\Model\SpiFormVer3Table;
use Application\Model\SpiFormVer6Table;
use Application\Service\OdkFormService;
use Application\Service\FacilityService;
use Application\Model\SpiFormLabelsTable;
use Application\Model\AuditSpiFormV3Table;
use Application\Model\AuditSpiFormV6Table;


use Application\Service\AuditTrailService;
use Application\Model\SpiFormVer3TempTable;
use Application\Model\SpiRtFacilitiesTable;
use Application\Model\SpiFormVer3DownloadTable;

use Application\Model\SpiFormVer6DownloadTable;
use Application\Model\SpiFormVer3DuplicateTable;

use Application\Model\SpiFormVer6DuplicateTable;

use Application\Service\Logger;
use Application\Service\ProvinceService;
use Application\Service\ApiLogger;
use Application\Model\ApiLogsTable;
use Application\Model\GeographicalDivisionsTable;
use Application\Model\UserLocationMapTable;

class Module
{


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Logger' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new Logger();
                    }
                },
                'AppAcl' => new class
                {

                    public function __invoke($diContainer)
                    {
                        $resourcesTable = $diContainer->get('ResourcesTable');
                        $rolesTable = $diContainer->get('RolesTable');
                        // return new Acl($resourcesTable->fetchAllResourceMap(), $rolesTable->fecthAllActiveRoles());
                        return new Acl($resourcesTable->fetchAllResourceMap(), $rolesTable->fecthAllActiveRoles(), $rolesTable->getAllPrivilegesMap(), $rolesTable->getAllPrivileges());
                    }
                },
                'SpiFormVer3Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer3Table($dbAdapter);
                    }
                },
                'SpiFormVer6Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer6Table($dbAdapter, $diContainer->get('translator'));
                    }
                },
                'UsersTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new UsersTable($dbAdapter);
                    }
                },
                'SpiFormLabelsTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormLabelsTable($dbAdapter);
                    }
                },
                'SpiRtFacilitiesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiRtFacilitiesTable($dbAdapter);
                    }
                },
                'RolesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new RolesTable($dbAdapter);
                    }
                },
                'GlobalTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new GlobalTable($dbAdapter);
                    }
                },
                'EventLogTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new EventLogTable($dbAdapter);
                    }
                },
                'ApiLogsTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new ApiLogsTable($dbAdapter);
                    }
                },
                'ApiLogger' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new ApiLogger($diContainer->get(Connection::class));
                    }
                },
                'ResourcesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new ResourcesTable($diContainer->get(Connection::class));
                    }
                },
                'TempMailTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new TempMailTable($diContainer->get(Connection::class));
                    }
                },
                'AuditMailTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new AuditMailTable($dbAdapter);
                    }
                },
                'SpiFormVer3DownloadTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer3DownloadTable($dbAdapter);
                    }
                },
                'SpiFormVer6DownloadTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer6DownloadTable($dbAdapter);
                    }
                },
                'SpiFormVer3DuplicateTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer3DuplicateTable($dbAdapter);
                    }
                },
                'SpiFormVer6DuplicateTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer6DuplicateTable($dbAdapter);
                    }
                },
                'SpiFormVer3TempTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new SpiFormVer3TempTable($dbAdapter);
                    }
                },
                'CountriesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new CountriesTable($dbAdapter);
                    }
                },
                'AuditSpiFormV3Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new AuditSpiFormV3Table($dbAdapter);
                    }
                },
                'AuditSpiFormV6Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new AuditSpiFormV6Table($dbAdapter);
                    }
                },
                'GeographicalDivisionsTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new GeographicalDivisionsTable($dbAdapter);
                    }
                },
                'UserLocationMapTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbAdapter = $diContainer->get('Laminas\Db\Adapter\Adapter');
                        return new UserLocationMapTable($dbAdapter);
                    }
                },
                'OdkFormService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new OdkFormService($diContainer);
                    }
                },
                'CommonService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new CommonService($diContainer);
                    }
                },
                'UserService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $usersTable = $diContainer->get('UsersTable');
                        return new UserService($diContainer, $usersTable);
                    }
                },
                'FacilityService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new FacilityService($diContainer);
                    }
                },
                'RoleService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new RoleService($diContainer);
                    }
                },
                'EventService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new EventService($diContainer);
                    }
                },
                'AuditTrailService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new AuditTrailService($diContainer);
                    }
                },
                'ProvinceService' => new class
                {
                    public function __invoke($diContainer)
                    {
                        return new ProvinceService($diContainer);
                    }
                },
            ),

        );
    }

}

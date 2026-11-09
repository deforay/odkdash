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
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new SpiFormVer3Table($dbConnection);
                    }
                },
                'SpiFormVer6Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new SpiFormVer6Table($dbConnection, $diContainer->get('translator'));
                    }
                },
                'UsersTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new UsersTable($dbConnection);
                    }
                },
                'SpiFormLabelsTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new SpiFormLabelsTable($dbConnection);
                    }
                },
                'SpiRtFacilitiesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new SpiRtFacilitiesTable($dbConnection);
                    }
                },
                'RolesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new RolesTable($dbConnection);
                    }
                },
                'GlobalTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new GlobalTable($dbConnection);
                    }
                },
                'EventLogTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new EventLogTable($dbConnection);
                    }
                },
                'ApiLogsTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new ApiLogsTable($dbConnection);
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
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new AuditMailTable($dbConnection);
                    }
                },
                'SpiFormVer3DownloadTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new SpiFormVer3DownloadTable($dbConnection);
                    }
                },
                'SpiFormVer6DownloadTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new SpiFormVer6DownloadTable($dbConnection);
                    }
                },
                'SpiFormVer3DuplicateTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new SpiFormVer3DuplicateTable($dbConnection);
                    }
                },
                'SpiFormVer6DuplicateTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new SpiFormVer6DuplicateTable($dbConnection);
                    }
                },
                'SpiFormVer3TempTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new SpiFormVer3TempTable($dbConnection);
                    }
                },
                'CountriesTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new CountriesTable($dbConnection);
                    }
                },
                'AuditSpiFormV3Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new AuditSpiFormV3Table($dbConnection);
                    }
                },
                'AuditSpiFormV6Table' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new AuditSpiFormV6Table($dbConnection);
                    }
                },
                'GeographicalDivisionsTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new GeographicalDivisionsTable($dbConnection);
                    }
                },
                'UserLocationMapTable' => new class
                {
                    public function __invoke($diContainer)
                    {
                        $dbConnection = $diContainer->get(\Doctrine\DBAL\Connection::class);
                        return new UserLocationMapTable($dbConnection);
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

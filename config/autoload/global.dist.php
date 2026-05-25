<?php

/**
 * Global Configuration Override (distributed template).
 *
 * Copy to global.php and adjust for the local environment. global.php itself
 * is gitignored so each deployment can pin its own DB name / host without
 * accidentally committing it. Sensitive values (credentials) belong in
 * local.php / custom.global.php, not here.
 */

return [
    'db' => [
        'driver' => 'Pdo',
        'dsn' => 'mysql:dbname=odkdash;host=localhost',
        'driver_options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ],
    ],
    'module_layouts' => [
        'Application' => 'layout/layout',
        'Application' => 'layout/modal'
    ],
    'service_manager' => [
        'factories' => [
            'Laminas\Db\Adapter\Adapter'
            => 'Laminas\Db\Adapter\AdapterServiceFactory',
        ],
    ],
];

<?php

namespace Application;

use Application\Command\SendTempMail;
use Application\Command\SendAuditMail;
use Application\Command\SyncCentralV3;
use Application\Command\SyncCentralV6;
use Application\Command\GenerateBulkPdf;
use Application\Command\CleanupOldLogs;
use Application\Command\AuditsUnlinked;
use Application\Command\ResetPassword;

return array(
    'translator' => [
        'locale' => 'en_US',
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'view_manager' => [
        // ErrorMiddleware reads display_exceptions. It defaults to false, so
        // production users never see stack traces, vendor paths or SQL on a
        // 500 page. Set 'view_manager.display_exceptions' => true in the
        // git-ignored config/autoload/local.php to see them locally.
        'display_not_found_reason' => false,
        'display_exceptions' => false,
    ],
    // Read by bin/console. Each name maps to a class ServiceDefinitions
    // builds; the name is bound onto the command at construction.
    'console' => [
        'commands' => [
            'send-mail' => SendTempMail::class,
            'send-audit-mail' => SendAuditMail::class,
            'sync-central-v3' => SyncCentralV3::class,
            'sync-central-v6' => SyncCentralV6::class,
            'generate-bulk-pdf' => GenerateBulkPdf::class,
            'cleanup-old-logs' => CleanupOldLogs::class,
            'audits:unlinked' => AuditsUnlinked::class,
            'reset-password' => ResetPassword::class,
        ],
    ],
);

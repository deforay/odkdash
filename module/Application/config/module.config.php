<?php

namespace Application;

use Application\Command\SendTempMail;
use Application\Command\SendAuditMail;
use Application\Command\SyncCentralV3;
use Application\Command\SyncCentralV6;
use Application\Command\GenerateBulkPdf;
use Application\Command\CleanupOldLogs;
use Application\Command\SendTempMailFactory;
use Application\Command\SendAuditMailFactory;
use Application\Command\SyncCentralV3Factory;
use Laminas\Db\Adapter\Adapter;
use Application\I18n\Translator;
use Application\Command\SyncCentralV6Factory;
use Application\Command\GenerateBulkPdfFactory;
use Application\Command\CleanupOldLogsFactory;
use Application\Command\AuditsUnlinked;
use Application\Command\AuditsUnlinkedFactory;
use Application\Command\ResetPassword;
use Application\Command\ResetPasswordFactory;

return array(
    'service_manager' => [
        'factories' => [
            'DbAdapter' => function ($container) {
                // Configuration for the database connection
                $config = $container->get('Config');
                $dbConfig = $config['db'];
                return new Adapter($dbConfig);
            },
            // Was Laminas\Mvc\I18n\TranslatorFactory. Reads the same
            // 'translator' config block below, so the catalogue location and
            // the locale are still declared in one place.
            'translator' => function ($container) {
                $config = $container->get('Config')['translator'] ?? [];
                $translator = new Translator($config['locale'] ?? 'en_US');

                foreach ($config['translation_file_patterns'] ?? [] as $filePattern) {
                    $translator->addPattern($filePattern['base_dir'], $filePattern['pattern']);
                }

                return $translator;
            },
            SendTempMail::class => SendTempMailFactory::class,
            SendAuditMail::class => SendAuditMailFactory::class,
            SyncCentralV3::class => SyncCentralV3Factory::class,
            SyncCentralV6::class => SyncCentralV6Factory::class,
            GenerateBulkPdf::class => GenerateBulkPdfFactory::class,
            CleanupOldLogs::class => CleanupOldLogsFactory::class,
            AuditsUnlinked::class => AuditsUnlinkedFactory::class,
            ResetPassword::class => ResetPasswordFactory::class,
        ]
    ],
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
    // Read by bin/console. Each name maps to a class the service manager
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

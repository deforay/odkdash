<?php

declare(strict_types=1);

namespace App\Services;

use Application\Command\AuditTrailInstall;
use Application\Command\AuditTrailInstallFactory;
use Application\Command\AuditsUnlinked;
use Application\Command\AuditsUnlinkedFactory;
use Application\Command\CleanupOldLogs;
use Application\Command\CleanupOldLogsFactory;
use Application\Command\GenerateBulkPdf;
use Application\Command\GenerateBulkPdfFactory;
use Application\Command\ResetPassword;
use Application\Command\ResetPasswordFactory;
use Application\Command\SendAuditMail;
use Application\Command\SendAuditMailFactory;
use Application\Command\SendTempMail;
use Application\Command\SendTempMailFactory;
use Application\Command\SyncCentralV3;
use Application\Command\SyncCentralV3Factory;
use Application\Command\SyncCentralV6;
use Application\Command\SyncCentralV6Factory;
use App\Db\ConfigPdoDriver;
use App\Db\QueryLog;
use Application\I18n\Translator;
use Application\Module;
use DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\Middleware as LoggingMiddleware;
use Psr\Container\ContainerInterface;

use function DI\factory;
use function DI\get;

/**
 * The application services, as PHP-DI definitions. The web app (Kernel),
 * bin/console and bin/migrate build their containers from these.
 *
 * Services are registered under string keys ('CommonService', 'GlobalTable',
 * 'AppAcl'), which the services pass to $this->sm->get('...'). Each one is
 * built on first use and shared after that.
 */
final class ServiceDefinitions
{
    /** Each command class and the factory that builds it. */
    private const array COMMANDS = [
        SendTempMail::class => SendTempMailFactory::class,
        SendAuditMail::class => SendAuditMailFactory::class,
        SyncCentralV3::class => SyncCentralV3Factory::class,
        SyncCentralV6::class => SyncCentralV6Factory::class,
        GenerateBulkPdf::class => GenerateBulkPdfFactory::class,
        CleanupOldLogs::class => CleanupOldLogsFactory::class,
        AuditsUnlinked::class => AuditsUnlinkedFactory::class,
        ResetPassword::class => ResetPasswordFactory::class,
        AuditTrailInstall::class => AuditTrailInstallFactory::class,
    ];

    /**
     * The application config, merged in this order. A later source overrides an earlier one:
     *
     * 1. module/Application/config/module.config.php.
     * 2. config/autoload/global.php, then *.global.php, then local.php,
     *    then *.local.php. Each glob is in alphabetical order.
     *
     * The 'service_manager' key that servers' global.php still carries is
     * not read. connection() opens the database from the 'db' block.
     *
     * @return array<string, mixed>
     */
    public static function config(string $appRoot): array
    {
        $config = (new Module())->getConfig();

        $dir = $appRoot . '/config/autoload';
        foreach (['global.php', '*.global.php', 'local.php', '*.local.php'] as $pattern) {
            foreach (glob($dir . '/' . $pattern) ?: [] as $file) {
                $config = self::merge($config, require $file);
            }
        }

        return $config;
    }

    /** @return array<string, mixed> */
    public static function definitions(string $appRoot): array
    {
        $definitions = [];
        foreach ((new Module())->getServiceConfig()['factories'] as $name => $serviceFactory) {
            $definitions[$name] = factory($serviceFactory);
        }
        foreach (self::COMMANDS as $command => $commandFactory) {
            $definitions[$command] = factory($commandFactory);
        }

        return [
            ...$definitions,
            'config' => static fn(): array => self::config($appRoot),
            'Config' => get('config'),
            Connection::class => static fn(ContainerInterface $c): Connection => self::connection($c),
            'translator' => static fn(ContainerInterface $c): Translator => self::translator($c),
        ];
    }

    /**
     * The one database connection, opened from config['db'] on first use,
     * with the query log when it is on. Every model and service shares it,
     * so DBAL counts nested transactions in one place.
     */
    private static function connection(ContainerInterface $c): Connection
    {
        $driver = new ConfigPdoDriver($c->get('config')['db'] ?? []);
        $log = QueryLog::fromEnvironment();
        if ($log !== null) {
            $driver = (new LoggingMiddleware($log))->wrap($driver);
        }

        return new Connection([], $driver);
    }

    /** A container for bin/console and bin/migrate. */
    public static function container(string $appRoot): Container
    {
        return new Container(self::definitions($appRoot));
    }

    /**
     * The translator, set up from the 'translator' config block. The web
     * app then sets the user's locale on it (Kernel::applyLocale()).
     */
    public static function translator(ContainerInterface $c): Translator
    {
        $config = $c->get('config')['translator'] ?? [];
        $translator = new Translator($config['locale'] ?? 'en_US');
        foreach ($config['translation_file_patterns'] ?? [] as $filePattern) {
            $translator->addPattern($filePattern['base_dir'], $filePattern['pattern']);
        }

        return $translator;
    }

    /**
     * Merges config arrays. A list item
     * from $b is appended. Two arrays under one string key are merged.
     * Anything else from $b replaces the value in $a.
     *
     * @param array<array-key, mixed> $a
     * @param array<array-key, mixed> $b
     * @return array<array-key, mixed>
     */
    private static function merge(array $a, array $b): array
    {
        foreach ($b as $key => $value) {
            if (!array_key_exists($key, $a)) {
                $a[$key] = $value;
            } elseif (is_int($key)) {
                $a[] = $value;
            } elseif (is_array($value) && is_array($a[$key])) {
                $a[$key] = self::merge($a[$key], $value);
            } else {
                $a[$key] = $value;
            }
        }

        return $a;
    }
}

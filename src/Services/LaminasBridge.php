<?php

declare(strict_types=1);

namespace App\Services;

use Application\Module;
use Closure;
use Laminas\Db\ConfigProvider as DbConfigProvider;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;

/**
 * Gives Slim handlers, bin/console and bin/migrate the existing Laminas
 * services (CommonService, GlobalTable, AppAcl and the rest) without
 * rewriting them.
 *
 * Nothing is built until a service is first asked for, so a Slim route that
 * needs no Laminas service never pays for loading it.
 *
 * This goes away once the service factories in Module.php move to PHP-DI.
 */
final class LaminasBridge
{
    private ?ServiceManager $serviceManager = null;

    /**
     * @param Closure(ServiceManager): void|null $onBoot Runs once, after the
     *        service manager is built. The web app sets the locale here.
     */
    public function __construct(
        private readonly string $appRoot,
        private readonly ?Closure $onBoot = null,
    ) {
    }

    /**
     * The application config, merged in the order the Laminas module
     * manager merged it. A later source overrides an earlier one:
     *
     * 1. laminas-db's services: the Adapter factory and its aliases.
     * 2. module/Application/config/module.config.php.
     * 3. config/autoload/global.php, then *.global.php, then local.php,
     *    then *.local.php. Each glob is in alphabetical order.
     *
     * @return array<string, mixed>
     */
    public static function config(string $appRoot): array
    {
        $config = ['service_manager' => (new DbConfigProvider())->getDependencyConfig()];
        $config = ArrayUtils::merge($config, (new Module())->getConfig());

        $dir = $appRoot . '/config/autoload';
        foreach (['global.php', '*.global.php', 'local.php', '*.local.php'] as $pattern) {
            foreach (glob($dir . '/' . $pattern) ?: [] as $file) {
                $config = ArrayUtils::merge($config, require $file);
            }
        }

        return $config;
    }

    /**
     * A service manager holding every service Module::getServiceConfig()
     * and the 'service_manager' config key define, plus the merged config
     * as 'config'. 'Config' is another name for it, as under Laminas.
     */
    public static function build(string $appRoot): ServiceManager
    {
        $config = self::config($appRoot);

        $services = ArrayUtils::merge((new Module())->getServiceConfig(), $config['service_manager'] ?? []);
        $services['services']['config'] = $config;
        $services['aliases']['Config'] = 'config';

        return new ServiceManager($services);
    }

    public function serviceManager(): ServiceManager
    {
        if ($this->serviceManager !== null) {
            return $this->serviceManager;
        }

        $this->serviceManager = self::build($this->appRoot);

        if ($this->onBoot !== null) {
            ($this->onBoot)($this->serviceManager);
        }

        return $this->serviceManager;
    }

    /** @param string $name A Laminas service manager key, such as 'CommonService'. */
    public function get(string $name): mixed
    {
        return $this->serviceManager()->get($name);
    }
}

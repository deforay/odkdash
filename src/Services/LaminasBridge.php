<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

/**
 * Gives Slim handlers the existing Laminas services (CommonService,
 * GlobalTable, AppAcl and the rest) without rewriting them.
 *
 * The boot is the one bin/console and bin/migrate use: load the modules and
 * configure the service manager, but never bootstrap or dispatch the MVC
 * application. Nothing is built until a service is first asked for, so a
 * Slim route that needs no Laminas service never pays for loading it.
 *
 * This goes away once the service factories in Module.php move to PHP-DI.
 */
final class LaminasBridge
{
    private ?ServiceManager $serviceManager = null;

    /**
     * @param Closure(ServiceManager): void|null $onBoot Runs once, after the
     *        modules load. Stands in for what Module::onBootstrap does to
     *        services on the Laminas side, such as setting the locale.
     */
    public function __construct(
        private readonly string $appRoot,
        private readonly ?Closure $onBoot = null,
    ) {
    }

    public function serviceManager(): ServiceManager
    {
        if ($this->serviceManager !== null) {
            return $this->serviceManager;
        }

        $appConfig = require $this->appRoot . '/config/application.config.php';

        $serviceManager = new ServiceManager();
        (new ServiceManagerConfig($appConfig['service_manager'] ?? []))->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $appConfig);
        $serviceManager->get('ModuleManager')->loadModules();

        $this->serviceManager = $serviceManager;

        if ($this->onBoot !== null) {
            ($this->onBoot)($serviceManager);
        }

        return $serviceManager;
    }

    /** @param string $name A Laminas service manager key, such as 'CommonService'. */
    public function get(string $name): mixed
    {
        return $this->serviceManager()->get($name);
    }
}

<?php

declare(strict_types=1);

namespace App\View;

use App\Http\Kernel;
use Laminas\View\Helper\Url;
use Psr\Container\ContainerInterface;

/**
 * Lets $this->url() on a Laminas-rendered page link to a page that has moved
 * to Slim. Without it, the layout's menu fails on the first route that
 * leaves module.config.php.
 *
 * Registered in Module::getViewHelperConfig() as a delegator on the Laminas
 * url helper, so the helper Laminas builds still handles every other route.
 */
final class SlimAwareUrlDelegator
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): callable
    {
        /** @var Url $laminasUrl */
        $laminasUrl = $callback();

        return static function (?string $route = null, array $params = [], $options = [], $reuseMatchedParams = false) use ($laminasUrl) {
            if ($route !== null && is_array($options)) {
                $url = Kernel::current()?->urlFor($route, $params, $options['query'] ?? []);
                if ($url !== null) {
                    return $url;
                }
            }

            return $laminasUrl($route, $params, $options, $reuseMatchedParams);
        };
    }
}

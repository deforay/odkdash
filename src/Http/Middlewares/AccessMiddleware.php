<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Services\LaminasBridge;
use App\View\UrlHelper;
use App\View\ViewRenderer;
use Application\Model\Acl;
use Application\Session\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Slim\Routing\RouteContext;

/**
 * Login and permission checks for Slim routes, in place of
 * Module::preSetter on the Laminas side.
 *
 * Every route is closed unless it says otherwise. A route names its ACL
 * resource and privilege as route arguments, which are the controller class
 * and action Laminas checked:
 *
 *     ->setArgument('resource', 'Application\Controller\LogViewerController')
 *     ->setArgument('privilege', 'index')
 *
 * A route anyone may call sets ->setArgument('access', 'public') instead.
 * A route with neither is a mistake and answers 500.
 *
 * AJAX requests without a login get a 401 instead of a redirect to the
 * login page.
 */
final class AccessMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responses,
        private readonly LaminasBridge $laminas,
        private readonly ViewRenderer $view,
        private readonly UrlHelper $url,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        if ($route === null) {
            return $handler->handle($request);
        }

        if ($route->getArgument('access') === 'public') {
            return $handler->handle($request);
        }

        $resource = $route->getArgument('resource');
        $privilege = $route->getArgument('privilege');
        if ($resource === null || $privilege === null) {
            throw new RuntimeException(sprintf(
                'Route "%s" names no ACL resource and privilege, and is not marked public.',
                $route->getPattern()
            ));
        }

        $session = new Container('credo');

        if (empty($session->userId)) {
            if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->responses->createResponse(401);
            }

            return $this->responses->createResponse(302)
                ->withHeader('Location', ($this->url)('login'));
        }

        /** @var Acl $acl */
        $acl = $this->laminas->get('AppAcl');
        if (!$acl->hasResource($resource) || !$acl->isAllowed($session->roleCode, $resource, $privilege)) {
            return $this->responses->createResponse(403);
        }

        // The layout builds its menus from this, as it did from the layout
        // ViewModel's acl variable.
        $this->view->setLayoutVariable('acl', $acl);

        // The layouts print the country name. preSetter fills it in on the
        // first request of a session.
        if (empty($session->countryName)) {
            $config = $this->laminas->get('CommonService')->getGlobalConfigDetails();
            $session->countryName = $config['country-name'] ?? null;
        }

        return $handler->handle($request);
    }
}

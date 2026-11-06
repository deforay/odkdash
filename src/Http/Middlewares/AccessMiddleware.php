<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Handlers\ActionHandler;
use App\Http\HelperAccess;
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
 * A route with an {action} placeholder leaves out the privilege: the action
 * named in the URL is the privilege, "index" when absent, as in Laminas.
 *
 * An action with no privilege in the privileges table is a helper that a
 * page's own JavaScript calls. The role edit screen cannot grant it, so a
 * role may call it when it may open one of the pages that do
 * (see HelperAccess). An action with a privilege is decided by the ACL alone.
 *
 * A route anyone may call sets ->setArgument('access', 'public') instead.
 * The route files mark it with 'public' => true. A route any signed-in user
 * may call, whatever the role, sets ->setArgument('access', 'signed-in'). The
 * route files mark it with 'signed_in' => true. A route with no resource
 * is a mistake and answers 500.
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
        private readonly HelperAccess $helpers,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        if ($route === null) {
            return $handler->handle($request);
        }

        $session = new Container('credo');

        // A public page needs no login. As in preSetter, a logged-in user
        // still gets the ACL, which the home layout's menu reads.
        if ($route->getArgument('access') === 'public') {
            $this->rememberCountry($session);
            if (!empty($session->userId)) {
                $this->view->setLayoutVariable('acl', $this->laminas->get('AppAcl'));
            }
            return $handler->handle($request);
        }

        $resource = $route->getArgument('resource');
        $signedIn = $route->getArgument('access') === 'signed-in';
        if ($resource === null && !$signedIn) {
            throw new RuntimeException(sprintf(
                'Route "%s" names no ACL resource and is not marked public.',
                $route->getPattern()
            ));
        }
        $privilege = $route->getArgument('privilege') ?? ActionHandler::action($request);

        if (empty($session->userId)) {
            if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->responses->createResponse(401);
            }

            return $this->responses->createResponse(302)
                ->withHeader('Location', ($this->url)('login'));
        }

        if ($signedIn) {
            return $handler->handle($request);
        }

        /** @var Acl $acl */
        $acl = $this->laminas->get('AppAcl');
        if (!$this->isAllowed($acl, (string) $session->roleCode, $resource, $privilege)) {
            return $this->responses->createResponse(403);
        }

        // The layout builds its menus from this, as it did from the layout
        // ViewModel's acl variable.
        $this->view->setLayoutVariable('acl', $acl);

        $this->rememberCountry($session);

        return $handler->handle($request);
    }

    /**
     * The layouts print the country name. preSetter fills it in on the first
     * request of a session.
     */
    private function rememberCountry(Container $session): void
    {
        if (empty($session->countryName)) {
            $config = $this->laminas->get('CommonService')->getGlobalConfigDetails();
            $session->countryName = $config['country-name'] ?? null;
        }
    }

    private function isAllowed(Acl $acl, string $role, string $resource, string $privilege): bool
    {
        if (!$acl->hasResource($resource)) {
            return false;
        }
        if ($acl->isAllowed($role, $resource, $privilege)) {
            return true;
        }

        return !$acl->isDefined($resource, $privilege)
            && $this->helpers->allows($acl, $role, $resource, $privilege);
    }
}

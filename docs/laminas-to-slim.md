# From Laminas MVC to Slim: where the code lives now

odkdash moved off laminas-mvc one controller at a time. Every request now runs on Slim, and laminas-mvc is no longer installed. Four Laminas packages remain, for the services and the database layer. See [What is still on Laminas](#what-is-still-on-laminas). This page explains the new layout for anyone who knows the Laminas one.

The short version: controllers moved from `module/` to `src/`, and templates moved from `module/Application/view/` to `templates/`. Services, models and config stay where they are.

## How a request is served

`public/index.php` runs the Slim app:

```php
App\Http\Kernel::fromGlobals()->run();
```

A path with no route answers the `error/404` page, which `ErrorMiddleware` renders as Laminas did.

Slim handlers reuse the Laminas services. `src/Services/LaminasBridge.php` builds a laminas-servicemanager from `Module::getServiceConfig()` and the merged config. `bin/console` and `bin/migrate` build theirs with the same `LaminasBridge::build()`.

## Old and new locations

| What | Laminas location | Slim location |
|---|---|---|
| Front controller | `public/index.php` | `public/index.php` |
| Autoloading | `init_autoloader.php` | `vendor/autoload.php` |
| Module list and config merge | `config/application.config.php` and the module manager | `LaminasBridge::config()` |
| Routes | `router.routes` in `module/Application/config/module.config.php` | `src/Http/Routes/Application.php`, one file per module |
| Controllers | `module/Application/src/Application/Controller/` | `src/Http/Handlers/` |
| Controller factories | anonymous classes in `Module::getControllerConfig()` | none: PHP-DI reads the constructor |
| Login and ACL check | `Module::preSetter()` | `src/Http/Middlewares/AccessMiddleware.php` |
| Error pages | Laminas view strategies | `src/Http/Middlewares/ErrorMiddleware.php` |
| `ViewModel`, `redirect()` | Laminas MVC | `src/Http/Responder.php` |
| View rendering | Laminas `PhpRenderer` | `src/View/ViewRenderer.php` and `Template.php` |
| View helpers (`url`, `translate`, …) | `view_helpers` config | `Kernel::viewHelpers()` |
| Templates | `module/Application/view/` | `templates/` |
| Services | `module/Application/src/Application/Service/` | unchanged |
| Models and table gateways | `module/Application/src/Application/Model/` | unchanged |
| Service wiring | `Module::getServiceConfig()` | unchanged, read by `LaminasBridge::build()` |
| Console commands | `bin/console` | unchanged |

`src/` uses the `App\` namespace. `module/` keeps the `Application\` namespace.

## Routes keep the Laminas format

A route in `src/Http/Routes/Application.php` is its entry from `module.config.php` with a `handler` key added:

```php
// module.config.php
'roles' => [
    'type' => 'segment',
    'options' => [
        'route' => '/roles[/:action][/][:id]',
        'defaults' => ['controller' => 'Application\Controller\RolesController', 'action' => 'index'],
    ],
],

// src/Http/Routes/Application.php
'roles' => [
    'route' => '/roles[/:action][/][:id]',
    'handler' => RolesHandler::class,
    'defaults' => ['controller' => 'Application\Controller\RolesController', 'action' => 'index'],
],
```

The route name, the pattern and the defaults do not change:

- The name is what `$this->url('roles')` uses, on both sides.
- The pattern builds URLs exactly as Laminas built them. `Kernel::SLIM_TAILS` turns it into the Slim pattern.
- `defaults.controller` is the ACL resource, the value `Module::preSetter()` checked.

A route without a `handler` is registered for URL building only. A route with `'public' => true` needs no login. Only the landing page and the login page are public. A route with `'signed_in' => true` needs a login but no ACL grant: any role may call it, and the handler decides what the user gets. Only `/download` has it.

A route whose Laminas pattern `Kernel::SLIM_TAILS` cannot express gives its Slim pattern in a `slim` key. The landing page is the one case. Its Laminas pattern matched any single path segment, such as `/foo`, so its Slim pattern is `/[{id}]`. It must stay the last route. FastRoute refuses a fixed path such as `/login` declared after `/{id}`. The app then fails on every request.

## What a controller becomes

A Laminas controller becomes a handler with the same action methods. `SpiV3Controller::editAction()` becomes `SpiV3Handler::editAction()`. URLs do not change. `/spi-v3/approve-status` still reaches `approveStatusAction()`.

Laminas:

```php
class FacilityController extends AbstractActionController
{
    public function addAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->facilityService->addFacility($request->getPost());
            return $this->redirect()->toRoute('spi-facility');
        }
        return new ViewModel([
            'provinceResult' => $this->provinceService->getAllActiveProvinces(),
        ]);
    }
}
```

Slim:

```php
final class FacilityHandler extends ActionHandler
{
    public function addAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isPost($request)) {
            $this->facilityService->addFacility($this->post($request));
            return $this->respond->redirect('spi-facility');
        }
        return $this->respond->page('application/facility/add', [
            'provinceResult' => $this->provinceService->getAllActiveProvinces(),
        ]);
    }
}
```

The services arrive through the constructor. There is no factory to write. `ActionHandler` maps the `{action}` part of the URL to the method, as `AbstractActionController` did.

### Laminas idioms and their replacements

| Laminas | Slim handler |
|---|---|
| `$request->isPost()` | `$this->isPost($request)` |
| `$request->getPost()` | `$this->post($request)` (the same `Parameters` object) |
| `$this->params()->fromRoute('id')` | `$this->routeParam($request, 'id')` |
| `$this->params()->fromQuery('q')` | `$request->getQueryParams()['q'] ?? null` |
| `return new ViewModel([...])` | `return $this->respond->page('application/x/y', [...])` |
| `$viewModel->setTerminal(true)` | `return $this->respond->fragment('application/x/y', [...])` |
| `$this->layout()->setTemplate('layout/modal')` | `$this->respond->page('application/x/y', [...], 'layout/modal')` |
| `$this->getResponse()->setContent($json)` | `return $this->respond->html($json)` |
| `$this->redirect()->toRoute('name')` | `return $this->respond->redirect('name')` |
| `$this->redirect()->toUrl('/x')` | `return $this->respond->redirectToUrl('/x')` |
| A TCPDF template that calls `Output(..., 'I')` | `return $this->respond->output('application/x/y', [...])` |

A handler names its template in full. Laminas derived it from the controller and action names.

## Templates moved, but did not change

The `.phtml` files moved from `module/Application/view/` to `templates/` with their folders intact. Template names did not change: `'application/spi-v3/index'` is `templates/application/spi-v3/index.phtml`. The `application/` folder is the module name, as Laminas named it.

The files themselves are the same. `ViewRenderer` runs them with the same variables and the same helper names: `$this->url()`, `$this->translate()`, `$this->layout()->acl` and the others. A template does not know which framework rendered it.

Route names did not change either. Every `$this->url('spi-v3-form', ...)` in a template builds the same URL as before.


## Access control

`AccessMiddleware` replaces `Module::preSetter()`. It applies the same ACL from the same tables, with three differences:

- **The ACL applies to AJAX requests too.** `preSetter()` checked only the login on AJAX requests.
- **Helper actions inherit access from their pages.** A helper is an action that a page's JavaScript calls and that has no row in the `privileges` table, such as a table feed or a dropdown fill. A role that may open a page that calls the helper may call the helper. `bin/dev/helper-access` builds the list of callers from the templates into `config/helper-access.php`. Run it after a template starts calling a new helper. `composer qc` fails when the file is out of date.
- **Every route must name an ACL resource or be marked public.** A route that does neither answers 500, so a forgotten check cannot leave a page open.

## What is still on Laminas

No controller, route, module or MVC event is left. Two packages remain, and they bring a third, laminas-stdlib, which the app no longer calls itself:

| Package | Used by | Next step |
|---|---|---|
| laminas-servicemanager | `LaminasBridge` and the factories in `Module::getServiceConfig()` | Move the factories to PHP-DI. |
| laminas-db | The models and table gateways | Replace with Doctrine DBAL or PDO, one model at a time. This is the last step. |

Two packages are gone:

- laminas-escaper. `App\View\Escape` replaces it. Its output matched Laminas on 200,011 random strings, invalid UTF-8 included.
- laminas-stdlib as a direct dependency. `App\Http\Parameters` replaces `Parameters` with the same behaviour: null for a missing key, and property access. `LaminasBridge::merge()` replaces `ArrayUtils::merge()`. The merged config and the registered services are identical.

`LaminasBridge::config()` merges the config in the order the module manager merged it:

1. laminas-db's adapter services.
2. `module/Application/config/module.config.php`.
3. `config/autoload/global.php`, then `*.global.php`, then `local.php`, then `*.local.php`.

A later file overrides an earlier one, as before. `config/autoload/` did not change.

Composer lists `module/Application/Module.php` in its `classmap`. The module manager used to load it.

The ODK push receivers, `/receiver` and `/receiver-spi-v6`, were removed rather than moved. Submissions come from ODK Central through the `sync-central` commands. The login OTP step was removed as well. No instance used it.

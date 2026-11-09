# From Laminas MVC to Slim: where the code lives now

odkdash moved off Laminas one piece at a time. Every request runs on Slim, and the database layer is Doctrine DBAL. No Laminas package is installed. See [What replaced each Laminas package](#what-replaced-each-laminas-package). This page explains the new layout for anyone who knows the Laminas one.

The short version: controllers moved from `module/` to `src/`, and templates moved from `module/Application/view/` to `templates/`. Services, models and config stay where they are.

## How a request is served

`public/index.php` runs the Slim app:

```php
App\Http\Kernel::fromGlobals()->run();
```

A path with no route answers the `error/404` page, which `ErrorMiddleware` renders as Laminas did.

Slim handlers reuse the existing services through PHP-DI. `src/Services/ServiceDefinitions.php` turns the factories in `Module::getServiceConfig()` into PHP-DI definitions, under the same string keys, so `$this->sm->get('CommonService')` keeps working. The Kernel, `bin/console` and `bin/migrate` build their containers from the same definitions.

## Old and new locations

| What | Laminas location | Slim location |
|---|---|---|
| Front controller | `public/index.php` | `public/index.php` |
| Autoloading | `init_autoloader.php` | `vendor/autoload.php` |
| Module list and config merge | `config/application.config.php` and the module manager | `ServiceDefinitions::config()` |
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
| Service wiring | `Module::getServiceConfig()` and laminas-servicemanager | `Module::getServiceConfig()`, read by `ServiceDefinitions::definitions()` into PHP-DI |
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

## What replaced each Laminas package

| Package | Replaced by |
|---|---|
| laminas-mvc, laminas-router | Slim 4. See [How a request is served](#how-a-request-is-served). |
| laminas-servicemanager | PHP-DI. `ServiceDefinitions` registers every service under its old key. The factories in `Module::getServiceConfig()` are invokable objects, and `DI\factory()` passes them the container as their first argument, so their bodies did not change. |
| laminas-escaper | `App\View\Escape`. Its output matched Laminas on 200,011 random strings, invalid UTF-8 included. |
| laminas-stdlib | `App\Http\Parameters` replaces `Parameters`, with null for a missing key and property access. `ServiceDefinitions::merge()` replaces `ArrayUtils::merge()`. |
| laminas-db | Doctrine DBAL. See [The database](#the-database). |

### The database

Every model takes the one `Doctrine\DBAL\Connection`. Models build each other with `new SomeTable($db)`, and services get the connection from the container as `Connection::class`. One shared object means DBAL counts nested transactions in one place.

| laminas-db | DBAL |
|---|---|
| `$adapter->query($sql, Adapter::QUERY_MODE_EXECUTE)->toArray()` | `$db->fetchAllAssociative($sql, $values)` |
| `new Sql($adapter)`, `Select`, `where([...])` | SQL with `?` placeholders and a list of values |
| `CommonService::dataTablesSearch()` and a sort string | `App\Db\DataTables::search()`, `order()` and `limit()` |
| The table gateway's `insert()`, `update()`, `delete()` | The DBAL methods of the same names |
| `$adapter->getDriver()->getConnection()->beginTransaction()` | `$db->beginTransaction()` |

DBAL's `insert()` and `update()` do not quote column names. Quote a name that is not a plain word, such as `meta-instance-id`, with `$db->quoteSingleIdentifier()`. `SpiFormVer6DuplicateTable` does this.

A list that an export reruns stores its query in the session as `['sql' => ..., 'values' => [...]]`, without the page. The export returns no file when the key holds anything else.

Begin and commit a transaction on the container's `Connection`. Roll back when the save returns 0 and in the `catch`. `UserService` shows the pattern.

`App\Db\ConfigPdoDriver` opens the connection from `config['db']`, read as laminas-db read it. The keys `driver`, `dsn`, `driver_options`, `username` and `password` mean what they meant, so no server's `config/autoload/` had to change.

`ODKDASH_SQL_LOG=<file>` makes the app log every statement, so two versions of the code can be compared request by request.

`ServiceDefinitions::config()` merges the config in the order the module manager merged it:

1. `module/Application/config/module.config.php`.
2. `config/autoload/global.php`, then `*.global.php`, then `local.php`, then `*.local.php`.

A later file overrides an earlier one, as before. `config/autoload/` did not change. Servers' `global.php` still holds a `service_manager` key naming laminas-db's `AdapterServiceFactory`. Nothing reads it.

Composer lists `module/Application/Module.php` in its `classmap`. The module manager used to load it.

The ODK push receivers, `/receiver` and `/receiver-spi-v6`, were removed rather than moved. Submissions come from ODK Central through the `sync-central` commands. The login OTP step was removed as well. No instance used it.

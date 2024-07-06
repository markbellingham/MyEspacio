<?php

declare(strict_types=1);

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tracy\Debugger;

use function FastRoute\simpleDispatcher;

// phpcs:disable
define('ROOT_DIR', dirname(__DIR__));
define('CONFIG', require ROOT_DIR . '/config/config.php');
require ROOT_DIR . '/vendor/autoload.php';
// phpcs:enable
Debugger::enable();
$request = Request::createFromGlobals();

function parseLanguage(Request $request): string
{
    $supportedLanguages = ['en','es','fr'];
    $path = $request->getPathInfo();
    if (preg_match('#^/([a-zA-Z]{2})(/|$)#', $path, $matches)) {
        $language = $matches[1];
        if (in_array($language, $supportedLanguages)) {
            $newPath = substr($path, 3);
            $request->server->set('REQUEST_URI', $newPath);
            return $language;
        }
    }
    return 'en';
}

$request->attributes->set('language', parseLanguage($request));

$dispatcher = simpleDispatcher(
    function (RouteCollector $r) {
        $routes = include ROOT_DIR . '/src/Routes.php';
        foreach ($routes as $route) {
            $r->addRoute(...$route);
        }
    }
);
$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        $response = new Response('Not Found', 404);
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        $response = new Response('Method Not Allowed', 405);
        break;
    case Dispatcher::FOUND:
        [$controllerName, $method] = explode('#', $routeInfo[1]);
        $vars = $routeInfo[2];
        $injector = include 'Dependencies.php';
        $controller = $injector->make($controllerName);
        $response = $controller->$method($request, $vars);
        break;
    default:
        $response = null;
}

if (!$response instanceof Response) {
    throw new Exception('Controller methods must return a Response object');
}

$response->prepare($request);
$response->send();

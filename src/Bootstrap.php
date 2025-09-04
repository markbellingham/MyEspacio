<?php

declare(strict_types=1);

use FastRoute\Dispatcher;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Localisation\Language;
use MyEspacio\Framework\Localisation\LanguageDetector;
use MyEspacio\Framework\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tracy\Debugger;

// phpcs:disable
define('ROOT_DIR', dirname(__DIR__));
define('CONFIG', require ROOT_DIR . '/config/config.php');
require ROOT_DIR . '/vendor/autoload.php';
// phpcs:enable
Debugger::enable();
$request = Request::createFromGlobals();

$languageDetector = new LanguageDetector(Language::cases());
$language = $languageDetector->getFromPath($request);
$request->attributes->set('language', $language->value);
$request->server->set('REQUEST_URI', $languageDetector->removeLanguagePrefix($request));

$router = new Router(['Contact', 'Home', 'Photos', 'User']);
$dispatcher = $router->createDispatcher();

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
        $vars = new DataSet($routeInfo[2]);
        $container = include 'Dependencies.php';
        $controller = $container->get($controllerName);
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

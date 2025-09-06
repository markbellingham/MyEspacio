<?php

declare(strict_types=1);

use MyEspacio\Framework\Http\RequestDispatcher;
use MyEspacio\Framework\Localisation\Language;
use MyEspacio\Framework\Localisation\LanguageDetector;
use MyEspacio\Framework\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
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
$container = include 'Dependencies.php';
$requestDispatcher = new RequestDispatcher($router, $container);
$response = $requestDispatcher->dispatch($request);
$response->send();

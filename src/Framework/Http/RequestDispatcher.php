<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

use Exception;
use FastRoute\Dispatcher;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Routing\RouterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class RequestDispatcher
{
    public function __construct(
        private RouterInterface $router,
        private ContainerInterface $container,
    ) {
    }

    public function dispatch(Request $request): Response
    {
        $dispatcher = $this->router->createDispatcher();
        $routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

        switch ($routeInfo[0]) {
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = new Response('Method Not Allowed', 405);
                break;
            case Dispatcher::FOUND:
                /** @var array{1, class-string, array<string,string>} $routeInfo */
                $response = $this->handleFoundRoute($request, $routeInfo);
                break;
            default:
                $response = new Response('Not Found', 404);
        }
        $response->prepare($request);
        return $response;
    }

    /** @param array{0:int, 1:string, 2:array<string,string>} $routeInfo */
    private function handleFoundRoute(Request $request, array $routeInfo): Response
    {
        [$controllerName, $method] = explode('#', $routeInfo[1]);
        $vars = new DataSet($routeInfo[2]);

        $controller = $this->container->get($controllerName);
        $response = $controller->$method($request, $vars);

        if (!$response instanceof Response) {
            throw new Exception('Controller methods must return a Response object');
        }

        return $response;
    }
}

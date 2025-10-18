<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Routing;

use FastRoute\RouteCollector;
use LogicException;
use ReflectionClass;
use ReflectionMethod;

final class RouteRegistrar
{
    /** @param class-string[] $controllers */
    public static function registerRoutes(RouteCollector $r, array $controllers): void
    {
        $registeredRoutes = [];
        $routes = [];

        foreach ($controllers as $controllerClass) {
            $reflection = new ReflectionClass($controllerClass);

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $attributes = $method->getAttributes(Route::class);

                foreach ($attributes as $attribute) {
                    /** @var Route $route */
                    $route = $attribute->newInstance();

                    $routes[] = [
                        'route' => $route,
                        'handler' => $controllerClass . '#' . $method->getName(),
                        'priority' => $route->priority
                    ];
                }
            }
        }

        usort($routes, function ($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return 0;
            }
            return $a['priority'] <=> $b['priority'];
        });

        foreach ($routes as $routeData) {
            $route = $routeData['route'];
            $handler = $routeData['handler'];
            $routeKey = $route->method->value . ':' . $route->path;

            if (isset($registeredRoutes[$routeKey])) {
                throw new LogicException(
                    "Route conflict detected: {$route->method->value} {$route->path} " .
                    "is defined in both {$registeredRoutes[$routeKey]} and {$handler}"
                );
            }

            $registeredRoutes[$routeKey] = $handler;
            $r->addRoute($route->method->value, $route->path, $handler);
        }
    }
}

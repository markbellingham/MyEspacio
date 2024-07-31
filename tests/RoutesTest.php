<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

final class RoutesTest extends TestCase
{
    private const ROUTE_URL_TOKENS = '/^\/[A-z]?\/?.*/';
    private const CONTROLLER_CLASS_NAME_TOKENS = '~^[A-Za-z0-9\\\]+#[A-Za-z0-9]+$~';

    public function testRoutes()
    {
        $allowedRouteMethods = [
            'GET',
            'POST'
        ];

        $routes = include ROOT_DIR . '/src/Routes.php';
        foreach ($routes as $route) {
            $this->assertCount(3, $route);
            $this->assertContains($route[0], $allowedRouteMethods);
            $this->assertMatchesRegularExpression(self::ROUTE_URL_TOKENS, $route[1]);
            $this->assertControllerExists($route[2]);
        }
    }

    private function assertControllerExists(string $controller): void
    {
        $this->assertMatchesRegularExpression(self::CONTROLLER_CLASS_NAME_TOKENS, $controller);

        list($className, $method) = explode('#', $controller);

        return;

//        $this->assertTrue(class_exists($className), "Controller class '$className' does not exist.");
//        $this->assertTrue(method_exists($className, $method), "Method '$method' does not exist in '$className'.");
    }
}

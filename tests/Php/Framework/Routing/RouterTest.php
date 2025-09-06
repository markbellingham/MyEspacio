<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Routing;

use FastRoute\Dispatcher;
use MyEspacio\Framework\Routing\Router;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testCreateDispatcherReturnsValidDispatcher(): void
    {
        $router = new Router(modules: ['Photos']);
        $dispatcher = $router->createDispatcher();

        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
    }

    #[DataProvider('dispatcherValidRouteDataProvider')]
    public function testDispatcherValidRoute(
        string $route,
        string $httpMethod,
    ): void {
        $router = new Router(modules: ['Photos']);
        $dispatcher = $router->createDispatcher();

        $routeInfo = $dispatcher->dispatch($httpMethod, $route);

        $this->assertSame(Dispatcher::FOUND, $routeInfo[0]);
        $this->assertStringContainsString('#', $routeInfo[1]);
    }

    /** @return array<string, array<string, mixed>> */
    public static function dispatcherValidRouteDataProvider(): array
    {
        return [
            'test_1' => [
                'route' => '/photos',
                'httpMethod' => 'GET',
            ],
            'test_2' => [
                'route' => '/photos/123',
                'httpMethod' => 'GET',
            ],
        ];
    }

    #[DataProvider('dispatcherInvalidRouteDataProvider')]
    public function testDispatcherInvalidRoute(
        string $route,
        string $httpMethod,
        int $dispatcherCode,
    ): void {
        $router = new Router(modules: ['Photos']);
        $dispatcher = $router->createDispatcher();

        $routeInfo = $dispatcher->dispatch($httpMethod, $route);
        $this->assertSame($dispatcherCode, $routeInfo[0]);
    }

    /** @return array<string, array<string, mixed>> */
    public static function dispatcherInvalidRouteDataProvider(): array
    {
        return [
            'not_found' => [
                'route' => '/bad-route',
                'httpMethod' => 'GET',
                'dispatcherCode' => Dispatcher::NOT_FOUND,
            ],
            'method_not_allowed' => [
                'route' => '/photos',
                'httpMethod' => 'POST',
                'dispatcherCode' => Dispatcher::METHOD_NOT_ALLOWED,
            ],
        ];
    }
}

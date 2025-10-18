<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Routing;

use FastRoute\RouteCollector;
use LogicException;
use MyEspacio\Framework\Routing\RouteRegistrar;
use PHPUnit\Framework\TestCase;
use Tests\Php\Framework\Routing\Presentation\ConflictingRouteController;
use Tests\Php\Framework\Routing\Presentation\PriorityTestController;
use Tests\Php\Framework\Routing\Presentation\TestController;

final class RouteRegistrarTest extends TestCase
{
    public function testRoutesAreRegistered(): void
    {
        $collector = $this->createMock(RouteCollector::class);

        $expected = [
            ['GET', '/dummy', TestController::class . '#dummy'],
            ['POST', '/dummy', TestController::class . '#dummyPost'],
            ['GET', '/dummy/{id:\d+}', TestController::class . '#dummyWithId'],
        ];

        $collector->expects($this->exactly(count($expected)))
            ->method('addRoute')
            ->willReturnCallback(function ($method, $path, $handler) use (&$expected) {
                $this->assertNotEmpty($expected, 'No more expected calls');
                [$expMethod, $expPath, $expHandler] = array_shift($expected);

                $this->assertSame($expMethod, $method);
                $this->assertSame($expPath, $path);
                $this->assertSame($expHandler, $handler);
            });

        RouteRegistrar::registerRoutes($collector, [TestController::class]);

        $this->assertEmpty($expected, 'All expected routes should be consumed');
    }

    public function testRouteConflictThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Route conflict detected');

        $collector = $this->createMock(RouteCollector::class);

        RouteRegistrar::registerRoutes($collector, [
            TestController::class,
            ConflictingRouteController::class
        ]);
    }

    public function testRoutesAreRegisteredInPriorityOrder(): void
    {
        $collector = $this->createMock(RouteCollector::class);

        $expectedOrder = [
            ['DELETE', '/test', PriorityTestController::class . '#defaultPriority'],  // priority: 0
            ['POST', '/test', PriorityTestController::class . '#highPriority'],      // priority: 1
            ['PUT', '/test', PriorityTestController::class . '#mediumPriority'],     // priority: 2
            ['GET', '/test', PriorityTestController::class . '#lowPriority'],       // priority: 3
        ];

        $actualOrder = [];
        $collector->expects($this->exactly(4))
            ->method('addRoute')
            ->willReturnCallback(function ($method, $path, $handler) use (&$actualOrder) {
                $actualOrder[] = [$method, $path, $handler];
            });

        RouteRegistrar::registerRoutes($collector, [PriorityTestController::class]);

        $this->assertSame($expectedOrder, $actualOrder, 'Routes should be registered in priority order (lower priority numbers first)');
    }
}

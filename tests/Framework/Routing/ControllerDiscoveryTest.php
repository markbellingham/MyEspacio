<?php

declare(strict_types=1);

namespace Tests\Framework\Routing;

use LogicException;
use MyEspacio\Framework\Routing\ControllerDiscovery;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ControllerDiscoveryTest extends TestCase
{
    public function testItDiscoversControllerClasses(): void
    {
        $classes = ControllerDiscovery::discover(
            directory: __DIR__ . '/../Routing/Presentation',
            namespace: 'Tests\\Framework\\Routing\\Presentation'
        );

        $this->assertEqualsCanonicalizing(
            [
                'Tests\\Framework\\Routing\\Presentation\\DummyController',
                'Tests\\Framework\\Routing\\Presentation\\TestController',
                'Tests\\Framework\\Routing\\Presentation\\ConflictingRouteController',
            ],
            $classes
        );
    }

    public function testNoBaseController(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Class Tests\Framework\Routing\Fixtures1\NoBaseController does not extend BaseController');

        ControllerDiscovery::discover(
            directory: __DIR__ . '/../Routing/Fixtures1',
            namespace: 'Tests\\Framework\\Routing\\Fixtures1'
        );
    }

    public function testClassNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Class Tests\Framework\Routing\Fixtures2\ClassNotFoundController does not exist');

        ControllerDiscovery::discover(
            directory: __DIR__ . '/../Routing/Fixtures2',
            namespace: 'Tests\\Framework\\Routing\\Fixtures2'
        );
    }
}

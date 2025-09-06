<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Routing;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;

final readonly class Router implements RouterInterface
{
    /** @param array<int, string> $modules */
    public function __construct(
        private array $modules,
        private string $rootDirectory = ROOT_DIR . '/src/',
        private string $namespace = 'MyEspacio',
    ) {
    }

    public function createDispatcher(): Dispatcher
    {
        return simpleDispatcher(function (RouteCollector $r) {
            $controllers = [];

            foreach ($this->modules as $module) {
                $controllers = array_merge(
                    $controllers,
                    ControllerDiscovery::discover(
                        directory: $this->rootDirectory . $module . '/Presentation',
                        namespace: $this->namespace . "\\$module\\Presentation"
                    )
                );
            }

            RouteRegistrar::registerRoutes($r, $controllers);
        });
    }
}

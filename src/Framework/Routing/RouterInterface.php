<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Routing;

use FastRoute\Dispatcher;

interface RouterInterface
{
    public function createDispatcher(): Dispatcher;
}

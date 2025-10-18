<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Routing\Presentation;

use MyEspacio\Framework\BaseController;
use MyEspacio\Framework\Routing\HttpMethod;
use MyEspacio\Framework\Routing\Route;

final class PriorityTestController extends BaseController
{
    #[Route('/test', HttpMethod::GET, priority: 4)]
    public function lowPriority(): void
    {
    }

    #[Route('/test', HttpMethod::POST, priority: 2)]
    public function highPriority(): void
    {
    }

    #[Route('/test', HttpMethod::PUT, priority: 3)]
    public function mediumPriority(): void
    {
    }

    #[Route('/test', HttpMethod::DELETE)]
    public function defaultPriority(): void
    {
    }
}

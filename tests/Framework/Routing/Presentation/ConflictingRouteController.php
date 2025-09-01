<?php

declare(strict_types=1);

namespace Tests\Framework\Routing\Presentation;

use MyEspacio\Framework\BaseController;
use MyEspacio\Framework\Routing\HttpMethod;
use MyEspacio\Framework\Routing\Route;

final class ConflictingRouteController extends BaseController
{
    #[Route('/conflicting', HttpMethod::GET)]
    public function conflicting(): void
    {
    }

    #[Route('/conflicting', HttpMethod::GET)]
    public function conflicting2(): void
    {
    }
}

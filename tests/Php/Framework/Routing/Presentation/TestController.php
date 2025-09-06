<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Routing\Presentation;

use MyEspacio\Framework\BaseController;
use MyEspacio\Framework\Routing\HttpMethod;
use MyEspacio\Framework\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

final class TestController extends BaseController
{
    #[Route('/dummy', method: HttpMethod::GET)]
    public function dummy(): Response
    {
        return new Response('dummy ok', 200);
    }

    #[Route('/dummy', method: HttpMethod::POST)]
    public function dummyPost(): Response
    {
        return new Response('dummy post', 200);
    }

    #[Route('/dummy/{id:\d+}', method: HttpMethod::GET)]
    public function dummyWithId(): Response
    {
        return new Response('dummy with id', 200);
    }

    /**
     * Deliberate unused method to test RouteRegistrar
     * @phpstan-ignore-next-line
     */
    #[Route('/unusedMethod/{id:\d+}', method: HttpMethod::GET)]
    private function methodNotPublic(): Response
    {
        return new Response('method not public', 200);
    }
}

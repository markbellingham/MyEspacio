<?php

declare(strict_types=1);

namespace Tests\Framework\Routing\Fixtures1;

use GuzzleHttp\Psr7\Response;
use MyEspacio\Framework\Routing\HttpMethod;
use MyEspacio\Framework\Routing\Route;

final class NoBaseController
{
    #[Route('/show', HttpMethod::GET)]
    public function show(): Response
    {
        return new Response();
    }
}

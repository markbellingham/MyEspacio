<?php

declare(strict_types=1);

namespace MyEspacio\Home\Presentation;

use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HomePageController
{
    public function __construct(
        private RequestHandlerInterface $requestHandler,
    ) {
    }

    public function show(Request $request): Response
    {
        $valid = $this->requestHandler->validate($request);

        return $this->requestHandler->sendResponse(new ResponseData(
            template: 'home/FrontPage.html.twig'
        ));
    }
}

<?php

declare(strict_types=1);

namespace MyEspacio\Home\Presentation;

use MyEspacio\Framework\BaseController;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Framework\Routing\HttpMethod;
use MyEspacio\Framework\Routing\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HomePageController extends BaseController
{
    public function __construct(
        private readonly RequestHandlerInterface $requestHandler,
    ) {
    }

    #[Route('/', HttpMethod::GET)]
    public function show(Request $request): Response
    {
        $valid = $this->requestHandler->validate($request);

        return $this->requestHandler->sendResponse(new ResponseData(
            template: 'home/FrontPage.html.twig'
        ));
    }

    #[Route('/home', HttpMethod::GET)]
    public function homeAlias(): RedirectResponse
    {
        return new RedirectResponse('/');
    }
}

<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Presentation;

use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Photos\Application\PhotoSearchInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class PhotoController
{
    public function __construct(
        private PhotoSearchInterface $photoSearch,
        private RequestHandlerInterface $requestHandler
    ) {
    }

    /** @param array<string, mixed> $vars */
    public function photoGrid(Request $request, array $vars): Response
    {
        $valid = $this->requestHandler->validate($request);
        if ($valid === false) {
            return $this->requestHandler->showRoot($request, $vars);
        }

        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: [
                    'photos' => $this->photoSearch->search($vars['searchPhotos'])
                ],
                template: 'photos/Photos.html.twig'
            )
        );
    }
}

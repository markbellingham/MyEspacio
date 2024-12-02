<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Presentation;

use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Photos\Application\PhotoSearchInterface;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class PhotoController
{
    public function __construct(
        private PhotoRepositoryInterface $photoRepository,
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

        $results = $this->photoSearch->search($vars['searchPhotos']);
        $key = is_a($results, PhotoAlbum::class) ? 'album' : 'photos';
        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: [
                    $key => $results
                ],
                template: 'photos/PhotoGrid.html.twig'
            )
        );
    }

    /** @param array<string, mixed> $vars */
    public function singlePhoto(Request $request, array $vars): Response
    {
        $valid = $this->requestHandler->validate($request);
        if ($valid === false) {
            return $this->requestHandler->showRoot($request, $vars);
        }

        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: [
                    'photo' => $this->photoRepository->fetchByUuid(($vars['uuid'] ?? ''))
                ],
                template: 'photos/SinglePhoto.html.twig'
            )
        );
    }
}

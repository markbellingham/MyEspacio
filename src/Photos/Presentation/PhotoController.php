<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Presentation;

use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Photos\Application\PhotoSearchInterface;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use Ramsey\Uuid\Uuid;
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

        $results = $this->photoSearch->search(
            albumName: $vars['album'] ?? null,
            searchTerms: $request->query->get('search')
        );
        $template = is_a($results, PhotoAlbum::class)
            ? 'photos/PhotoAlbumView.html.twig'
            : 'photos/PhotosNoAlbumView.html.twig';
        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: [
                    'photos' => $results
                ],
                template: $template
            )
        );
    }

    /** @param array<string, mixed> $vars */
    public function singlePhoto(Request $request, array $vars): Response
    {
        $valid = $this->requestHandler->validate($request);

        $uuid = Uuid::fromString($vars['uuid'] ?? '');

        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: [
                    'photo' => $this->photoRepository->fetchByUuid($uuid)
                ],
                template: 'photos/SinglePhoto.html.twig'
            )
        );
    }
}

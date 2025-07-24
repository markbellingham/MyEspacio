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
        $albumName = $vars['album'] ?? null;
        if (!is_null($albumName) && !is_string($albumName)) {
            return new Response('Invalid album name', Response::HTTP_BAD_REQUEST);
        }
        $results = $this->photoSearch->search(
            albumName: $albumName,
            searchTerms: $request->query->getString('search')
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
        $uuidString = $vars['uuid'] ?? '';
        if (is_string($uuidString) === false) {
            return new Response('Invalid UUID', Response::HTTP_BAD_REQUEST);
        }
        $uuid = Uuid::fromString($uuidString);

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

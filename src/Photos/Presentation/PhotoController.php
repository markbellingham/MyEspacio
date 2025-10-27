<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Presentation;

use MyEspacio\Framework\BaseController;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Framework\Routing\HttpMethod;
use MyEspacio\Framework\Routing\Route;
use MyEspacio\Photos\Application\PhotoSearchInterface;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PhotoController extends BaseController
{
    public function __construct(
        private readonly PhotoAlbumRepositoryInterface $albumRepository,
        private readonly PhotoRepositoryInterface $photoRepository,
        private readonly PhotoSearchInterface $photoSearch,
        private readonly RequestHandlerInterface $requestHandler
    ) {
    }

    #[Route('/photos[/[{album:.+}]]', HttpMethod::GET, priority: 2)]
    public function photoGrid(Request $request, DataSet $pathParams): Response
    {
        $valid = $this->requestHandler->validate($request);

        $photos = $this->photoSearch->search(
            albumName: $pathParams->stringNull('album'),
            searchTerms: $request->query->getString('search')
        );
        $albums = $this->albumRepository->fetchAll();

        $template = $this->determineAlbumTemplate($valid, $photos);

        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: [
                    'albums' => $albums,
                    'photos' => $photos
                ],
                template: $template
            )
        );
    }

    #[Route('/photo/{uuid:.+}', HttpMethod::GET)]
    #[Route('/photos/{album:.+}/photo/{uuid:.+}', HttpMethod::GET, priority: 1)]
    public function singlePhoto(Request $request, DataSet $pathParams): Response
    {
        $valid = $this->requestHandler->validate($request);
        $uuid = $pathParams->uuidNull('uuid');

        if ($uuid === null) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    data: [],
                    statusCode: Response::HTTP_BAD_REQUEST,
                    translationKey: 'photos.invalid_uuid'
                )
            );
        }

        $data = [];
        $data['photo'] = $this->photoRepository->fetchByUuid($uuid);

        if ($valid === false) {
            $data['albums'] = $this->albumRepository->fetchAll();
            $data['photos'] = $this->photoSearch->search(
                albumName: $pathParams->stringNull('album'),
                searchTerms: $request->query->getString('search')
            );
        }

        $template = $this->determinePhotoTemplate($valid, $data['photos'] ?? null);

        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: $data,
                template: $template
            )
        );
    }

    private function determineAlbumTemplate(bool $validRequest, PhotoAlbum|PhotoCollection|null $photos): string
    {
        if ($validRequest && $photos instanceof PhotoCollection) {
            return 'photos/partials/photo-grid.html.twig';
        }
        if ($validRequest && $photos instanceof PhotoAlbum) {
            return 'photos/partials/album-grid.html.twig';
        }
        if ($photos instanceof PhotoAlbum) {
            return 'photos/photo-album.html.twig';
        }
        return 'photos/photos.html.twig';
    }

    private function determinePhotoTemplate(bool $validRequest, PhotoAlbum|PhotoCollection|null $photos): string
    {
        if ($validRequest) {
            return 'photos/partials/single-photo.html.twig';
        }
        if ($photos instanceof PhotoAlbum) {
            return 'photos/photo-album.html.twig';
        }
        return 'photos/photos.html.twig';
    }
}

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

    #[Route('/photos[/[{album:.+}]]', HttpMethod::GET)]
    public function photoGrid(Request $request, DataSet $pathParams): Response
    {
        $valid = $this->requestHandler->validate($request);

        $results = $this->photoSearch->search(
            albumName: $pathParams->stringNull('album'),
            searchTerms: $request->query->getString('search')
        );
        $albums = $this->albumRepository->fetchAll();

        $template = is_a($results, PhotoAlbum::class)
            ? 'photos/PhotoAlbumView.html.twig'
            : 'photos/PhotosNoAlbumView.html.twig';

        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: [
                    'albums' => $albums,
                    'photos' => $results
                ],
                template: $template
            )
        );
    }

    #[Route('/photo/{uuid:.+}', HttpMethod::GET)]
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

        $photo = $this->photoRepository->fetchByUuid($uuid);

        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: [
                    'photo' => $photo
                ],
                template: 'photos/SinglePhoto.html.twig'
            )
        );
    }
}

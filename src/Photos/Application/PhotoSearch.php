<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Application;

use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;

final class PhotoSearch implements PhotoSearchInterface
{
    private ?int $albumId;
    private int $paramCount;
    /** @var array<int, string> */
    private array $params;

    public function __construct(
        private readonly PhotoAlbumRepositoryInterface $photoAlbumRepository,
        private readonly PhotoRepositoryInterface $photoRepository
    ) {
    }

    public function search(array $params): PhotoCollection|PhotoAlbum
    {
        $this->parseSearchParams($params['searchPhotos'] ?? '');
        $this->isAlbum(urldecode($this->params[0]));

        if ($this->albumId && $this->paramCount === 1) {
            return $this->albumPhotos();
        }
        if ($this->albumId && $this->paramCount > 1) {
            return $this->searchAlbumPhotos();
        }
        if ($this->albumId === null && $this->paramCount === 1) {
            return $this->popularSearchTypes();
        }
        return $this->photoRepository->randomSelection();
    }

    private function parseSearchParams(string $requestParams): void
    {
        $this->params = explode('/', $requestParams);
        $this->params = array_filter($this->params);
        $this->paramCount = count($this->params);
    }

    private function isAlbum(string $arg): void
    {
        $this->albumId = $this->photoAlbumRepository->albumExists(trim($arg));
    }

    private function albumPhotos(): PhotoAlbum
    {
        $album = $this->photoAlbumRepository->fetchById($this->albumId);
        $album->setPhotos(
            $this->photoAlbumRepository->fetchAlbumPhotos($album)
        );
        return $album;
    }

    private function searchAlbumPhotos(): PhotoAlbum
    {
        $album = $this->photoAlbumRepository->fetchById($this->albumId);
        $album->setPhotos(
            $this->photoAlbumRepository->searchAlbumPhotos($album, $this->params)
        );
        return $album;
    }

    private function popularSearchTypes(): PhotoCollection
    {
        return match ($this->params[0]) {
            'most-popular' => $this->photoRepository->topPhotos(),
            'my-favourites' => $this->photoAlbumRepository->fetchMyFavourites(),
            default => $this->photoRepository->searchAllPhotos($this->params)
        };
    }
}

<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Application;

use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;

final class PhotoSearch implements PhotoSearchInterface
{
    private ?PhotoAlbum $album = null;
    private int $paramCount;
    /** @var array<int, string> */
    private array $params;

    public function __construct(
        private readonly PhotoAlbumRepositoryInterface $photoAlbumRepository,
        private readonly PhotoRepositoryInterface $photoRepository
    ) {
    }

    public function search(?string $album, ?string $searchTerms): PhotoCollection|PhotoAlbum
    {
        $this->parseSearchParams($searchTerms);
        $this->isAlbum($album);

        if ($this->album && $this->paramCount === 0) {
            return $this->albumPhotos();
        }
        if ($this->album && $this->paramCount > 0) {
            return $this->searchAlbumPhotos();
        }
        if ($this->album === null && $this->paramCount > 0) {
            return $this->popularSearchTypes();
        }
        return $this->photoRepository->randomSelection();
    }

    private function parseSearchParams(?string $requestParams): void
    {
        $this->params = explode(' ', $requestParams ?? '');
        $this->params = array_filter($this->params);
        $this->paramCount = count($this->params);
        if ($this->paramCount > 0) {
            $this->params = array_map(fn($param) => trim($param), $this->params);
        }
    }

    private function isAlbum(?string $album): void
    {
        if ($album === null) {
            return;
        }
        $this->album = $this->photoAlbumRepository->fetchByName(trim($album));
    }

    private function albumPhotos(): PhotoAlbum
    {
        $this->album->setPhotos(
            $this->photoAlbumRepository->fetchAlbumPhotos($this->album)
        );
        return $this->album;
    }

    private function searchAlbumPhotos(): PhotoAlbum
    {
        $this->album->setPhotos(
            $this->photoAlbumRepository->searchAlbumPhotos($this->album, $this->params)
        );
        return $this->album;
    }

    private function popularSearchTypes(): PhotoCollection
    {
        return match ($this->params[0]) {
            'most-popular' => $this->photoRepository->topPhotos(),
            default => $this->photoRepository->search($this->params)
        };
    }
}

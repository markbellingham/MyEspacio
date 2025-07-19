<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Application;

use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;

final class PhotoSearch implements PhotoSearchInterface
{
    private const string POPULAR_PHOTOS_KEYWORD = 'most-popular';
    const array NON_ALBUM_SEARCHES = [
        self::POPULAR_PHOTOS_KEYWORD
    ];
    private const string COMMON_SEARCH_TERMS_DIVISORS = '/[\s\/\-_.,;]+/';

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
        if (
            $this->album === null &&
            in_array($album, self::NON_ALBUM_SEARCHES, true)
        ) {
            return $this->popularSearchTypes($album);
        }
        if ($this->album === null && $this->paramCount > 0) {
            return $this->photoRepository->search($this->params);
        }
        return $this->photoRepository->randomSelection();
    }

    private function parseSearchParams(?string $requestParams): void
    {
        $this->params = preg_split(
            pattern: self::COMMON_SEARCH_TERMS_DIVISORS,
            subject: $requestParams ?? '',
            limit: -1,
            flags: PREG_SPLIT_NO_EMPTY
        );
        $this->paramCount = count($this->params);
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

    private function popularSearchTypes(?string $searchType): PhotoCollection
    {
        return match ($searchType) {
            self::POPULAR_PHOTOS_KEYWORD => $this->photoRepository->topPhotos(),
            default => $this->photoRepository->search($this->params)
        };
    }
}

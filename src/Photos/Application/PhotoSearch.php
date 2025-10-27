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
    private const string COMMON_SEARCH_TERMS_DIVISORS = '/\s[$\/\-_.,;]+/';

    private int $paramCount;
    /** @var array<int, string> */
    private array $params;

    public function __construct(
        private readonly PhotoAlbumRepositoryInterface $photoAlbumRepository,
        private readonly PhotoRepositoryInterface $photoRepository
    ) {
    }

    public function search(?string $albumName, ?string $searchTerms): PhotoCollection|PhotoAlbum
    {
        $this->parseSearchParams($searchTerms);
        $album = $this->isAlbum(urldecode((string) $albumName));

        if ($album && $this->paramCount === 0) {
            return $this->albumPhotos($album);
        }
        if ($album && $this->paramCount > 0) {
            return $this->searchAlbumPhotos($album);
        }
        if (
            $album === null &&
            in_array($albumName, self::NON_ALBUM_SEARCHES, true)
        ) {
            return $this->popularSearchTypes($albumName);
        }
        if ($album === null && $this->paramCount > 0) {
            return $this->photoRepository->search($this->params);
        }
        return $this->photoRepository->randomSelection();
    }

    private function parseSearchParams(?string $requestParams): void
    {
        $result = preg_split(
            pattern: self::COMMON_SEARCH_TERMS_DIVISORS,
            subject: $requestParams ?? '',
            limit: -1,
            flags: PREG_SPLIT_NO_EMPTY
        );
        $this->params = $result ?: [];
        $this->paramCount = count($this->params);
    }

    private function isAlbum(?string $album): ?PhotoAlbum
    {
        if (in_array($album, [null, '', 'all'])) {
            return null;
        }
        return $this->photoAlbumRepository->fetchByName(trim($album));
    }

    private function albumPhotos(PhotoAlbum $album): PhotoAlbum
    {
        $album->setPhotos(
            $this->photoAlbumRepository->fetchAlbumPhotos($album)
        );
        return $album;
    }

    private function searchAlbumPhotos(PhotoAlbum $album): PhotoAlbum
    {
        $album->setPhotos(
            $this->photoAlbumRepository->searchAlbumPhotos($album, $this->params)
        );
        return $album;
    }

    private function popularSearchTypes(?string $searchType): PhotoCollection
    {
        return match ($searchType) {
            self::POPULAR_PHOTOS_KEYWORD => $this->photoRepository->topPhotos(),
            default => $this->photoRepository->search($this->params)
        };
    }
}

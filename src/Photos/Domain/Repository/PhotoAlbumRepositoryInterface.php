<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Repository;

use MyEspacio\Photos\Domain\Collection\PhotoAlbumCollection;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;

interface PhotoAlbumRepositoryInterface
{
    public function fetchById(int $albumId): ?PhotoAlbum;

    public function fetchAll(): PhotoAlbumCollection;

    public function fetchAlbumPhotos(PhotoAlbum $album): PhotoCollection;

    /**
     * @param array<int, string> $searchTerms
     */
    public function searchAlbumPhotos(PhotoAlbum $album, array $searchTerms): PhotoCollection;

    public function fetchByName(string $albumName): ?PhotoAlbum;

    public function fetchMyFavourites(): ?PhotoAlbum;
}

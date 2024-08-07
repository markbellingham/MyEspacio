<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Repository;

use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Photo;

interface PhotoRepositoryInterface
{
    public function findOne(int $photoId): ?Photo;

    public function fetchAlbumPhotos(int $albumId): PhotoCollection;
}

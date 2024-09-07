<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Repository;

use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Photo;

interface PhotoRepositoryInterface
{
    public function fetchById(int $photoId): ?Photo;

    public function topPhotos(): PhotoCollection;

    public function randomSelection(): PhotoCollection;

    /**
     * @param array<int, string> $searchTerms
     */
    public function searchAllPhotos(array $searchTerms): PhotoCollection;
}

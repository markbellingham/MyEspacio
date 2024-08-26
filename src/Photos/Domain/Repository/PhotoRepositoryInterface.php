<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Repository;

use MyEspacio\Photos\Domain\Entity\Photo;

interface PhotoRepositoryInterface
{
    public function fetchById(int $photoId): ?Photo;
}

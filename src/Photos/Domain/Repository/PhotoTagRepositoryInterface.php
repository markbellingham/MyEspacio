<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Repository;

use MyEspacio\Photos\Domain\Collection\PhotoTagCollection;
use MyEspacio\Photos\Domain\Entity\Photo;

interface PhotoTagRepositoryInterface
{
    public function fetchForPhoto(Photo $photo): PhotoTagCollection;
}

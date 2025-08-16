<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Repository;

use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoFave;

interface PhotoFaveRepositoryInterface
{
    public function add(PhotoFave $fave): bool;

    public function addAnonymous(PhotoFave $fave): bool;

    public function countForPhoto(Photo $photo): int;
}

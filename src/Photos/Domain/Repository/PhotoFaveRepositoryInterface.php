<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Repository;

use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoFave;
use MyEspacio\User\Domain\User;

interface PhotoFaveRepositoryInterface
{
    public function save(PhotoFave $fave): bool;

    public function add(PhotoFave $fave): bool;

    public function addAnonymous(PhotoFave $fave): bool;

    public function countForPhoto(Photo $photo): int;

    public function delete(PhotoFave $fave): void;

    public function isUserFave(Photo $photo, User $user): bool;
}

<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Fave;
use MyEspacio\User\Domain\User;

final class PhotoFave extends Fave
{
    public function __construct(
        private readonly Photo $photo,
        private readonly User $user
    ) {
        parent::__construct(
            userUuid: $this->user->getUuid(),
            itemUuid: $this->photo->getUuid()
        );
    }

    public function getPhoto(): Photo
    {
        return $this->photo;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}

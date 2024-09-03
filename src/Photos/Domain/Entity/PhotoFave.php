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
            userId: $this->user->getId(),
            itemId: $this->photo->getId()
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

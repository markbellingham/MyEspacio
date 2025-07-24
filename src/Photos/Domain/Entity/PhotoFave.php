<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Fave;
use MyEspacio\Framework\Exceptions\FaveException;
use MyEspacio\User\Domain\User;
use Ramsey\Uuid\UuidInterface;

final class PhotoFave extends Fave
{
    /**
     * @throws FaveException
     */
    public function __construct(
        private readonly Photo $photo,
        private readonly User $user
    ) {
        if ($photo->getUuid() === null) {
            throw FaveException::noNullValues();
        }
        /** @var UuidInterface $photoUuid */
        $photoUuid = $this->photo->getUuid();

        parent::__construct(
            userUuid: $this->user->getUuid(),
            itemUuid: $photoUuid
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

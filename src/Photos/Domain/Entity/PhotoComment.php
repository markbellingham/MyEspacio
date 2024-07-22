<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use DateTimeImmutable;
use MyEspacio\Common\Domain\Entity\Comment;

final class PhotoComment extends Comment
{
    public function __construct(
        private readonly int $photoId,
        private readonly string $comment,
        private readonly ?DateTimeImmutable $created,
        private readonly ?string $title,
        private readonly int $userId,
        private readonly string $username
    ) {
        parent::__construct(
            $this->comment,
            $this->created,
            $this->title,
            $this->userId,
            $this->username
        );
    }

    public function getPhotoId(): int
    {
        return $this->photoId;
    }
}

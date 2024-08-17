<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use DateTimeImmutable;
use MyEspacio\Common\Domain\Entity\Comment;
use MyEspacio\Framework\DataSet;

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

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'photoId' => $this->photoId,
            'comment' => $this->comment,
            'created' => $this->getCreatedString(),
            'username' => $this->username
        ];
    }

    public static function createFromDataSet(DataSet $data): Comment
    {
        return new PhotoComment(
            photoId: $data->int('photo_id'),
            comment: $data->string('comment'),
            created: $data->dateTimeNull('created'),
            title: $data->stringNull('title'),
            userId: $data->int('user_id'),
            username: $data->string('username')
        );
    }
}

<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use MyEspacio\Common\Domain\Entity\Comment;
use MyEspacio\Framework\DataSet;
use Ramsey\Uuid\UuidInterface;

final class PhotoComment extends Comment
{
    public function __construct(
        private readonly UuidInterface $photoUuid,
        private readonly string $comment,
        private readonly DateTimeImmutable $created,
        private readonly ?string $title,
        private readonly UuidInterface $userUuid,
        private readonly string $username
    ) {
        parent::__construct(
            $this->comment,
            $this->created,
            $this->title,
            $this->userUuid,
            $this->username
        );
    }

    public function getPhotoUuid(): UuidInterface
    {
        return $this->photoUuid;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'photoUuid' => $this->photoUuid->toString(),
            'comment' => $this->comment,
            'created' => $this->getCreated()?->format(DateTimeInterface::ATOM),
            'username' => $this->username,
            'userUuid' => $this->userUuid->toString()
        ];
    }

    public static function createFromDataSet(DataSet $data): Comment
    {
        return new PhotoComment(
            photoUuid: $data->uuid('photo_uuid'),
            comment: $data->string('comment'),
            created: $data->utcDateTime('created'),
            title: $data->stringNull('title'),
            userUuid: $data->uuid('user_uuid'),
            username: $data->string('username')
        );
    }
}

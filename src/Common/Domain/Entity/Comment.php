<?php

namespace MyEspacio\Common\Domain\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;
use Ramsey\Uuid\UuidInterface;

class Comment extends Model
{
    public function __construct(
        private string $comment,
        private DateTimeImmutable $created,
        private readonly ?string $title,
        private UuidInterface $userUuid,
        private string $username,
    ) {
    }

    public function getUserUuid(): UuidInterface
    {
        return $this->userUuid;
    }

    public function setUserUuid(UuidInterface $userUuid): void
    {
        $this->userUuid = $userUuid;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setCreated(DateTimeImmutable $datetime): void
    {
        $this->created = $datetime;
    }

    public function jsonSerialize(): array
    {
        return [
            'comment' => $this->comment,
            'created' => $this->created->format(DateTimeInterface::ATOM),
            'username' => $this->username,
            'userUuid' => $this->userUuid->toString(),
        ];
    }

    public static function createFromDataSet(DataSet $data): Comment
    {
        return new Comment(
            comment: $data->string('comment'),
            created: $data->utcDateTime('created'),
            title: $data->string('title'),
            userUuid: $data->uuid('user_uuid'),
            username: $data->string('username')
        );
    }
}

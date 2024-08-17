<?php

namespace MyEspacio\Common\Domain\Entity;

use DateTimeImmutable;
use Exception;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;

class Comment extends Model
{
    public function __construct(
        private string $comment = '',
        private ?DateTimeImmutable $created = null,
        private readonly ?string $title = '',
        private int $userId = 0,
        private string $username = ''
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    public function getCreatedString(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->created ? $this->created->format($format) : '';
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

    /**
     * @throws Exception
     */
    public function setCreated(string $datetime): void
    {
        $this->created = new DateTimeImmutable($datetime);
    }

    public function jsonSerialize(): array
    {
        return [
            'comment' => $this->comment,
            'created' => $this->getCreatedString(),
            'username' => $this->username
        ];
    }

    public static function createFromDataSet(DataSet $data): Comment
    {
        return new Comment(
            comment: $data->string('comment'),
            created: $data->dateTimeNull('created'),
            title: $data->string('title'),
            userId: $data->int('user_id'),
            username: $data->string('username')
        );
    }
}

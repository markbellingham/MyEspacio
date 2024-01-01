<?php

namespace MyEspacio\Common\Domain;

use DateTimeImmutable;
use Exception;
use JsonSerializable;

class Comment implements JsonSerializable
{
    public function __construct(
        private string $comment = '',
        private ?DateTimeImmutable $created = null,
        private readonly ?string $title = '',
        private int $user_id = 0,
        private string $username = ''
    ) {
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
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
        return get_object_vars($this);
    }
}

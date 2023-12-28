<?php

namespace Personly\Common\Domain;

use DateTimeImmutable;
use Exception;
use Personly\Common\Application\CommentValidator;

class Comment
{
    protected string $comment;
    protected ?DateTimeImmutable $created;
    protected string $title;
    protected int $user_id;
    protected string $username;
    protected bool $verified = false;

    protected array $dateProperties = [
        'created'
    ];

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

    public function getCreated(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->created->format($format);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function isVerified(): bool
    {
        $this->verified = (new CommentValidator($this))->validate();
        return $this->verified;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setCreated(string $datetime): void
    {
        try {
            $this->created = new DateTimeImmutable($datetime);
        } catch (Exception $e) {
            $this->created = null;
        }
    }
}

<?php

namespace MyEspacio\Common\Application;

use MyEspacio\Common\Domain\Entity\Comment;
use Ramsey\Uuid\UuidInterface;

final readonly class CommentValidator
{
    public function __construct(
        private Comment $comment,
    ) {
    }

    public function validate(): bool
    {
        if (trim($this->comment->getComment()) === '') {
            return false;
        }

        if (
            $this->comment->getTitle() !== '' &&
            $this->comment->getTitle() !== null
        ) {
            return false;
        }

        if ($this->comment->getComment() !== strip_tags($this->comment->getComment())) {
            return false;
        }

        $danger = ['http', 'https', 'www', 'href', '@', 'src'];
        foreach ($danger as $word) {
            if (str_contains($this->comment->getComment(), $word)) {
                return false;
            }
        }

        if (preg_match('/\d{6,}/', $this->comment->getComment())) {
            return false;
        }

        return true;
    }
}

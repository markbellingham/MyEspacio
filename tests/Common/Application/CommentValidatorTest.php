<?php

declare(strict_types=1);

namespace Tests\Common\Application;

use DateTimeImmutable;
use MyEspacio\Common\Application\CommentValidator;
use MyEspacio\Common\Domain\Entity\Comment;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Nonstandard\Uuid;

final class CommentValidatorTest extends TestCase
{
    public function testValidComment(): void
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            userUuid: Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertTrue($validator->validate());
    }

    public function testInvalidUserId(): void
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            userUuid: null,
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertFalse($validator->validate());
    }

    public function testCommentIsEmpty(): void
    {
        $comment = new Comment(
            comment: '',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            userUuid: Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertFalse($validator->validate());
    }

    public function testInvalidTitle(): void
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: 'Title',
            userUuid: Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertFalse($validator->validate());
    }

    public function testCommentContainsTags(): void
    {
        $comment = new Comment(
            comment: '<div class="my-class">Hello</div>',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            userUuid: Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertFalse($validator->validate());
    }

    public function testCommentContainsHtml(): void
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            userUuid: Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
            username: 'Mark Bellingham'
        );

        $securityPatterns = ['http', 'https', 'www', 'href', '@', 'src'];
        foreach ($securityPatterns as $pattern) {
            $comment->setComment($pattern);
            $validator = new CommentValidator($comment);
            $this->assertFalse($validator->validate());
        }
    }

    public function testCommentContainsConsecutiveNumbers(): void
    {
        $comment = new Comment(
            comment: '01234567890',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            userUuid: Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertFalse($validator->validate());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Common\Application;

use DateTimeImmutable;
use MyEspacio\Common\Application\CommentValidator;
use MyEspacio\Common\Domain\Comment;
use PHPUnit\Framework\TestCase;

final class CommentValidatorTest extends TestCase
{
    public function testValidComment()
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            user_id: 2,
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertTrue($validator->validate());
    }

    public function testInvalidUserId()
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            user_id: 1,
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertFalse($validator->validate());
    }

    public function testCommentIsEmpty()
    {
        $comment = new Comment(
            comment: '',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            user_id: 2,
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertFalse($validator->validate());
    }

    public function testInvalidTitle()
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: 'Title',
            user_id: 1,
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertFalse($validator->validate());
    }

    public function testCommentContainsTags()
    {
        $comment = new Comment(
            comment: '<div class="my-class">Hello</div>',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            user_id: 2,
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertFalse($validator->validate());
    }

    public function testCommentContainsHtml()
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            user_id: 2,
            username: 'Mark Bellingham'
        );

        $securityPatterns = ['http', 'https', 'www', 'href', '@', 'src'];
        foreach ($securityPatterns as $pattern) {
            $comment->setComment($pattern);
            $validator = new CommentValidator($comment);
            $this->assertFalse($validator->validate());
        }
    }

    public function testCommentContainsConsecutiveNumbers()
    {
        $comment = new Comment(
            comment: '01234567890',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            user_id: 2,
            username: 'Mark Bellingham'
        );

        $validator = new CommentValidator($comment);
        $this->assertFalse($validator->validate());
    }
}

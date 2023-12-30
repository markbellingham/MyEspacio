<?php

declare(strict_types=1);

namespace Tests\Common\Domain;

use DateTimeImmutable;
use MyEspacio\Common\Domain\Comment;
use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase
{
    public function testComment()
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            user_id: 2,
            username: 'Mark Bellingham'
        );

        $this->assertEquals('Hello', $comment->getComment());
        $this->assertInstanceOf(DateTimeImmutable::class, $comment->getCreated());
        $this->assertEquals('30-12-2023 @ 12:13', $comment->getCreatedString('d-m-Y @ H:i'));
        $this->assertEquals(2, $comment->getUserId());
        $this->assertEquals('Mark Bellingham', $comment->getUsername());
        $this->assertEquals(
            [
                'comment' => 'Hello',
                'created' => DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
                'title' => null,
                'user_id' => 2,
                'username' => 'Mark Bellingham'
            ],
            $comment->jsonSerialize()
        );
    }

    public function testCommentDefault()
    {
        $comment = new Comment();

        $this->assertEquals('', $comment->getComment());
        $this->assertNull($comment->getCreated());
        $this->assertEquals('', $comment->getTitle());
        $this->assertEquals(0, $comment->getUserId());
        $this->assertEquals('', $comment->getUsername());
        $this->assertEquals(
            [
                'comment' => '',
                'created' => null,
                'title' => '',
                'user_id' => 0,
                'username' => ''
            ],
            $comment->jsonSerialize()
        );
    }

    public function testCommentNulls()
    {
        $comment = new Comment(
            comment: '',
            created: null,
            title: null,
            user_id: 0,
            username: ''
        );

        $this->assertNull($comment->getCreated());
        $this->assertNull($comment->getTitle());
        $this->assertEquals(
            [
                'comment' => '',
                'created' => null,
                'title' => null,
                'user_id' => 0,
                'username' => ''
            ],
            $comment->jsonSerialize()
        );
    }
}

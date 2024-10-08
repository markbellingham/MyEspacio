<?php

declare(strict_types=1);

namespace Tests\Common\Domain\Entity;

use DateTimeImmutable;
use Exception;
use MyEspacio\Common\Domain\Entity\Comment;
use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase
{
    public function testComment(): void
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            userId: 2,
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
                'created' => '2023-12-30 12:13:14',
                'username' => 'Mark Bellingham'
            ],
            $comment->jsonSerialize()
        );
    }

    public function testCommentDefault(): void
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
                'username' => ''
            ],
            $comment->jsonSerialize()
        );
    }

    public function testCommentNulls(): void
    {
        $comment = new Comment(
            comment: '',
            created: null,
            title: null,
            userId: 0,
            username: ''
        );

        $this->assertNull($comment->getCreated());
        $this->assertNull($comment->getTitle());
        $this->assertEquals(
            [
                'comment' => '',
                'created' => null,
                'username' => ''
            ],
            $comment->jsonSerialize()
        );
    }

    public function testCommentSetters(): void
    {
        $comment = new Comment(
            comment: 'Hello',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-30 12:13:14'),
            title: null,
            userId: 2,
            username: 'Mark Bellingham'
        );
        $this->assertEquals('Hello', $comment->getComment());
        $this->assertInstanceOf(DateTimeImmutable::class, $comment->getCreated());
        $this->assertEquals('30-12-2023 @ 12:13', $comment->getCreatedString('d-m-Y @ H:i'));
        $this->assertEquals(2, $comment->getUserId());
        $this->assertEquals('Mark Bellingham', $comment->getUsername());

        $comment->setComment('Hello World');
        $comment->setCreated('2023-12-31 23:59');
        $comment->setUserId(5);
        $comment->setUsername('Joe Bloggs');

        $this->assertEquals('Hello World', $comment->getComment());
        $this->assertInstanceOf(DateTimeImmutable::class, $comment->getCreated());
        $this->assertEquals('31-12-2023 @ 23:59', $comment->getCreatedString('d-m-Y @ H:i'));
        $this->assertEquals(5, $comment->getUserId());
        $this->assertEquals('Joe Bloggs', $comment->getUsername());
    }

    public function testCreatedException(): void
    {
        $comment = new Comment();
        $this->expectException(Exception::class);
        $comment->setCreated('invalid_date');
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'comment' => 'Hello',
            'created' => '2023-12-30 12:13:14',
            'title' => null,
            'user_id' => 2,
            'username' => 'Mark Bellingham'
        ]);

        $comment = Comment::createFromDataSet($data);
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('Hello', $comment->getComment());
        $this->assertInstanceOf(DateTimeImmutable::class, $comment->getCreated());
        $this->assertEquals('2023-12-30 12:13:14', $comment->getCreatedString());
        $this->assertEquals('', $comment->getTitle());
        $this->assertSame(2, $comment->getUserId());
        $this->assertEquals('Mark Bellingham', $comment->getUsername());
    }
}

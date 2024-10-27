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
            userUuid: '39fa7943-6fa7-4412-97c8-c6cec6a44e0b',
            username: 'Mark Bellingham'
        );

        $this->assertEquals('Hello', $comment->getComment());
        $this->assertInstanceOf(DateTimeImmutable::class, $comment->getCreated());
        $this->assertEquals('30-12-2023 @ 12:13', $comment->getCreated()->format('d-m-Y @ H:i'));
        $this->assertEquals('39fa7943-6fa7-4412-97c8-c6cec6a44e0b', $comment->getUserUuid());
        $this->assertEquals('Mark Bellingham', $comment->getUsername());
        $this->assertEquals(
            [
                'comment' => 'Hello',
                'created' => '2023-12-30 12:13:14',
                'username' => 'Mark Bellingham',
                'userUuid' => '39fa7943-6fa7-4412-97c8-c6cec6a44e0b'
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
        $this->assertEquals('', $comment->getUserUuid());
        $this->assertEquals('', $comment->getUsername());
        $this->assertEquals(
            [
                'comment' => '',
                'created' => null,
                'username' => '',
                'userUuid' => ''
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
            userUuid: '',
            username: ''
        );

        $this->assertNull($comment->getCreated());
        $this->assertNull($comment->getTitle());
        $this->assertEquals(
            [
                'comment' => '',
                'created' => null,
                'username' => '',
                'userUuid' => ''
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
            userUuid: '39fa7943-6fa7-4412-97c8-c6cec6a44e0b',
            username: 'Mark Bellingham'
        );
        $this->assertEquals('Hello', $comment->getComment());
        $this->assertInstanceOf(DateTimeImmutable::class, $comment->getCreated());
        $this->assertEquals('30-12-2023 @ 12:13', $comment->getCreated()->format('d-m-Y @ H:i'));
        $this->assertEquals('39fa7943-6fa7-4412-97c8-c6cec6a44e0b', $comment->getUserUuid());
        $this->assertEquals('Mark Bellingham', $comment->getUsername());

        $comment->setComment('Hello World');
        $comment->setCreated('2023-12-31 23:59');
        $comment->setUserUuid('39fa7943-6fa7-4412-97c8-c6cec6a44e0b');
        $comment->setUsername('Joe Bloggs');

        $this->assertEquals('Hello World', $comment->getComment());
        $this->assertInstanceOf(DateTimeImmutable::class, $comment->getCreated());
        $this->assertEquals('31-12-2023 @ 23:59', $comment->getCreated()->format('d-m-Y @ H:i'));
        $this->assertEquals('39fa7943-6fa7-4412-97c8-c6cec6a44e0b', $comment->getUserUuid());
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
            'user_uuid' => '39fa7943-6fa7-4412-97c8-c6cec6a44e0b',
            'username' => 'Mark Bellingham'
        ]);

        $comment = Comment::createFromDataSet($data);
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('Hello', $comment->getComment());
        $this->assertInstanceOf(DateTimeImmutable::class, $comment->getCreated());
        $this->assertEquals('2023-12-30 12:13:14', $comment->getCreated()->format('Y-m-d H:i:s'));
        $this->assertEquals('', $comment->getTitle());
        $this->assertSame('39fa7943-6fa7-4412-97c8-c6cec6a44e0b', $comment->getUserUuid());
        $this->assertEquals('Mark Bellingham', $comment->getUsername());
    }
}

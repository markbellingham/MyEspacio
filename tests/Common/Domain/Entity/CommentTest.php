<?php

declare(strict_types=1);

namespace Tests\Common\Domain\Entity;

use DateTimeImmutable;
use MyEspacio\Common\Domain\Entity\Comment;
use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class CommentTest extends TestCase
{
    /** @param array<string, mixed> $jsonSerialized */
    #[DataProvider('modelDataProvider')]
    public function testModel(
        string $comment,
        DateTimeImmutable $created,
        ?string $title,
        UuidInterface $userUuid,
        string $username,
        array $jsonSerialized,
    ): void {
        $model = new Comment(
            comment: $comment,
            created: $created,
            title: $title,
            userUuid: $userUuid,
            username: $username
        );

        $this->assertSame($comment, $model->getComment());
        $this->assertSame($created, $model->getCreated());
        $this->assertSame($title, $model->getTitle());
        $this->assertSame($userUuid, $model->getUserUuid());
        $this->assertSame($username, $model->getUsername());
        $this->assertEquals($jsonSerialized, $model->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                'comment' => 'Hello',
                'created' => new DateTimeImmutable('2023-12-30 12:13:14'),
                'title' => 'Test comment',
                'userUuid' => Uuid::fromString('39fa7943-6fa7-4412-97c8-c6cec6a44e0b'),
                'username' => 'Mark Bellingham',
                'jsonSerialized' => [
                    'comment' => 'Hello',
                    'created' => '2023-12-30T12:13:14+00:00',
                    'username' => 'Mark Bellingham',
                    'userUuid' => '39fa7943-6fa7-4412-97c8-c6cec6a44e0b'
                ],
            ],
            'test_null' => [
                'comment' => 'Goodbye',
                'created' => new DateTimeImmutable('2025-07-27 20:32:01'),
                'title' => null,
                'userUuid' => Uuid::fromString('db49852b-246b-41ae-9390-215377d8ccfd'),
                'username' => 'Joe Bloggs',
                'jsonSerialized' => [
                    'comment' => 'Goodbye',
                    'created' => '2025-07-27T20:32:01+00:00',
                    'username' => 'Joe Bloggs',
                    'userUuid' => 'db49852b-246b-41ae-9390-215377d8ccfd',
                ],
            ],
        ];
    }

    #[DataProvider('settersDataProvider')]
    public function testSetters(
        string $comment,
        DateTimeImmutable $created,
        UuidInterface $userUuid,
        string $username,
    ): void {
        $model = new Comment(
            comment: 'Hello',
            created: new DateTimeImmutable('2023-12-30 12:13:14'),
            title: null,
            userUuid: Uuid::fromString('39fa7943-6fa7-4412-97c8-c6cec6a44e0b'),
            username: 'Mark Bellingham'
        );
        $this->assertSame('Hello', $model->getComment());
        $this->assertInstanceOf(DateTimeImmutable::class, $model->getCreated());
        $this->assertSame('30-12-2023 @ 12:13', $model->getCreated()->format('d-m-Y @ H:i'));
        $this->assertSame('39fa7943-6fa7-4412-97c8-c6cec6a44e0b', $model->getUserUuid()->toString());
        $this->assertSame('Mark Bellingham', $model->getUsername());

        $model->setComment($comment);
        $model->setCreated($created);
        $model->setUserUuid($userUuid);
        $model->setUsername($username);

        $this->assertSame($comment, $model->getComment());
        $this->assertSame($created, $model->getCreated());
        $this->assertSame($userUuid, $model->getUserUuid());
        $this->assertSame($username, $model->getUsername());
    }

    /** @return array<string, array<string, mixed>> */
    public static function settersDataProvider(): array
    {
        return [
            'test_1' => [
                'comment' => 'Hello',
                'created' => new DateTimeImmutable('2023-12-30 12:13:14'),
                'userUuid' => Uuid::fromString('39fa7943-6fa7-4412-97c8-c6cec6a44e0b'),
                'username' => 'Mark Bellingham',
            ],
            'test_2' => [
                'comment' => 'Goodbye',
                'created' => new DateTimeImmutable('2025-07-27 20:32:01'),
                'userUuid' => Uuid::fromString('db49852b-246b-41ae-9390-215377d8ccfd'),
                'username' => 'Joe Bloggs',
            ],
        ];
    }

    #[DataProvider('createFromDataSetDataProvider')]
    public function testCreateFromDataSet(
        DataSet $dataSet,
        Comment $expectedComment,
    ): void {
        $comment = Comment::createFromDataSet($dataSet);
        $this->assertEquals($expectedComment, $comment);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDataSetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataSet' => new DataSet([
                    'comment' => 'Hello',
                    'created' => '2023-12-30 12:13:14',
                    'title' => null,
                    'user_uuid' => '39fa7943-6fa7-4412-97c8-c6cec6a44e0b',
                    'username' => 'Mark Bellingham'
                ]),
                'expectedComment' => new Comment(
                    comment: 'Hello',
                    created: new DateTimeImmutable('2023-12-30 12:13:14'),
                    title: null,
                    userUuid: Uuid::fromString('39fa7943-6fa7-4412-97c8-c6cec6a44e0b'),
                    username: 'Mark Bellingham'
                ),
            ],
            'test_2' => [
                'dataSet' => new DataSet([
                    'comment' => 'Goodbye',
                    'created' => '2025-07-27 21:28:03',
                    'title' => null,
                    'user_uuid' => '38de882d-e983-49b9-a78f-e8f93c1dea87',
                    'username' => 'Joe Bloggs'
                ]),
                'expectedComment' => new Comment(
                    comment: 'Goodbye',
                    created: new DateTimeImmutable('2025-07-27 21:28:03'),
                    title: null,
                    userUuid: Uuid::fromString('38de882d-e983-49b9-a78f-e8f93c1dea87'),
                    username: 'Joe Bloggs'
                ),
            ],
        ];
    }
}

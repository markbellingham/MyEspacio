<?php

declare(strict_types=1);

namespace Tests\Php\Php\Photos\Domain\Entity;

use DateTimeImmutable;
use MyEspacio\Common\Domain\Entity\Comment;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\PhotoComment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PhotoCommentTest extends TestCase
{
    /** @param array<string, mixed> $jsonSerialized */
    #[DataProvider('modelDataProvider')]
    public function testModel(
        UuidInterface $photoUuid,
        string $comment,
        DateTimeImmutable $created,
        string $title,
        UuidInterface $userUuid,
        string $username,
        array $jsonSerialized,
    ): void {
        $created = new DateTimeImmutable('2024-07-20 16:23:00');

        $photoComment = new PhotoComment(
            $photoUuid,
            $comment,
            $created,
            $title,
            $userUuid,
            $username,
        );

        $this->assertInstanceOf(Comment::class, $photoComment);

        $this->assertSame($photoUuid, $photoComment->getPhotoUuid());
        $this->assertSame($comment, $photoComment->getComment());
        $this->assertSame($created, $photoComment->getCreated());
        $this->assertSame($title, $photoComment->getTitle());
        $this->assertSame($userUuid, $photoComment->getUserUuid());
        $this->assertSame($username, $photoComment->getUsername());
        $this->assertEquals($jsonSerialized, $photoComment->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                'photoUuid' => Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
                'comment' => 'Nice photo!',
                'created' => new DateTimeImmutable('2024-07-20 16:23:00'),
                'title' => 'Some Title',
                'userUuid' => Uuid::fromString('2a8b2a67-867e-4eaf-9102-2cf1cdf691c9'),
                'username' => 'Mark Bellingham',
                'jsonSerialized' => [
                    'comment' => 'Nice photo!',
                    'created' => '2024-07-20T16:23:00+00:00',
                    'username' => 'Mark Bellingham',
                    'photoUuid' => '95e7a3b0-6b8a-41bc-bbe2-4efcea215aea',
                    'userUuid' => '2a8b2a67-867e-4eaf-9102-2cf1cdf691c9',
                ],
            ],
            'test_2' => [
                'photoUuid' => Uuid::fromString('01ed37b2-0367-4dab-8364-0f39581d0523'),
                'comment' => 'Photo nice!',
                'created' => new DateTimeImmutable('2025-08-21 17:24:01'),
                'title' => 'Titlesome',
                'userUuid' => Uuid::fromString('eb4acc3e-410a-4dd2-b214-df318a403e0d'),
                'username' => 'Joe Bloggs',
                'jsonSerialized' => [
                    'comment' => 'Photo nice!',
                    'created' => '2024-07-20T16:23:00+00:00',
                    'username' => 'Joe Bloggs',
                    'photoUuid' => '01ed37b2-0367-4dab-8364-0f39581d0523',
                    'userUuid' => 'eb4acc3e-410a-4dd2-b214-df318a403e0d',
                ],
            ]
        ];
    }

    public function testNullValues(): void
    {
        $created = new DateTimeImmutable('2024-07-20 16:23:00');

        $photoComment = new PhotoComment(
            photoUuid: Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
            comment: 'Nice photo!',
            created: $created,
            title: null,
            userUuid: Uuid::fromString('2a8b2a67-867e-4eaf-9102-2cf1cdf691c9'),
            username: 'Mark Bellingham'
        );

        $this->assertNull($photoComment->getTitle());
    }

    #[DataProvider('createFromDataSetDataProvider')]
    public function testCreateFromDataset(
        DataSet $dataSet,
        PhotoComment $expectedModel,
    ): void {
        $photoComment = PhotoComment::createFromDataSet($dataSet);
        $this->assertEquals($expectedModel, $photoComment);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDataSetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataSet' => new DataSet([
                    'photo_uuid' => '95e7a3b0-6b8a-41bc-bbe2-4efcea215aea',
                    'comment' => 'Nice photo!',
                    'created' => '2024-07-20 16:23:00',
                    'title' => null,
                    'user_uuid' => '2a8b2a67-867e-4eaf-9102-2cf1cdf691c9',
                    'username' => 'Mark Bellingham'
                ]),
                'expectedModel' => new PhotoComment(
                    photoUuid: Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
                    comment: 'Nice photo!',
                    created: new DateTimeImmutable('2024-07-20 16:23:00'),
                    title: null,
                    userUuid: Uuid::fromString('2a8b2a67-867e-4eaf-9102-2cf1cdf691c9'),
                    username: 'Mark Bellingham'
                ),
            ],
            'test_2' => [
                'dataSet' => new DataSet([
                    'photo_uuid' => '01ed37b2-0367-4dab-8364-0f39581d0523',
                    'comment' => 'Photo nice!',
                    'created' => '2025-08-21 17:24:01',
                    'title' => null,
                    'user_uuid' => 'eb4acc3e-410a-4dd2-b214-df318a403e0d',
                    'username' => 'Joe Bloggs'
                ]),
                'expectedModel' => new PhotoComment(
                    photoUuid: Uuid::fromString('01ed37b2-0367-4dab-8364-0f39581d0523'),
                    comment: 'Photo nice!',
                    created: new DateTimeImmutable('2025-08-21 17:24:01'),
                    title: null,
                    userUuid: Uuid::fromString('eb4acc3e-410a-4dd2-b214-df318a403e0d'),
                    username: 'Joe Bloggs'
                ),
            ],
        ];
    }
}

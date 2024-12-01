<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Collection;

use DateTimeImmutable;
use MyEspacio\Framework\Exceptions\CollectionException;
use MyEspacio\Photos\Domain\Collection\PhotoCommentCollection;
use MyEspacio\Photos\Domain\Entity\PhotoComment;
use PHPUnit\Framework\TestCase;

final class PhotoCommentCollectionTest extends TestCase
{
    public function testPhotoCommentCollection(): void
    {
        $created = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-07-20 16:23:00');

        $data = [
            [
                'photo_uuid' => '9d0a6098-8e0e-4caf-9748-175518694fe4',
                'comment' => 'Nice photo!',
                'created' => '2024-07-20 16:23:00',
                'title' => 'Some Title',
                'user_uuid' => '120f05ed-fda7-4a3b-8a4a-bbf9bb6f8211',
                'username' => 'Mark Bellingham'
            ],
            [
                'photo_uuid' => '4fb5fe7d-41de-4b41-a5f9-1897221f4333',
                'comment' => 'Nice photo!',
                'created' => '2024-07-20 16:23:00',
                'title' => null,
                'user_uuid' => '72f997d2-1614-46f1-8396-434042ecd0b3',
                'username' => 'Mark Bellingham'
            ]
        ];

        $collection = new PhotoCommentCollection($data);

        $this->assertInstanceOf(PhotoCommentCollection::class, $collection);
        $this->assertCount(2, $collection);
        foreach ($collection as $comment) {
            $this->assertInstanceOf(PhotoComment::class, $comment);
        }

        $this->assertEquals(
            [
                [
                    'photoUuid' => '9d0a6098-8e0e-4caf-9748-175518694fe4',
                    'comment' => 'Nice photo!',
                    'created' => '2024-07-20T16:23:00+00:00',
                    'userUuid' => '120f05ed-fda7-4a3b-8a4a-bbf9bb6f8211',
                    'username' => 'Mark Bellingham'
                ],
                [
                    'photoUuid' => '4fb5fe7d-41de-4b41-a5f9-1897221f4333',
                    'comment' => 'Nice photo!',
                    'created' => '2024-07-20T16:23:00+00:00',
                    'userUuid' => '72f997d2-1614-46f1-8396-434042ecd0b3',
                    'username' => 'Mark Bellingham'
                ]
            ],
            $collection->jsonSerialize()
        );
    }

    public function testCollectionEmpty(): void
    {
        $collection = new PhotoCommentCollection([]);

        $this->assertInstanceOf(PhotoCommentCollection::class, $collection);
        $this->assertCount(0, $collection);
    }

    public function testMissingRequiredKeys(): void
    {
        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('Missing required keys: comment, created, photo_uuid, title, user_uuid, username');

        $data = [
            [
                'bad_key' => 'bad_value',
            ]
        ];
        new PhotoCommentCollection($data);
    }
}

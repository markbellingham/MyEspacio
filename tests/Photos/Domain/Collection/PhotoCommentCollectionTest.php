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
                'photo_id' => 1,
                'comment' => 'Nice photo!',
                'created' => $created,
                'title' => 'Some Title',
                'user_id' => 2,
                'username' => 'Mark Bellingham'
            ],
            [
                'photo_id' => 1,
                'comment' => 'Nice photo!',
                'created' => null,
                'title' => null,
                'user_id' => 2,
                'username' => 'Mark Bellingham'
            ]
        ];

        $collection = new PhotoCommentCollection($data);

        $this->assertInstanceOf(PhotoCommentCollection::class, $collection);
        $this->assertCount(2, $collection);
        foreach ($collection as $comment) {
            $this->assertInstanceOf(PhotoComment::class, $comment);
        }
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
        $this->expectExceptionMessage('Missing required keys: comment, created, photo_id, title, user_id, username');

        $data = [
            [
                'bad_key' => 'bad_value',
            ]
        ];
        $collection = new PhotoCommentCollection($data);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use DateTimeImmutable;
use MyEspacio\Common\Domain\Entity\Comment;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\PhotoComment;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoCommentTest extends TestCase
{
    public function testPhotoComment(): void
    {
        $created = new DateTimeImmutable('2024-07-20 16:23:00');

        $photoComment = new PhotoComment(
            photoUuid: Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
            comment: 'Nice photo!',
            created: $created,
            title: 'Some Title',
            userUuid: Uuid::fromString('2a8b2a67-867e-4eaf-9102-2cf1cdf691c9'),
            username: 'Mark Bellingham'
        );

        $this->assertInstanceOf(Comment::class, $photoComment);
        $this->assertInstanceOf(PhotoComment::class, $photoComment);

        $this->assertSame('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea', $photoComment->getPhotoUuid()->toString());
        $this->assertSame('Nice photo!', $photoComment->getComment());
        $this->assertEquals($created, $photoComment->getCreated());
        $this->assertSame('Some Title', $photoComment->getTitle());
        $this->assertSame('2a8b2a67-867e-4eaf-9102-2cf1cdf691c9', $photoComment->getUserUuid()->toString());
        $this->assertSame('Mark Bellingham', $photoComment->getUsername());
        $this->assertEquals(
            [
                'comment' => 'Nice photo!',
                'created' => '2024-07-20T16:23:00+00:00',
                'username' => 'Mark Bellingham',
                'photoUuid' => '95e7a3b0-6b8a-41bc-bbe2-4efcea215aea',
                'userUuid' => '2a8b2a67-867e-4eaf-9102-2cf1cdf691c9'
            ],
            $photoComment->jsonSerialize()
        );
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

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'photo_uuid' => '95e7a3b0-6b8a-41bc-bbe2-4efcea215aea',
            'comment' => 'Nice photo!',
            'created' => '2024-07-20 16:23:00',
            'title' => null,
            'user_uuid' => '2a8b2a67-867e-4eaf-9102-2cf1cdf691c9',
            'username' => 'Mark Bellingham'
        ]);

        $photoComment = PhotoComment::createFromDataSet($data);
        $this->assertInstanceOf(PhotoComment::class, $photoComment);
        $this->assertSame('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea', $photoComment->getPhotoUuid()->toString());
        $this->assertSame('Nice photo!', $photoComment->getComment());
        $this->assertInstanceOf(DateTimeImmutable::class, $photoComment->getCreated());
        $this->assertEquals('2024-07-20 16:23:00', $photoComment->getCreated()->format('Y-m-d H:i:s'));
        $this->assertNull($photoComment->getTitle());
        $this->assertSame('2a8b2a67-867e-4eaf-9102-2cf1cdf691c9', $photoComment->getUserUuid()->toString());
        $this->assertSame('Mark Bellingham', $photoComment->getUsername());
    }
}

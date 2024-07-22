<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use DateTimeImmutable;
use MyEspacio\Common\Domain\Entity\Comment;
use MyEspacio\Photos\Domain\Entity\PhotoComment;
use PHPUnit\Framework\TestCase;

final class PhotoCommentTest extends TestCase
{
    public function testPhotoComment(): void
    {
        $created = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-07-20 16:23:00');

        $photoComment = new PhotoComment(
            photoId: 1,
            comment: 'Nice photo!',
            created: $created,
            title: 'Some Title',
            userId: 2,
            username: 'Mark Bellingham'
        );

        $this->assertInstanceOf(Comment::class, $photoComment);
        $this->assertInstanceOf(\MyEspacio\Photos\Domain\Entity\PhotoComment::class, $photoComment);

        $this->assertSame(1, $photoComment->getPhotoId());
        $this->assertSame('Nice photo!', $photoComment->getComment());
        $this->assertEquals($created, $photoComment->getCreated());
        $this->assertSame('Some Title', $photoComment->getTitle());
        $this->assertSame(2, $photoComment->getUserId());
        $this->assertSame('Mark Bellingham', $photoComment->getUsername());
    }
}

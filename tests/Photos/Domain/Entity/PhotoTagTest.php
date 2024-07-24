<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Photos\Domain\Entity\PhotoTag;
use PHPUnit\Framework\TestCase;

final class PhotoTagTest extends TestCase
{
    public function testPhotoTag(): void
    {
        $photoTag = new PhotoTag(
            photoId: 2,
            tag: 'Sunset',
            id:1
        );

        $this->assertInstanceOf(Tag::class, $photoTag);
        $this->assertInstanceOf(\MyEspacio\Photos\Domain\Entity\PhotoTag::class, $photoTag);

        $this->assertSame(2, $photoTag->getPhotoId());
        $this->assertEquals('Sunset', $photoTag->getTag());
        $this->assertSame(1, $photoTag->getId());
    }
}
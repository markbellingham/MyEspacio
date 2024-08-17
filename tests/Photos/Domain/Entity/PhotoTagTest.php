<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Framework\DataSet;
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

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'photo_id' => 23,
            'tag' => 'birds',
            'tag_id' => 45
        ]);
        $photoTag = PhotoTag::createFromDataSet($data);
        $this->assertInstanceOf(PhotoTag::class, $photoTag);
        $this->assertSame(23, $photoTag->getPhotoId());
        $this->assertEquals('birds', $photoTag->getTag());
        $this->assertSame(45, $photoTag->getId());
    }
}

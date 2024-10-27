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
            photoUuid: '98951d80-139a-4745-adc8-2f15cb600fb1',
            tag: 'Sunset',
            id:1
        );

        $this->assertInstanceOf(Tag::class, $photoTag);
        $this->assertInstanceOf(\MyEspacio\Photos\Domain\Entity\PhotoTag::class, $photoTag);

        $this->assertSame('98951d80-139a-4745-adc8-2f15cb600fb1', $photoTag->getPhotoUuid());
        $this->assertEquals('Sunset', $photoTag->getTag());
        $this->assertSame(1, $photoTag->getId());
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'photo_uuid' => '98951d80-139a-4745-adc8-2f15cb600fb1',
            'tag' => 'birds',
            'tag_id' => 45
        ]);
        $photoTag = PhotoTag::createFromDataSet($data);
        $this->assertInstanceOf(PhotoTag::class, $photoTag);
        $this->assertSame('98951d80-139a-4745-adc8-2f15cb600fb1', $photoTag->getPhotoUuid());
        $this->assertEquals('birds', $photoTag->getTag());
        $this->assertSame(45, $photoTag->getId());
    }
}

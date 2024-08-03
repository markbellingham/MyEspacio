<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Collection;

use MyEspacio\Framework\Exceptions\CollectionException;
use MyEspacio\Photos\Domain\Collection\PhotoTagCollection;
use MyEspacio\Photos\Domain\Entity\PhotoTag;
use PHPUnit\Framework\TestCase;

final class PhotoTagCollectionTest extends TestCase
{
    public function testPhotoTagCollection(): void
    {
        $data = [
            [
                'photo_id' => 1,
                'tag' => 'sunset',
                'id' => 1
            ],
            [
                'photo_id' => 1,
                'tag' => 'mexico',
                'id' => 2
            ]
        ];

        $collection = new PhotoTagCollection($data);

        $this->assertCount(2, $collection);
        foreach ($collection as $photoTag) {
            $this->assertInstanceOf(PhotoTag::class, $photoTag);
        }
    }

    public function testCollectionEmpty(): void
    {
        $data = [];

        $collection = new PhotoTagCollection($data);

        $this->assertInstanceOf(PhotoTagCollection::class, $collection);
        $this->assertCount(0, $collection);
    }

    public function testRequiredKeys(): void
    {
        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('Missing required keys: photo_id, tag, id');

        $data = [
            [
                'bad_key' => 'bad value'
            ]
        ];

        $collection = new PhotoTagCollection($data);
    }
}

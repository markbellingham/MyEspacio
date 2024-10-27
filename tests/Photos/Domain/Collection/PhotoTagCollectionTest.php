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
                'photo_uuid' => '72f997d2-1614-46f1-8396-434042ecd0b3',
                'tag' => 'sunset',
                'id' => 1
            ],
            [
                'photo_uuid' => 'f133fede-65f5-4b68-aded-f8f0e9bfe3bb',
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
        $this->expectExceptionMessage('Missing required keys: photo_uuid, tag, id');

        $data = [
            [
                'bad_key' => 'bad value'
            ]
        ];

        $collection = new PhotoTagCollection($data);
    }
}

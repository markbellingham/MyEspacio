<?php

declare(strict_types=1);

namespace Photos\Domain\Collection;

use MyEspacio\Framework\Exceptions\CollectionException;
use MyEspacio\Photos\Domain\Collection\PhotoAlbumCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use PHPUnit\Framework\TestCase;

final class PhotoAlbumCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $data = [
            [
                'album_id' => '4',
                'album_uuid' => '78eda1f2-a6f8-48d8-af30-3907f5f9e534',
                'description' => null,
                'title' => 'The Red Fort, Delhi',
                'country_id' => '102',
                'country_name' => 'India',
                'three_char_code' => 'IND',
                'two_char_code' => 'IN'
            ],
            [
                'album_id' => '5',
                'album_uuid' => '4b9d0175-6d47-4460-b48b-6385db446a30',
                'description' => null,
                'title' => 'Qutab Minar, Delhi',
                'country_id' => '102',
                'country_name' => 'India',
                'three_char_code' => 'IND',
                'two_char_code' => 'IN'
            ],
            [
                'album_id' => '7',
                'album_uuid' => 'adf36769-8983-448d-b3ad-0ab1e5edb9c5',
                'description' => null,
                'title' => 'Mumbai',
                'country_id' => '102',
                'country_name' => 'India',
                'three_char_code' => 'IND',
                'two_char_code' => 'IN'
            ]
        ];

        $collection = new PhotoAlbumCollection($data);
        $this->assertCount(3, $collection);
        foreach ($collection as $album) {
            $this->assertInstanceOf(PhotoAlbum::class, $album);
        }
        $this->assertEquals(
            [
                [
                    'country' => [
                        'name' => 'India',
                        'threeCharCode' => 'IND',
                        'twoCharCode' => 'IN'
                    ],
                    'album_uuid' => '78eda1f2-a6f8-48d8-af30-3907f5f9e534',
                    'description' => null,
                    'title' => 'The Red Fort, Delhi',
                    'photos' => []
                ],
                [
                    'country' => [
                        'name' => 'India',
                        'threeCharCode' => 'IND',
                        'twoCharCode' => 'IN'
                    ],
                    'album_uuid' => '4b9d0175-6d47-4460-b48b-6385db446a30',
                    'description' => null,
                    'title' => 'Qutab Minar, Delhi',
                    'photos' => []
                ],
                [
                    'country' => [
                        'name' => 'India',
                        'threeCharCode' => 'IND',
                        'twoCharCode' => 'IN'
                    ],
                    'album_uuid' => 'adf36769-8983-448d-b3ad-0ab1e5edb9c5',
                    'description' => null,
                    'title' => 'Mumbai',
                    'photos' => []
                ]
            ],
            $collection->jsonSerialize()
        );
    }

    public function testCollectionEmpty(): void
    {
        $data = [];
        $collection = new PhotoAlbumCollection($data);
        $this->assertCount(0, $collection);
    }

    public function testRequiredKeys(): void
    {
        $data = [
            [
                [
                    'album_id' => '7',
                    'album_uuid' => '9d0a6098-8e0e-4caf-9748-175518694fe4',
                    'description' => null,
                    'title' => 'Mumbai',
                    'country_id' => '102',
                    'country_name' => 'India',
                    'three_char_code' => 'IND',
                    'two_char_code' => 'IN'
                ],
                [
                    'bad_key' => 'Bad Value'
                ]
            ]
        ];

        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('Missing required keys: album_id, album_uuid, description, title, country_id, country_name, three_char_code, two_char_code');

        new PhotoAlbumCollection($data);
    }
}

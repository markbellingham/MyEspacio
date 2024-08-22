<?php

declare(strict_types=1);

namespace Photos\Domain\Collection;

use MyEspacio\Framework\Exceptions\CollectionException;
use MyEspacio\Photos\Domain\Collection\PhotoAlbumCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use PHPUnit\Framework\TestCase;

class PhotoAlbumCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $data = [
            [
                'album_id' => '4',
                'description' => null,
                'title' => 'The Red Fort, Delhi',
                'country_id' => '102',
                'country_name' => 'India',
                'three_char_code' => 'IND',
                'two_char_code' => 'IN'
            ],
            [
                'album_id' => '5',
                'description' => null,
                'title' => 'Qutab Minar, Delhi',
                'country_id' => '102',
                'country_name' => 'India',
                'three_char_code' => 'IND',
                'two_char_code' => 'IN'
            ],
            [
                'album_id' => '7',
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
        $this->expectExceptionMessage('Missing required keys: album_id, description, title, country_id, country_name, three_char_code, two_char_code');

        new PhotoAlbumCollection($data);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PhotoAlbumTest extends TestCase
{
    /** @param array<string, mixed> $jsonSerialized */
    #[DataProvider('photoAlbumDataProvider')]
    public function testPhotoAlbum(
        string $title,
        int $albumId,
        ?UuidInterface $uuid,
        ?string $description,
        Country $country,
        ?PhotoCollection $photos,
        array $jsonSerialized,
    ): void {
        $photoAlbum = new PhotoAlbum(
            $title,
            $albumId,
            $uuid,
            $description,
            $country,
            $photos
        );

        $this->assertSame($title, $photoAlbum->getTitle());
        $this->assertSame($albumId, $photoAlbum->getAlbumId());
        $this->assertSame($uuid, $photoAlbum->getUuid());
        $this->assertSame($description, $photoAlbum->getDescription());
        $this->assertSame($country, $photoAlbum->getCountry());

        $this->assertEquals($jsonSerialized, $photoAlbum->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function photoAlbumDataProvider(): array
    {
        return [
            'test_1' => [
                'title' => 'MyAlbum',
                'albumId' => 1,
                'uuid' => Uuid::fromString('78eda1f2-a6f8-48d8-af30-3907f5f9e534'),
                'description' => 'My favourite photos',
                'country' => new Country(
                    id: 1,
                    name: 'United Kingdom',
                    twoCharCode: 'GB',
                    threeCharCode: 'GBR'
                ),
                'photos' => new PhotoCollection([
                    [
                        'country_id' => '45',
                        'country_name' => 'Chile',
                        'two_char_code' => 'CL',
                        'three_char_code' => 'CHL',
                        'geo_id' => '2559',
                        'photo_uuid' => '9d0a6098-8e0e-4caf-9748-175518694fe4',
                        'latitude' => '-33438084',
                        'longitude' => '-33438084',
                        'accuracy' =>  '16',
                        'width' => '456',
                        'height' => '123',
                        'date_taken' => "2012-10-21",
                        'description' => "Note the spurs...",
                        'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                        'filename' => "P1070237.JPG",
                        'photo_id' => '2689',
                        'title' => "Getting ready to dance",
                        'town' => "Valparaiso",
                        'comment_count' => '1',
                        'fave_count' => '1'
                    ]
                ]),
                'jsonSerialized' => [
                    'title' => 'MyAlbum',
                    'album_uuid' => '78eda1f2-a6f8-48d8-af30-3907f5f9e534',
                    'description' => 'My favourite photos',
                    'country' => [
                        'name' => 'United Kingdom',
                        'twoCharCode' => 'GB',
                        'threeCharCode' => 'GBR'
                    ],
                    'photos' => [
                        [
                            'country' => [
                                'name' => 'Chile',
                                'twoCharCode' => 'CL',
                                'threeCharCode' => 'CHL',
                            ],
                            'geoCoordinates' => [
                                'latitude' => -33438084,
                                'longitude' => -33438084,
                                'accuracy' =>  16,
                            ],
                            'dimensions' => [
                                'width' => 456,
                                'height' => 123,
                            ],
                            'relevance' => [
                                'cScore' => 0,
                                'pScore' => 0
                            ],
                            'photoUuid' => '9d0a6098-8e0e-4caf-9748-175518694fe4',
                            'dateTaken' => "2012-10-21T00:00:00+00:00",
                            'description' => "Note the spurs...",
                            'title' => "Getting ready to dance",
                            'town' => "Valparaiso",
                            'commentCount' => 1,
                            'faveCount' => 1
                        ]
                    ],
                ],
            ],
            'test_2' => [
                'title' => 'TestAlbum',
                'albumId' => 2,
                'uuid' => Uuid::fromString('b97ea1e9-9bf9-4c1b-8a99-a30a8c6725e9'),
                'description' => 'Testing photo album',
                'country' => new Country(
                    id: 2,
                    name: 'France',
                    twoCharCode: 'FR',
                    threeCharCode: 'FRA'
                ),
                'photos' => new PhotoCollection([
                    [
                        'country_id' => '2',
                        'country_name' => 'France',
                        'two_char_code' => 'FR',
                        'three_char_code' => 'FRA',
                        'geo_id' => '1234',
                        'photo_uuid' => 'a7ec2ac2-343f-40ee-94bf-50672c509db2',
                        'latitude' => '32240065',
                        'longitude' => '77187860',
                        'accuracy' =>  '16',
                        'width' => '987',
                        'height' => '654',
                        'date_taken' => "2013-11-12",
                        'description' => "Random photo!",
                        'directory' => "RTW Trip\/18France\/10 - Paris",
                        'filename' => "P1078612.JPG",
                        'photo_id' => '1313',
                        'title' => "outdoor terrace",
                        'town' => "Paris",
                        'comment_count' => '2',
                        'fave_count' => '2'
                    ]
                ]),
                'jsonSerialized' => [
                    'title' => 'TestAlbum',
                    'album_uuid' => 'b97ea1e9-9bf9-4c1b-8a99-a30a8c6725e9',
                    'description' => 'Testing photo album',
                    'country' => [
                        'name' => 'France',
                        'twoCharCode' => 'FR',
                        'threeCharCode' => 'FRA'
                    ],
                    'photos' => [
                        [
                            'country' => [
                                'name' => 'France',
                                'twoCharCode' => 'FR',
                                'threeCharCode' => 'FRA',
                            ],
                            'geoCoordinates' => [
                                'latitude' => 32240065,
                                'longitude' => 77187860,
                                'accuracy' =>  16,
                            ],
                            'dimensions' => [
                                'width' => 987,
                                'height' => 654,
                            ],
                            'relevance' => [
                                'cScore' => 0,
                                'pScore' => 0
                            ],
                            'photoUuid' => 'a7ec2ac2-343f-40ee-94bf-50672c509db2',
                            'dateTaken' => "2013-11-12T00:00:00+00:00",
                            'description' => "Random photo!",
                            'title' => "outdoor terrace",
                            'town' => "Paris",
                            'commentCount' => 2,
                            'faveCount' => 2
                        ]
                    ],
                ],
            ]
        ];
    }

    public function testDefaultValues(): void
    {
        $photoAlbum = new PhotoAlbum();

        $this->assertEquals('Unassigned', $photoAlbum->getTitle());
        $this->assertNull($photoAlbum->getAlbumId());
        $this->assertNull($photoAlbum->getUuid());
        $this->assertNull($photoAlbum->getDescription());
        $this->assertNull($photoAlbum->getCountry());
        $this->assertInstanceOf(PhotoCollection::class, $photoAlbum->getPhotos());
    }

    public function testSetters(): void
    {
        $photoAlbum = new PhotoAlbum();

        $this->assertSame('Unassigned', $photoAlbum->getTitle());
        $this->assertNull($photoAlbum->getAlbumId());
        $this->assertNull($photoAlbum->getUuid());
        $this->assertNull($photoAlbum->getDescription());
        $this->assertNull($photoAlbum->getCountry());

        $photoAlbum->setTitle('Yadda Yadda');
        $photoAlbum->setAlbumId(1);
        $photoAlbum->setUuid(Uuid::fromString('4b9d0175-6d47-4460-b48b-6385db446a30'));
        $photoAlbum->setDescription('My favourite photos');
        $photoAlbum->setCountry(new Country(
            id: 1,
            name: 'United Kingdom',
            twoCharCode: 'GB',
            threeCharCode: 'GBR'
        ));

        $this->assertSame('Yadda Yadda', $photoAlbum->getTitle());
        $this->assertSame(1, $photoAlbum->getAlbumId());
        $this->assertEquals(Uuid::fromString('4b9d0175-6d47-4460-b48b-6385db446a30'), $photoAlbum->getUuid());
        $this->assertSame('My favourite photos', $photoAlbum->getDescription());
        $this->assertInstanceOf(Country::class, $photoAlbum->getCountry());
    }

    public function testAlbumPhotos(): void
    {
        $photoAlbum = new PhotoAlbum(
            title: 'MyAlbum',
            albumId: 1,
            description: 'My favourite photos'
        );

        $this->assertInstanceOf(PhotoCollection::class, $photoAlbum->getPhotos());
        $this->assertCount(0, $photoAlbum->getPhotos());

        $photos = new PhotoCollection([
            [
                'country_id' => '45',
                'country_name' => 'Chile',
                'two_char_code' => 'CL',
                'three_char_code' => 'CHL',
                'geo_id' => '2559',
                'latitude' => '-33438084',
                'longitude' => '-33438084',
                'accuracy' =>  '16',
                'width' => '456',
                'height' => '123',
                'date_taken' => "2012-10-21",
                'description' => "Note the spurs...",
                'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                'filename' => "P1070237.JPG",
                'photo_id' => '2689',
                'photo_uuid' => 'f133fede-65f5-4b68-aded-f8f0e9bfe3bb',
                'title' => "Getting ready to dance",
                'town' => "Valparaiso",
                'comment_count' => '1',
                'fave_count' => '1',
            ]
        ]);

        $this->assertInstanceOf(PhotoCollection::class, $photos);
        $photoAlbum->setPhotos($photos);

        $this->assertCount(1, $photoAlbum->getPhotos());
        $this->assertEquals($photos, $photoAlbum->getPhotos());
    }
}

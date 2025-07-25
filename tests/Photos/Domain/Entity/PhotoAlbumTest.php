<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoAlbumTest extends TestCase
{
    public function testPhotoAlbum(): void
    {
        $photoAlbum = new PhotoAlbum(
            title: 'MyAlbum',
            albumId: 1,
            uuid: Uuid::fromString('78eda1f2-a6f8-48d8-af30-3907f5f9e534'),
            description: 'My favourite photos',
            country: new Country(
                id: 1,
                name: 'United Kingdom',
                twoCharCode: 'GB',
                threeCharCode: 'GBR'
            )
        );

        $this->assertSame('MyAlbum', $photoAlbum->getTitle());
        $this->assertSame(1, $photoAlbum->getAlbumId());
        $this->assertEquals('78eda1f2-a6f8-48d8-af30-3907f5f9e534', $photoAlbum->getUuid()?->toString());
        $this->assertSame('My favourite photos', $photoAlbum->getDescription());
        $this->assertInstanceOf(Country::class, $photoAlbum->getCountry());
        $this->assertEquals('United Kingdom', $photoAlbum->getCountry()->getName());

        $this->assertEquals(
            [
                'title' => 'MyAlbum',
                'album_uuid' => '78eda1f2-a6f8-48d8-af30-3907f5f9e534',
                'description' => 'My favourite photos',
                'country' => [
                    'name' => 'United Kingdom',
                    'twoCharCode' => 'GB',
                    'threeCharCode' => 'GBR'
                ],
                'photos' => []
            ],
            $photoAlbum->jsonSerialize()
        );
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

        $this->assertEquals('Unassigned', $photoAlbum->getTitle());
        $this->assertNull($photoAlbum->getAlbumId());
        $this->assertEquals('', $photoAlbum->getUuid());
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

        $this->assertEquals('Yadda Yadda', $photoAlbum->getTitle());
        $this->assertSame(1, $photoAlbum->getAlbumId());
        $this->assertEquals('4b9d0175-6d47-4460-b48b-6385db446a30', $photoAlbum->getUuid()?->toString());
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
    }
}

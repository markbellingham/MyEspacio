<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use PHPUnit\Framework\TestCase;

final class PhotoAlbumTest extends TestCase
{
    public function testPhotoAlbum(): void
    {
        $photoAlbum = new PhotoAlbum(
            title: 'MyAlbum',
            albumId: 1,
            description: 'My favourite photos'
        );

        $this->assertSame('MyAlbum', $photoAlbum->getTitle());
        $this->assertSame(1, $photoAlbum->getAlbumId());
        $this->assertSame('My favourite photos', $photoAlbum->getDescription());
    }

    public function testDefaultValues(): void
    {
        $photoAlbum = new PhotoAlbum();

        $this->assertEquals('Unassigned', $photoAlbum->getTitle());
        $this->assertSame(0, $photoAlbum->getAlbumId());
        $this->assertNull($photoAlbum->getDescription());
    }

    public function testSetters(): void
    {
        $photoAlbum = new PhotoAlbum();

        $this->assertEquals('Unassigned', $photoAlbum->getTitle());
        $this->assertSame(0, $photoAlbum->getAlbumId());
        $this->assertNull($photoAlbum->getDescription());

        $photoAlbum->setTitle('Yadda Yadda');
        $photoAlbum->setAlbumId(1);
        $photoAlbum->setDescription('My favourite photos');

        $this->assertEquals('Yadda Yadda', $photoAlbum->getTitle());
        $this->assertSame(1, $photoAlbum->getAlbumId());
        $this->assertSame('My favourite photos', $photoAlbum->getDescription());
    }

    public function testCollection(): void
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
                'title' => "Getting ready to dance",
                'town' => "Valparaiso",
                'comment_count' => '1',
                'fave_count' => '1'
            ]
        ]);

        $this->assertInstanceOf(PhotoCollection::class, $photos);
        $photoAlbum->setPhotos($photos);

        $this->assertCount(1, $photoAlbum->getPhotos());
    }
}

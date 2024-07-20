<?php

declare(strict_types=1);

namespace Tests\Photos\Domain;

use MyEspacio\Photos\Domain\PhotoAlbum;
use PHPUnit\Framework\TestCase;

final class PhotoAlbumTest extends TestCase
{
    public function testPhotoAlbum(): void
    {
        $photoAlbum = new PhotoAlbum(
            photoId: 1,
            title: 'MyAlbum',
            albumId: 1,
            description: 'My favourite photos'
        );

        $this->assertSame(1, $photoAlbum->getPhotoId());
        $this->assertSame('MyAlbum', $photoAlbum->getTitle());
        $this->assertSame(1, $photoAlbum->getAlbumId());
        $this->assertSame('My favourite photos', $photoAlbum->getDescription());
    }

    public function testDefaultValues(): void
    {
        $photoAlbum = new PhotoAlbum(
            photoId: 1,
            title: 'MyAlbum'
        );

        $this->assertNull($photoAlbum->getAlbumId());
        $this->assertNull($photoAlbum->getDescription());
    }

    public function testSetters(): void
    {
        $photoAlbum = new PhotoAlbum(
            photoId: 1,
            title: 'MyAlbum'
        );

        $this->assertNull($photoAlbum->getAlbumId());
        $this->assertNull($photoAlbum->getDescription());

        $photoAlbum->setAlbumId(1);
        $photoAlbum->setDescription('My favourite photos');

        $this->assertSame(1, $photoAlbum->getAlbumId());
        $this->assertSame('My favourite photos', $photoAlbum->getDescription());
    }
}

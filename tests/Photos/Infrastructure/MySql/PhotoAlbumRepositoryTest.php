<?php

declare(strict_types=1);

namespace Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoAlbumCollection;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Infrastructure\MySql\PhotoAlbumRepository;
use PHPUnit\Framework\TestCase;

class PhotoAlbumRepositoryTest extends TestCase
{
    public function testFetchById(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id
            WHERE albums.album_id = :albumId',
                [
                    'albumId' => 4
                ]
            )
            ->willReturn(
                [
                    'album_id' => '4',
                    'description' => null,
                    'title' => 'The Red Fort, Delhi',
                    'country_id' => '102',
                    'country_name' => 'India',
                    'three_char_code' => 'IND',
                    'two_char_code' => 'IN'
                ]
            );

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchById(4);

        $this->assertInstanceOf(PhotoAlbum::class, $result);
        $this->assertSame(4, $result->getAlbumId());
    }

    public function testFetchByIdNotFound(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id
            WHERE albums.album_id = :albumId',
                [
                    'albumId' => 4
                ]
            )
            ->willReturn(null);

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchById(4);
        $this->assertNull($result);
    }

    public function testFetchAll(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id',
                []
            )
            ->willReturn(
                [
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
                ]
            );

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchAll();

        $this->assertInstanceOf(PhotoAlbumCollection::class, $result);
        $this->assertCount(3, $result);
    }

    public function testFetchAllDatabaseError(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id',
                []
            )
            ->willReturn([]);

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchAll();

        $this->assertInstanceOf(PhotoAlbumCollection::class, $result);
        $this->assertCount(0, $result);
    }

    public function testFetchAlbumPhotos(): void
    {
        $photoAlbum = new PhotoAlbum(
            title: 'MyAlbum',
            albumId: 1,
            description: 'My favourite photos',
            country: new Country(
                id: 1,
                name: 'United Kingdom',
                twoCharCode: 'GB',
                threeCharCode: 'GBR'
            )
        );

        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT 
                photos.id AS photo_id,
                photos.date_taken,
                photos.description,
                photos.directory,
                photos.filename,
                photos.title,
                photos.town,
                photos.height,
                photos.width,
                countries.id AS country_id,
                countries.name AS country_name,
                countries.two_char_code,
                countries.three_char_code,
                geo.id AS geo_id,
                geo.accuracy,
                geo.latitude,
                geo.longitude,
                COUNT(photo_comments.id) AS comment_count,
                COUNT(photo_faves.photo_id) AS fave_count
            FROM pictures.photos
            LEFT JOIN pictures.countries ON countries.Id = photos.country
            LEFT JOIN pictures.geo ON photos.id = geo.photo_id
            LEFT JOIN pictures.photo_comments ON photos.id = photo_comments.photo_id
            LEFT JOIN pictures.photo_faves ON photos.id = photo_faves.photo_id
            LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id WHERE photo_album.album_id = :albumId',
                [
                    'albumId' => 1
                ]
            )
            ->willReturn(
                [
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
                ]
            );

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchAlbumPhotos($photoAlbum);

        $this->assertInstanceOf(PhotoCollection::class, $result);
        $this->assertCount(1, $result);
    }

    public function testFetchAlbumPhotosNoneFound(): void
    {
        $photoAlbum = new PhotoAlbum(
            title: 'MyAlbum',
            albumId: 1,
            description: 'My favourite photos',
            country: new Country(
                id: 1,
                name: 'United Kingdom',
                twoCharCode: 'GB',
                threeCharCode: 'GBR'
            )
        );

        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT 
                photos.id AS photo_id,
                photos.date_taken,
                photos.description,
                photos.directory,
                photos.filename,
                photos.title,
                photos.town,
                photos.height,
                photos.width,
                countries.id AS country_id,
                countries.name AS country_name,
                countries.two_char_code,
                countries.three_char_code,
                geo.id AS geo_id,
                geo.accuracy,
                geo.latitude,
                geo.longitude,
                COUNT(photo_comments.id) AS comment_count,
                COUNT(photo_faves.photo_id) AS fave_count
            FROM pictures.photos
            LEFT JOIN pictures.countries ON countries.Id = photos.country
            LEFT JOIN pictures.geo ON photos.id = geo.photo_id
            LEFT JOIN pictures.photo_comments ON photos.id = photo_comments.photo_id
            LEFT JOIN pictures.photo_faves ON photos.id = photo_faves.photo_id
            LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id WHERE photo_album.album_id = :albumId',
                [
                    'albumId' => 1
                ]
            )
            ->willReturn([]);

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchAlbumPhotos($photoAlbum);

        $this->assertInstanceOf(PhotoCollection::class, $result);
        $this->assertCount(0, $result);
    }
}

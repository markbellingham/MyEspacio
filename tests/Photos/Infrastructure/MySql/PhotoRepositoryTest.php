<?php

declare(strict_types=1);

namespace Tests\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Infrastructure\MySql\PhotoRepository;
use PHPUnit\Framework\TestCase;

final class PhotoRepositoryTest extends TestCase
{
    public function testFindOne(): void
    {
        $db = $this->createMock(Connection::class);

        $db->expects($this->once())
            ->method('fetchOne')
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
                countries.two_char_country_code,
                countries.three_char_country_code,
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
            LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id
            WHERE photos.id = :photoId
            GROUP BY photos.id',
                [
                    'photoId' => 1
                ]
            )
            ->willReturn(
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
            );

        $repository = new PhotoRepository($db);
        $result = $repository->findOne(1);

        $this->assertInstanceOf(Photo::class, $result);
    }

    public function testFindOneNotFound(): void
    {
        $db = $this->createMock(Connection::class);

        $db->expects($this->once())
            ->method('fetchOne')
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
                countries.two_char_country_code,
                countries.three_char_country_code,
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
            LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id
            WHERE photos.id = :photoId
            GROUP BY photos.id',
                [
                    'photoId' => 1
                ]
            )
            ->willReturn(null);

        $repository = new PhotoRepository($db);
        $result = $repository->findOne(1);

        $this->assertNull($result);
    }
}

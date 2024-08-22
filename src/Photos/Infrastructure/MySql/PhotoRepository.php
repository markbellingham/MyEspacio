<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Application\PhotoBuilder;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;

final class PhotoRepository implements PhotoRepositoryInterface
{
    public const PHOTO_PROPERTIES = 'SELECT 
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
            LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id';

    public function __construct(
        private readonly Connection $db
    ) {
    }

    public function findOne(int $photoId): ?Photo
    {
        $result = $this->db->fetchOne(
            self::PHOTO_PROPERTIES .
            ' WHERE photos.id = :photoId
            GROUP BY photos.id',
            [
                'photoId' => $photoId
            ]
        );

        if ($result === null) {
            return null;
        }

        $dataset = new DataSet($result);
        return (new PhotoBuilder($dataset))->build();
    }

    public function fetchAlbumPhotos(int $albumId): PhotoCollection
    {
        $results = $this->db->fetchAll(
            self::PHOTO_PROPERTIES . ' WHERE photo_album.album_id = :albumId',
            [
                'albumId' => $albumId
            ]
        );
        return new PhotoCollection($results);
    }
}

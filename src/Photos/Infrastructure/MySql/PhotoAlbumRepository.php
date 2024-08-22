<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Collection\PhotoAlbumCollection;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;

class PhotoAlbumRepository implements PhotoAlbumRepositoryInterface
{
    public function __construct(
        private readonly Connection $db
    ) {
    }

    public function fetchById(int $albumId): ?PhotoAlbum
    {
        $result = $this->db->fetchOne(
            'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id
            WHERE albums.album_id = :albumId',
            [
                'albumId' => $albumId
            ]
        );
        if ($result === null) {
            return null;
        }
        return PhotoAlbum::createFromDataSet(
            new DataSet($result)
        );
    }

    public function fetchAll(): PhotoAlbumCollection
    {
        $result = $this->db->fetchAll(
            'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id',
            []
        );

        return new PhotoAlbumCollection($result);
    }

    public function fetchAlbumPhotos(PhotoAlbum $album): PhotoCollection
    {
        $result = $this->db->fetchAll(
            PhotoRepository::PHOTO_PROPERTIES . ' WHERE photo_album.album_id = :albumId',
            [
                'albumId' => $album->getAlbumId()
            ]
        );

        return new PhotoCollection($result);
    }
}

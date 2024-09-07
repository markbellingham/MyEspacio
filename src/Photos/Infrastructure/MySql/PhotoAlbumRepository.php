<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Collection\PhotoAlbumCollection;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;
use MyEspacio\Photos\Infrastructure\MySql\Queries\QueryService;

class PhotoAlbumRepository implements PhotoAlbumRepositoryInterface
{
    private const MY_FAVOURITES = 2;

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
            QueryService::PHOTO_PROPERTIES .
            ' WHERE photo_album.album_id = :albumId',
            [
                'albumId' => $album->getAlbumId()
            ]
        );

        return new PhotoCollection($result);
    }

    public function searchAlbumPhotos(PhotoAlbum $album, array $searchTerms): PhotoCollection
    {
        $queryString = QueryService::prepareSearchTerms($searchTerms);
        if ($queryString === null) {
            return new PhotoCollection([]);
        }
        $results = $this->db->fetchAll(
            QueryService::PHOTO_MATCH_PROPERTIES .
            ' WHERE album.album_id = :albumId AND 
            MATCH (photo.title, photo.description photo.town AGAINST (:searchTerm) > 0',
            [
                'albumId' => $album->getAlbumId(),
                'searchTerm' => $queryString
            ]
        );
        return new PhotoCollection($results);
    }

    public function albumExists(string $albumName): ?int
    {
        $result = $this->db->fetchOne(
            'SELECT album_id FROM pictures.albums WHERE LOWER(title) LIKE :albumName',
            [
                'albumName' => '%' . strtolower($albumName) . '%'
            ]
        );
        if ($result) {
            return (int) $result['album_id'];
        }
        return null;
    }

    public function fetchMyFavourites(): PhotoAlbum
    {
        $album = $this->fetchById(self::MY_FAVOURITES);
        $album->setPhotos(
            $this->fetchAlbumPhotos($album)
        );
        return $album;
    }
}

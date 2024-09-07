<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Application\PhotoBuilder;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use MyEspacio\Photos\Infrastructure\MySql\Queries\QueryService;

final class PhotoRepository implements PhotoRepositoryInterface
{
    private const MY_FAVOURITES = 2;

    public function __construct(
        private readonly Connection $db
    ) {
    }

    public function fetchById(int $photoId): ?Photo
    {
        $result = $this->db->fetchOne(
            QueryService::PHOTO_PROPERTIES .
            ' WHERE photos.id = :photoId',
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

    public function topPhotos(): PhotoCollection
    {
        $result = $this->db->fetchAll(
            QueryService::PHOTO_PROPERTIES .
            'ORDER BY (comment_count + fave_count)
            LIMIT 100',
            []
        );
        return new PhotoCollection($result);
    }

    public function randomSelection(): PhotoCollection
    {
        $result = $this->db->fetchAll(
            QueryService::PHOTO_PROPERTIES .
            ' WHERE photo_album.album_id = :albumId
            ORDER BY RAND()
            LIMIT 100',
            [
                'albumId' => self::MY_FAVOURITES
            ]
        );
        return new PhotoCollection($result);
    }

    public function searchAllPhotos(array $searchTerms): PhotoCollection
    {
        $results = $this->db->fetchAll(
            QueryService::PHOTO_MATCH_PROPERTIES .
            'WHERE MATCH (photos.title, photos.description, photos.town) AGAINST (:searchTerm IN BOOLEAN MODE)',
            [

            ]
        );
        return new PhotoCollection($results);
    }
}

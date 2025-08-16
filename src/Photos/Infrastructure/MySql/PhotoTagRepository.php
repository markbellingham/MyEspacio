<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoTagCollection;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Repository\PhotoTagRepositoryInterface;

final readonly class PhotoTagRepository implements PhotoTagRepositoryInterface
{
    public function __construct(
        private Connection $db
    ) {
    }

    public function fetchForPhoto(Photo $photo): PhotoTagCollection
    {
        $results = $this->db->fetchAll(
            'SELECT
                tags.id,
                tags.tag,
                photo_tags.photo_id
            FROM project.tags
            LEFT JOIN pictures.photo_tags ON tags.id = photo_tags.tag_id
            WHERE photo_tags.photo_id = :photoId',
            [
                'photoId' => $photo->getId()
            ]
        );

        return new PhotoTagCollection($results);
    }
}

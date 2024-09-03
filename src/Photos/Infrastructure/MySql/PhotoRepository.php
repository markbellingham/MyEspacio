<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Application\PhotoBuilder;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use MyEspacio\Photos\Infrastructure\MySql\Queries\PhotoQueryService;

final class PhotoRepository implements PhotoRepositoryInterface
{
    public function __construct(
        private readonly Connection $db
    ) {
    }

    public function fetchById(int $photoId): ?Photo
    {
        $result = $this->db->fetchOne(
            PhotoQueryService::PHOTO_PROPERTIES .
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
}

<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoFave;
use MyEspacio\Photos\Domain\Repository\PhotoFaveRepositoryInterface;

final readonly class PhotoFaveRepository implements PhotoFaveRepositoryInterface
{
    public function __construct(
        private Connection $db
    ) {
    }

    public function add(PhotoFave $fave): bool
    {
        $statement = $this->db->run(
            'INSERT INTO pictures.photo_faves (user_id, photo_id) VALUES (:userId, :photoId)',
            [
                'userId' => $fave->getUser()->getId(),
                'photoId' => $fave->getPhoto()->getId()
            ]
        );
        return $this->db->statementHasErrors($statement) === false;
    }

    public function addAnonymous(PhotoFave $fave): bool
    {
        $statement = $this->db->run(
            'INSERT INTO pictures.anon_photo_faves (photo_id) VALUES (:photoId)',
            [
                'photoId' => $fave->getPhoto()->getId()
            ]
        );
        return $this->db->statementHasErrors($statement) === false;
    }

    public function getPhotoFaveCount(Photo $photo): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(photo_id) AS quantity FROM pictures.photo_faves WHERE photo_id = :photoId',
            [
                'photoId' => $photo->getId()
            ]
        );
        $quantity = $result['quantity'] ?? 0;
        if (is_numeric($quantity)) {
            return (int) $quantity;
        }
        return 0;
    }

    public function getAnonymousFaveCount(Photo $photo): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(photo_id) AS quantity FROM pictures.anon_photo_faves WHERE photo_id = :photoId',
            [
                'photoId' => $photo->getId()
            ]
        );
        $quantity = $result['quantity'] ?? 0;
        if (is_numeric($quantity)) {
            return (int) $quantity;
        }
        return 0;
    }
}

<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoFave;
use MyEspacio\Photos\Domain\Repository\PhotoFaveRepositoryInterface;

final readonly class PhotoFaveRepository implements PhotoFaveRepositoryInterface
{
    private const int ANONYMOUSE_USER_ID = 1;

    public function __construct(
        private Connection $db
    ) {
    }

    public function save(PhotoFave $fave): bool
    {
        if ($fave->getUser()->getId() === self::ANONYMOUSE_USER_ID) {
            return $this->addAnonymous($fave);
        }
        return $this->add($fave);
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

    public function countForPhoto(Photo $photo): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) AS quantity
            FROM (
                SELECT photo_id
                FROM pictures.photo_faves 
                WHERE photo_id = :photoId
                UNION ALL
                SELECT photo_id
                FROM pictures.anon_photo_faves
                WHERE photo_id = :photoId
            ) AS combined_faves',
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

<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoCommentCollection;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoComment;
use MyEspacio\Photos\Domain\Repository\PhotoCommentRepositoryInterface;

final readonly class PhotoCommentRepository implements PhotoCommentRepositoryInterface
{
    public function __construct(
        private Connection $db
    ) {
    }

    public function fetchCount(Photo $photo): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) AS comment_count 
            FROM pictures.photo_comments 
            WHERE photo_id = :photoId',
            [
                'photoId' => $photo->getId(),
            ]
        );
        $count = $result['comment_count'] ?? 0;
        if (is_numeric($count)) {
            return (int) $count;
        }
        return 0;
    }

    public function save(PhotoComment $comment): bool
    {
        $stmt = $this->db->run(
            'INSERT INTO pictures.photo_comments (user_id, photo_id, comment, created)
          SELECT users.id, photos.id, :comment, :created
          FROM project.users
          JOIN pictures.photos
          WHERE users.uuid = :userUuid
          AND photos.uuid = :photoUuid',
            [
                'comment' => $comment->getComment(),
                'created' => $comment->getCreated()->format('Y-m-d H:i:s'),
                'photoUuid' => $comment->getPhotoUuid()->getBytes(),
                'userUuid' => $comment->getUserUuid()->getBytes(),
            ]
        );
        return $this->db->statementHasErrors($stmt) === false;
    }

    public function fetchForPhoto(Photo $photo): PhotoCommentCollection
    {
        $result = $this->db->fetchAll(
            'SELECT 
                photo_comments.user_id, 
                photo_comments.photo_id, 
                photo_comments.comment, 
                photo_comments.created, 
                photo_comments.title,
                users.name AS username
            FROM pictures.photo_comments
            LEFT JOIN project.users ON users.id = photo_comments.user_id
            WHERE photo_comments.photo_id = :photoId AND photo_comments.verified = 1',
            [
                'photoId' => $photo->getId(),
            ]
        );
        return new PhotoCommentCollection($result);
    }
}

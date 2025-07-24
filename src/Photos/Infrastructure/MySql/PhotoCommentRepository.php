<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoCommentCollection;
use MyEspacio\Photos\Domain\Entity\PhotoComment;
use MyEspacio\Photos\Domain\Repository\PhotoCommentRepositoryInterface;

final readonly class PhotoCommentRepository implements PhotoCommentRepositoryInterface
{
    public function __construct(
        private Connection $db
    ) {
    }

    public function getCommentCount(int $photoId): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) AS comment_count 
            FROM pictures.photo_comments 
            WHERE photo_id = :photoId',
            [
                'photoId' => $photoId,
            ]
        );
        $count = $result['comment_count'] ?? 0;
        if (is_numeric($count)) {
            return (int) $count;
        }
        return 0;
    }

    public function addComment(PhotoComment $comment): bool
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
                'photoUuid' => $comment->getPhotoUuid(),
                'userUuid' => $comment->getUserUuid(),
            ]
        );
        return $this->db->statementHasErrors($stmt) === false;
    }

    public function getPhotoComments(int $photoId): PhotoCommentCollection
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
            WHERE photo_id = :photoId AND verified = 1',
            [
                'photoId' => $photoId,
            ]
        );
        return new PhotoCommentCollection($result);
    }
}

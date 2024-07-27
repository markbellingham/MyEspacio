<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoCommentCollection;
use MyEspacio\Photos\Domain\Entity\PhotoComment;
use MyEspacio\Photos\Domain\Repository\PhotoCommentRepositoryInterface;

final class PhotoCommentRepository implements PhotoCommentRepositoryInterface
{
    public function __construct(
        private readonly Connection $db
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
        return (int) ($result['comment_count'] ?? 0);
    }

    public function addComment(PhotoComment $comment): bool
    {
        $stmt = $this->db->run(
            'INSERT INTO pictures.photo_comments (user_id, photo_id, comment, created)
            VALUES (:userId, :photoId, :comment, :created)',
            [
                'comment' => $comment->getComment(),
                'created' => $comment->getCreatedString(),
                'photoId' => $comment->getPhotoId(),
                'userId' => $comment->getUserId(),
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

<?php

declare(strict_types=1);

namespace Tests\Photos\Infrastructure\MySql;

use Monolog\DateTimeImmutable;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoCommentCollection;
use MyEspacio\Photos\Domain\Entity\PhotoComment;
use MyEspacio\Photos\Infrastructure\MySql\PhotoCommentRepository;
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class PhotoCommentRepositoryTest extends TestCase
{
    public function testGetCommentCount(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT COUNT(*) AS comment_count 
            FROM pictures.photo_comments 
            WHERE photo_id = :photoId',
                [
                    'photoId' => 1,
                ]
            )
            ->willReturn(
                [
                    'comment_count' => '2',
                ]
            );

        $repository = new PhotoCommentRepository($db);
        $result = $repository->getCommentCount(1);
        $this->assertSame(2, $result);
    }

    public function testGetCommentCountNoRecordsFound(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT COUNT(*) AS comment_count 
            FROM pictures.photo_comments 
            WHERE photo_id = :photoId',
                [
                    'photoId' => 1,
                ]
            )
            ->willReturn(
                [
                    'comment_count' => '0',
                ]
            );

        $repository = new PhotoCommentRepository($db);
        $result = $repository->getCommentCount(1);
        $this->assertSame(0, $result);
    }

    public function testGetCommentCountQueryFail(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT COUNT(*) AS comment_count 
            FROM pictures.photo_comments 
            WHERE photo_id = :photoId',
                [
                    'photoId' => 1,
                ]
            )
            ->willReturn(null);

        $repository = new PhotoCommentRepository($db);
        $result = $repository->getCommentCount(1);
        $this->assertSame(0, $result);
    }

    public function testAddComment(): void
    {
        $comment = new PhotoComment(
            photoId: 1,
            comment: 'Great photo!',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-07-27 16:34:00'),
            title: '',
            userId: 2,
            username: ''
        );
        $stmt = $this->createMock(PDOStatement::class);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO pictures.photo_comments (user_id, photo_id, comment, created)
            VALUES (:userId, :photoId, :comment, :created)',
                [
                    'comment' => $comment->getComment(),
                    'created' => $comment->getCreatedString(),
                    'photoId' => $comment->getPhotoId(),
                    'userId' => $comment->getUserId(),
                ]
            )
            ->willReturn($stmt);

        $repository = new PhotoCommentRepository($db);
        $result = $repository->addComment($comment);

        $this->assertTrue($result);
    }

    public function testAddCommentQueryFail(): void
    {
        $comment = new PhotoComment(
            photoId: 1,
            comment: 'Great photo!',
            created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-07-27 16:34:00'),
            title: '',
            userId: 2,
            username: ''
        );
        $stmt = $this->createMock(PDOStatement::class);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO pictures.photo_comments (user_id, photo_id, comment, created)
            VALUES (:userId, :photoId, :comment, :created)',
                [
                    'comment' => $comment->getComment(),
                    'created' => $comment->getCreatedString(),
                    'photoId' => $comment->getPhotoId(),
                    'userId' => $comment->getUserId(),
                ]
            )
            ->willReturn($stmt);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->willReturn(true);

        $repository = new PhotoCommentRepository($db);
        $result = $repository->addComment($comment);

        $this->assertFalse($result);
    }

    public function testGetPhotoComments(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
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
                    'photoId' => 1,
                ]
            )
            ->willReturn(
                [
                    [
                        'photo_id' => '1',
                        'comment' => 'Nice photo!',
                        'created' => '2024-07-27 16:34:00',
                        'title' => 'Some Title',
                        'user_id' => '2',
                        'username' => 'Mark Bellingham'
                    ],
                    [
                        'photo_id' => '1',
                        'comment' => 'Nice photo!',
                        'created' => null,
                        'title' => null,
                        'user_id' => '2',
                        'username' => 'Mark Bellingham'
                    ]
                ]
            );

        $repository = new PhotoCommentRepository($db);
        $result = $repository->getPhotoComments(1);

        $this->assertInstanceOf(PhotoCommentCollection::class, $result);
        $this->assertCount(2, $result);
    }

    public function testGetPhotoCommentsNoneFound(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
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
                    'photoId' => 1,
                ]
            )
            ->willReturn([]);

        $repository = new PhotoCommentRepository($db);
        $result = $repository->getPhotoComments(1);

        $this->assertInstanceOf(PhotoCommentCollection::class, $result);
        $this->assertCount(0, $result);
    }
}

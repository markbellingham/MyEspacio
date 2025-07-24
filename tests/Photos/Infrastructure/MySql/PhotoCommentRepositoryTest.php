<?php

declare(strict_types=1);

namespace Tests\Photos\Infrastructure\MySql;

use DateTimeImmutable;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoCommentCollection;
use MyEspacio\Photos\Domain\Entity\PhotoComment;
use MyEspacio\Photos\Infrastructure\MySql\PhotoCommentRepository;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoCommentRepositoryTest extends TestCase
{
    /**
     * @param null|array<string, string> $databaseResult
     * @throws Exception
     */
    #[DataProvider('getCommentCountDataProvider')]
    public function testGetCommentCount(
        int $photoId,
        ?array $databaseResult,
        int $expectedFunctionResult
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT COUNT(*) AS comment_count 
            FROM pictures.photo_comments 
            WHERE photo_id = :photoId',
                [
                    'photoId' => $photoId,
                ]
            )
            ->willReturn($databaseResult);

        $repository = new PhotoCommentRepository($db);
        $actualResult = $repository->getCommentCount($photoId);
        $this->assertSame($expectedFunctionResult, $actualResult);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function getCommentCountDataProvider(): array
    {
        return [
            'test_found' => [
                1,
                [
                    'comment_count' => '2',
                ],
                2
            ],
            'test_not_found' => [
                2,
                [
                    'comment_count' => '0'
                ],
                0
            ],
            'test_error' => [
                3,
                null,
                0
            ]
        ];
    }

    #[DataProvider('addCommentDataProvider')]
    public function testAddComment(
        PhotoComment $photoComment,
        string $comment,
        string $date,
        string $photoUuid,
        string $userUuid,
        bool $errors,
        bool $expectedResult
    ): void {
        $stmt = $this->createMock(PDOStatement::class);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO pictures.photo_comments (user_id, photo_id, comment, created)
          SELECT users.id, photos.id, :comment, :created
          FROM project.users
          JOIN pictures.photos
          WHERE users.uuid = :userUuid
          AND photos.uuid = :photoUuid',
                [
                    'comment' => $comment,
                    'created' => $date,
                    'photoUuid' => $photoUuid,
                    'userUuid' => $userUuid,
                ]
            )
            ->willReturn($stmt);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->with($stmt)
            ->willReturn($errors);

        $repository = new PhotoCommentRepository($db);
        $actualResult = $repository->addComment($photoComment);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function addCommentDataProvider(): array
    {
        return [
            'test_success' => [
                new PhotoComment(
                    photoUuid: Uuid::fromString('3ad9590d-6bce-4eb3-a693-e06403178628'),
                    comment: 'Great photo!',
                    created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-07-27 16:34:00'),
                    title: '',
                    userUuid: Uuid::fromString('b8cf4379-62f4-4f98-a57e-9811d1a7d07d'),
                    username: ''
                ),
                'Great photo!',
                '2024-07-27 16:34:00',
                '3ad9590d-6bce-4eb3-a693-e06403178628',
                'b8cf4379-62f4-4f98-a57e-9811d1a7d07d',
                false,
                true
            ],
            'test_failure' => [
                new PhotoComment(
                    photoUuid: Uuid::fromString('3ad9590d-6bce-4eb3-a693-e06403178628'),
                    comment: 'Great photo!',
                    created: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-07-27 16:34:00'),
                    title: '',
                    userUuid: Uuid::fromString('b8cf4379-62f4-4f98-a57e-9811d1a7d07d'),
                    username: ''
                ),
                'Great photo!',
                '2024-07-27 16:34:00',
                '3ad9590d-6bce-4eb3-a693-e06403178628',
                'b8cf4379-62f4-4f98-a57e-9811d1a7d07d',
                true,
                false
            ]
        ];
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
                        'photo_uuid' => 'f133fede-65f5-4b68-aded-f8f0e9bfe3bb',
                        'comment' => 'Nice photo!',
                        'created' => '2024-07-27 16:34:00',
                        'title' => 'Some Title',
                        'user_uuid' => '2cb35615-f812-45b9-b552-88a116979d11',
                        'username' => 'Mark Bellingham'
                    ],
                    [
                        'photo_uuid' => '254b994d-fbb0-4f26-a99d-1da9f189df38',
                        'comment' => 'Nice photo!',
                        'created' => null,
                        'title' => null,
                        'user_uuid' => '51812b8b-a878-4e21-bc9a-e27350c43904',
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

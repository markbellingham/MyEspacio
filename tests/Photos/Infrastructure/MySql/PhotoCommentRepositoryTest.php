<?php

declare(strict_types=1);

namespace Tests\Photos\Infrastructure\MySql;

use DateTimeImmutable;
use DateTimeZone;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoCommentCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoComment;
use MyEspacio\Photos\Domain\Entity\Relevance;
use MyEspacio\Photos\Infrastructure\MySql\PhotoCommentRepository;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoCommentRepositoryTest extends TestCase
{
    /** @param null|array<string, string> $databaseResult */
    #[DataProvider('fetchCountDataProvider')]
    public function testFetchCount(
        Photo $photo,
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
        $actualResult = $repository->fetchCount($photo);
        $this->assertSame($expectedFunctionResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function fetchCountDataProvider(): array
    {
        return [
            'test_found' => [
                'photo' => self::createPhoto(1),
                'photoId' => 1,
                'databaseResult' => [
                    'comment_count' => '2',
                ],
                'expectedFunctionResult' => 2
            ],
            'test_not_found' => [
                'photo' => self::createPhoto(2),
                'photoId' => 2,
                'databaseResult' => [
                    'comment_count' => '0'
                ],
                'expectedFunctionResult' => 0
            ],
            'test_error_1' => [
                'photo' => self::createPhoto(3),
                'photoId' => 3,
                'databaseResult' => null,
                'expectedFunctionResult' => 0
            ],
            'test_error_2' => [
                'photo' => self::createPhoto(3),
                'photoId' => 3,
                'databaseResult' => [
                    'comment_count' => 'nonsense'
                ],
                'expectedFunctionResult' => 0
            ]
        ];
    }

    #[DataProvider('saveDataProvider')]
    public function testSave(
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
        $actualResult = $repository->save($photoComment);

        $this->assertSame($expectedResult, $actualResult);
    }

    /** @return array<string, array<int, mixed>> */
    public static function saveDataProvider(): array
    {
        return [
            'test_success' => [
                new PhotoComment(
                    photoUuid: Uuid::fromString('3ad9590d-6bce-4eb3-a693-e06403178628'),
                    comment: 'Great photo!',
                    created: new DateTimeImmutable('2024-07-27 16:34:00', new DateTimeZone('UTC')),
                    title: '',
                    userUuid: Uuid::fromString('b8cf4379-62f4-4f98-a57e-9811d1a7d07d'),
                    username: ''
                ),
                'Great photo!',
                '2024-07-27 16:34:00',
                hex2bin('3ad9590d6bce4eb3a693e06403178628'),
                hex2bin('b8cf437962f44f98a57e9811d1a7d07d'),
                false,
                true
            ],
            'test_failure' => [
                new PhotoComment(
                    photoUuid: Uuid::fromString('3ad9590d-6bce-4eb3-a693-e06403178628'),
                    comment: 'Great photo!',
                    created: new DateTimeImmutable('2024-07-27 16:34:00', new DateTimeZone('UTC')),
                    title: '',
                    userUuid: Uuid::fromString('b8cf4379-62f4-4f98-a57e-9811d1a7d07d'),
                    username: ''
                ),
                'Great photo!',
                '2024-07-27 16:34:00',
                hex2bin('3ad9590d6bce4eb3a693e06403178628'),
                hex2bin('b8cf437962f44f98a57e9811d1a7d07d'),
                true,
                false
            ]
        ];
    }

    /** @param array<string, array<string, mixed>> $databaseResult */
    #[DataProvider('fetchForPhotoDataProvider')]
    public function testFetchForPhoto(
        Photo $photo,
        int $photoId,
        array $databaseResult,
        PhotoCommentCollection $expectedFunctionResult,
    ): void {
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
            WHERE photo_comments.photo_id = :photoId AND photo_comments.verified = 1',
                [
                    'photoId' => $photoId,
                ]
            )
            ->willReturn($databaseResult);

        $repository = new PhotoCommentRepository($db);
        $actualResult = $repository->fetchForPhoto($photo);

        $this->assertEquals($expectedFunctionResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function fetchForPhotoDataProvider(): array
    {
        return [
            'test_found' => [
                'photo' => self::createPhoto(1),
                'photoId' => 1,
                'databaseResult' => [
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
                    ],
                ],
                'expectedFunctionResult' => new PhotoCommentCollection([
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
                    ],
                ]),
            ],
            'test_not_found' => [
                'photo' => self::createPhoto(2),
                'photoId' => 2,
                'databaseResult' => [],
                'expectedFunctionResult' => new PhotoCommentCollection([]),
            ],
        ];
    }

    private static function createPhoto(int $id): Photo
    {
        return new Photo(
            country: new Country(
                id: 45,
                name:'Chile',
                twoCharCode: 'CL',
                threeCharCode: 'CHL'
            ),
            geoCoordinates: new GeoCoordinates(
                id: 2559,
                photoUuid: Uuid::fromString('8d7fb4b9-b496-478b-bd9e-14dc30a1ca71'),
                latitude: -33438084,
                longitude: -33438084,
                accuracy:   16,
            ),
            dimensions: new Dimensions(
                width: 456,
                height: 123,
            ),
            relevance: new Relevance(
                cScore: 4,
                pScore: 5
            ),
            uuid: Uuid::fromString('8d7fb4b9-b496-478b-bd9e-14dc30a1ca71'),
            dateTaken: new DateTimeImmutable("2012-10-21"),
            description: "Note the spurs...",
            directory: "RTW Trip\/16Chile\/03 - Valparaiso",
            filename: "P1070237.JPG",
            id: $id,
            title: "Getting ready to dance",
            town: "Valparaiso",
            commentCount: 1,
            faveCount: 1,
        );
    }
}

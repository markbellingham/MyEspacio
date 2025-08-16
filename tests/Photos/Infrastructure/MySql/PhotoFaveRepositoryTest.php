<?php

declare(strict_types=1);

namespace Tests\Photos\Infrastructure\MySql;

use DateTimeImmutable;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\PhotoFave;
use MyEspacio\Photos\Domain\Entity\Relevance;
use MyEspacio\Photos\Infrastructure\MySql\PhotoFaveRepository;
use MyEspacio\User\Domain\User;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoFaveRepositoryTest extends TestCase
{
    /** @param array<string, int> $queryParameters */
    #[DataProvider('addDataProvider')]
    public function testAdd(
        array $queryParameters,
        PhotoFave $photoFave,
        bool $statementHasErrors,
        bool $expectedFunctionResult,
    ): void {
        $statement = $this->createMock(PDOStatement::class);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO pictures.photo_faves (user_id, photo_id) VALUES (:userId, :photoId)',
                $queryParameters
            )
            ->willReturn($statement);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->with($statement)
            ->willReturn($statementHasErrors);

        $repository = new PhotoFaveRepository($db);
        $actualResult = $repository->add($photoFave);
        $this->assertSame($expectedFunctionResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function addDataProvider(): array
    {
        return [
            'success' => [
                'queryParameters' => [
                    'userId' => 1,
                    'photoId' => 2689,
                ],
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(2689),
                    user: self::createUser(1),
                ),
                'statementHasErrors' => false,
                'expectedFunctionResult' => true,
            ],
            'failure' => [
                'queryParameters' => [
                    'userId' => 10,
                    'photoId' => 1000,
                ],
                'photoFave' => new PhotoFave(
                    photo: self::createPhoto(1000),
                    user: self::createUser(10),
                ),
                'statementHasErrors' => true,
                'expectedFunctionResult' => false,
            ]
        ];
    }

    /** @param array<string, int> $queryParameters */
    #[DataProvider('addAnonymousDataProvider')]
    public function testAddAnonymous(
        array $queryParameters,
        bool $statementHasErrors,
        PhotoFave $photoFave,
        bool $expectedFunctionResult,
    ): void {
        $statement = $this->createMock(PDOStatement::class);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'INSERT INTO pictures.anon_photo_faves (photo_id) VALUES (:photoId)',
                $queryParameters
            )
            ->willReturn($statement);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->with($statement)
            ->willReturn($statementHasErrors);

        $repository = new PhotoFaveRepository($db);
        $actualResult = $repository->addAnonymous($photoFave);
        $this->assertSame($expectedFunctionResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function addAnonymousDataProvider(): array
    {
        return [
            'success' => [
                'queryParameters' => [
                    'photoId' => 3,
                ],
                'statementHasErrors' => false,
                'photoFave' => new PhotoFave(
                    self::createPhoto(3),
                    self::createUser(1),
                ),
                'expectedFunctionResult' => true,
            ],
            'failure' => [
                'queryParameters' => [
                    'photoId' => 77,
                ],
                'statementHasErrors' => true,
                'photoFave' => new PhotoFave(
                    self::createPhoto(77),
                    self::createUser(1),
                ),
                'expectedFunctionResult' => false,
            ]
        ];
    }

    /** @param array<string, string> $databaseResult */
    #[DataProvider('getPhotoFaveCountDataProvider')]
    public function testGetPhotoFaveCount(
        int $photoId,
        Photo $photo,
        ?array $databaseResult,
        int $expectedFunctionResult,
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
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
                    'photoId' => $photoId
                ]
            )
            ->willReturn($databaseResult);

        $repository = new PhotoFaveRepository($db);
        $actualResult = $repository->countForPhoto($photo);

        $this->assertSame($expectedFunctionResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function getPhotoFaveCountDataProvider(): array
    {
        return [
            'test_1' => [
                'photoId' => 2869,
                'photo' => self::createPhoto(2869),
                'databaseResult' => [
                    'quantity' => '5',
                ],
                'expectedFunctionResult' => 5,
            ],
            'test_2' => [
                'photoId' => 1234,
                'photo' => self::createPhoto(1234),
                'databaseResult' => [
                    'quantity' => '0',
                ],
                'expectedFunctionResult' => 0,
            ],
            'test_3' => [
                'photoId' => 4321,
                'photo' => self::createPhoto(4321),
                'databaseResult' => null,
                'expectedFunctionResult' => 0,
            ],
            'test_4' => [
                'photoId' => 4999,
                'photo' => self::createPhoto(4999),
                'databaseResult' => [
                    'quantity' => 'nonsense',
                ],
                'expectedFunctionResult' => 0,
            ],
        ];
    }

    private static function createPhoto(int $photoId): Photo
    {
        return new Photo(
            country: new Country(
                id: 45,
                name: 'Chile',
                twoCharCode: 'CL',
                threeCharCode: 'CHL',
            ),
            geoCoordinates: new GeoCoordinates(
                id: 2559,
                photoUuid: Uuid::fromString('8d7fb4b9-b496-478b-bd9e-14dc30a1ca71'),
                latitude: -33438084,
                longitude: -33438084,
                accuracy:  16,
            ),
            dimensions: new Dimensions(
                width: 456,
                height: 123,
            ),
            relevance: new Relevance(
                cScore: 4,
                pScore: 5,
            ),
            uuid: Uuid::fromString('8d7fb4b9-b496-478b-bd9e-14dc30a1ca71'),
            dateTaken: new DateTimeImmutable("2012-10-21"),
            description: "Note the spurs...",
            directory: "RTW Trip\/16Chile\/03 - Valparaiso",
            filename: "P1070237.JPG",
            id: $photoId,
            title: "Getting ready to dance",
            town: "Valparaiso",
            commentCount: 1,
            faveCount: 1
        );
    }

    private static function createUser(int $userId): User
    {
        return new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 1,
            loginDate: new DateTimeImmutable('2024-03-02 15:26:00'),
            magicLink: '550e8400-e29b-41d4-a716-446655440000',
            phoneCode: '9bR3xZ',
            passcodeRoute: 'email',
            id: $userId
        );
    }
}

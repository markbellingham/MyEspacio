<?php

declare(strict_types=1);

namespace Tests\Php\Php\Photos\Infrastructure\MySql;

use DateTimeImmutable;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoTagCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\Relevance;
use MyEspacio\Photos\Infrastructure\MySql\PhotoTagRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoTagRepositoryTest extends TestCase
{
    /** @param array<int, array<string, string>> $databaseResult */
    #[DataProvider('fetchForPhotoDataProvider')]
    public function testFetchForPhoto(
        int $photoId,
        Photo $photo,
        array $databaseResult,
        PhototagCollection $expectedFunctionResult,
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT
                tags.id,
                tags.tag,
                photo_tags.photo_id
            FROM project.tags
            LEFT JOIN pictures.photo_tags ON tags.id = photo_tags.tag_id
            WHERE photo_tags.photo_id = :photoId',
                [
                    'photoId' => $photoId
                ]
            )
            ->willReturn($databaseResult);

        $repository = new PhotoTagRepository($db);
        $actualResult = $repository->fetchForPhoto($photo);

        $this->assertEquals($expectedFunctionResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function fetchForPhotoDataProvider(): array
    {
        return [
            'test_1' => [
                'photoId' => 2689,
                'photo' => self::createPhoto(2689),
                'databaseResult' => [
                    [
                        'photo_uuid' => '51812b8b-a878-4e21-bc9a-e27350c43904',
                        'tag' => 'sunset',
                        'id' => 1
                    ],
                    [
                        'photo_uuid' => '7add56c3-ea9a-4c36-916e-a51a19c4bba1',
                        'tag' => 'mexico',
                        'id' => 2
                    ],
                ],
                'expectedFunctionResult' => new PhotoTagCollection([
                    [
                        'photo_uuid' => '51812b8b-a878-4e21-bc9a-e27350c43904',
                        'tag' => 'sunset',
                        'id' => 1
                    ],
                    [
                        'photo_uuid' => '7add56c3-ea9a-4c36-916e-a51a19c4bba1',
                        'tag' => 'mexico',
                        'id' => 2
                    ]
                ]),
            ],
            'test_2' => [
                'photoId' => 1234,
                'photo' => self::createPhoto(1234),
                'databaseResult' => [],
                'expectedFunctionResult' => new PhotoTagCollection([]),
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
}

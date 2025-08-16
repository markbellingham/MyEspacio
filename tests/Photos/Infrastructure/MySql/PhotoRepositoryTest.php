<?php

declare(strict_types=1);

namespace Tests\Photos\Infrastructure\MySql;

use DateTimeImmutable;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\Relevance;
use MyEspacio\Photos\Infrastructure\MySql\PhotoRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoRepositoryTest extends TestCase
{
    public const int MY_FAVOURITES = 2;
    public const string PHOTO_PROPERTIES = 'SELECT photos.id AS photo_id,
        photos.date_taken,
        photos.description,
        photos.directory,
        photos.filename,
        photos.title,
        photos.town,
        photos.height,
        photos.width,
        photos.uuid AS photo_uuid,
        photos.id AS photo_id,
        countries.id AS country_id,
        countries.name AS country_name,
        countries.two_char_code,
        countries.three_char_code,
        geo.id AS geo_id,
        geo.accuracy,
        geo.latitude,
        geo.longitude,
        (SELECT COUNT(DISTINCT photo_comments.id) 
            FROM pictures.photo_comments 
            WHERE photo_comments.photo_id = photos.id) AS comment_count,
        (SELECT COUNT(DISTINCT photo_faves.photo_id) 
            FROM pictures.photo_faves 
            WHERE photo_faves.photo_id = photos.id) AS fave_count
    FROM pictures.photos
    LEFT JOIN pictures.countries ON countries.Id = photos.country
    LEFT JOIN pictures.geo ON photos.id = geo.photo_id
    LEFT JOIN pictures.photo_comments ON photos.id = photo_comments.photo_id
    LEFT JOIN pictures.photo_faves ON photos.id = photo_faves.photo_id
    LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id';

    public const string PHOTO_MATCH_PROPERTIES = 'SELECT 
        photos.id AS photo_id,
        photos.date_taken,
        photos.description,
        photos.directory,
        photos.filename,
        photos.title,
        photos.town,
        photos.height,
        photos.width,
        photos.uuid AS photo_uuid,
        countries.id AS country_id,
        countries.name AS country_name,
        countries.two_char_code,
        countries.three_char_code,
        geo.id AS geo_id,
        geo.accuracy,
        geo.latitude,
        geo.longitude,
        IFNULL(cmt.cmt_count, 0) AS comment_count, 
        IFNULL(fv.fave_count, 0) AS fave_count,
        MATCH(photos.title, photos.description, photos.town) AGAINST(:searchTerms IN BOOLEAN MODE) AS pscore,
        MATCH(countries.name) AGAINST(:searchTerms IN BOOLEAN MODE) AS cscore
    FROM pictures.photos
    LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id
    LEFT JOIN pictures.countries ON countries.id = photos.country
    LEFT JOIN pictures.geo ON photos.id = geo.photo_id
    LEFT JOIN (
        SELECT photo_id, COUNT(photo_id) AS cmt_count
        FROM pictures.photo_comments
        GROUP BY photo_id
    ) AS cmt ON cmt.photo_id = photos.id
    LEFT JOIN (
        SELECT photo_id, COUNT(photo_id) AS fave_count
        FROM pictures.photo_faves
        GROUP BY photo_id
    ) AS fv ON fv.photo_id = photos.id';

    /** @param array<string, string> $databaseResult */
    #[DataProvider('fetchByIdDataProvider')]
    public function testFetchById(
        int $photoId,
        ?array $databaseResult,
        ?Photo $expectedFunctionResult,
    ): void {
        $db = $this->createMock(Connection::class);

        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                self::PHOTO_PROPERTIES . ' WHERE photos.id = :photoId',
                [
                    'photoId' => $photoId
                ]
            )
            ->willReturn($databaseResult);

        $repository = new PhotoRepository($db);
        $actualResult = $repository->fetchById($photoId);

        $this->assertEquals($expectedFunctionResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function fetchByIdDataProvider(): array
    {
        return [
            'test_1' => [
                'photoId' => 2689,
                'databaseResult' => [
                    'country_id' => '45',
                    'country_name' => 'Chile',
                    'two_char_code' => 'CL',
                    'three_char_code' => 'CHL',
                    'geo_id' => '2559',
                    'latitude' => '-33438084',
                    'longitude' => '-33438084',
                    'accuracy' =>  '16',
                    'width' => '456',
                    'height' => '123',
                    'date_taken' => "2012-10-21",
                    'description' => "Note the spurs...",
                    'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                    'filename' => "P1070237.JPG",
                    'photo_id' => '2689',
                    'photo_uuid' => '02175773-89e6-4ab6-b559-5c16998bd7cd',
                    'title' => "Getting ready to dance",
                    'town' => "Valparaiso",
                    'comment_count' => '1',
                    'fave_count' => '1',
                    'cscore' => '4',
                    'pscore' => '5',
                ],
                'expectedFunctionResult' => new Photo(
                    country: new Country(
                        id: 45,
                        name: 'Chile',
                        twoCharCode: 'CL',
                        threeCharCode: 'CHL',
                    ),
                    geoCoordinates: new GeoCoordinates(
                        id: 2559,
                        photoUuid: Uuid::fromString('02175773-89e6-4ab6-b559-5c16998bd7cd'),
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
                    uuid: Uuid::fromString('02175773-89e6-4ab6-b559-5c16998bd7cd'),
                    dateTaken: new DateTimeImmutable("2012-10-21"),
                    description: "Note the spurs...",
                    directory: "RTW Trip\/16Chile\/03 - Valparaiso",
                    filename: "P1070237.JPG",
                    id: 2689,
                    title: "Getting ready to dance",
                    town: "Valparaiso",
                    commentCount: 1,
                    faveCount: 1
                )
            ],
            'test_2' => [
                'photoId' => 4521,
                'databaseResult' => [
                    'country_id' => '81',
                    'country_name' => 'Japan',
                    'two_char_code' => 'JP',
                    'three_char_code' => 'JPN',
                    'geo_id' => '7842',
                    'latitude' => '35676691',
                    'longitude' => '139650311',
                    'accuracy' => '12',
                    'width' => '1024',
                    'height' => '768',
                    'date_taken' => "2018-04-03",
                    'description' => "Cherry blossoms in full bloom during hanami season",
                    'directory' => "Asia Journey\/02Japan\/Tokyo\/Ueno Park",
                    'filename' => "IMG_5829.JPG",
                    'photo_id' => '4521',
                    'photo_uuid' => 'f7d9c2e1-4a8b-4c3d-9e2f-8b7a6c5d4e3f',
                    'title' => "Sakura at Ueno Park",
                    'town' => "Tokyo",
                    'comment_count' => '8',
                    'fave_count' => '23',
                    'cscore' => '9',
                    'pscore' => '8',
                ],
                'expectedFunctionResult' => new Photo(
                    country: new Country(
                        id: 81,
                        name: 'Japan',
                        twoCharCode: 'JP',
                        threeCharCode: 'JPN',
                    ),
                    geoCoordinates: new GeoCoordinates(
                        id: 7842,
                        photoUuid: Uuid::fromString('f7d9c2e1-4a8b-4c3d-9e2f-8b7a6c5d4e3f'),
                        latitude: 35676691,
                        longitude: 139650311,
                        accuracy: 12,
                    ),
                    dimensions: new Dimensions(
                        width: 1024,
                        height: 768,
                    ),
                    relevance: new Relevance(
                        cScore: 9,
                        pScore: 8,
                    ),
                    uuid: Uuid::fromString('f7d9c2e1-4a8b-4c3d-9e2f-8b7a6c5d4e3f'),
                    dateTaken: new DateTimeImmutable("2018-04-03"),
                    description: "Cherry blossoms in full bloom during hanami season",
                    directory: "Asia Journey\/02Japan\/Tokyo\/Ueno Park",
                    filename: "IMG_5829.JPG",
                    id: 4521,
                    title: "Sakura at Ueno Park",
                    town: "Tokyo",
                    commentCount: 8,
                    faveCount: 23
                )
            ],
            'test_not_found' => [
                'photoId' => 4521,
                'databaseResult' => null,
                'expectedFunctionResult' => null,
            ],
        ];
    }

    /** @param array<string, string>|null $queryResult */
    #[DataProvider('fetchByUuidDataProvider')]
    public function testFetchByUuid(
        ?array $queryResult,
        string $binary,
        string $uuid,
        ?Photo $expectedResult
    ): void {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('fetchOne')
            ->with(
                self::PHOTO_PROPERTIES . ' WHERE photos.uuid = :uuid',
                [
                    'uuid' => $binary
                ]
            )
            ->willReturn($queryResult);

        $repository = new PhotoRepository($connection);
        $actualResult = $repository->fetchByUuid(Uuid::fromString($uuid));

        $this->assertEquals($expectedResult, $actualResult);
    }

    /** @return array<string, array<int, mixed>> */
    public static function fetchByUuidDataProvider(): array
    {
        return [
            'test_1' => [
                [
                    'country_id' => '45',
                    'country_name' => 'Chile',
                    'two_char_code' => 'CL',
                    'three_char_code' => 'CHL',
                    'geo_id' => '2559',
                    'photo_id' => '2689',
                    'photo_uuid' => '02175773-89e6-4ab6-b559-5c16998bd7cd',
                    'latitude' => '-33438084',
                    'longitude' => '-33438084',
                    'accuracy' =>  '16',
                    'width' => '456',
                    'height' => '123',
                    'cscore' => '4',
                    'pscore' => '5',
                    'date_taken' => "2012-10-21",
                    'description' => "Note the spurs...",
                    'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                    'filename' => "P1070237.JPG",
                    'title' => "Getting ready to dance",
                    'town' => "Valparaiso",
                    'comment_count' => '1',
                    'fave_count' => '1'
                ],
                "\x02\x17\x57\x73\x89\xE6\x4A\xB6\xB5\x59\x5C\x16\x99\x8B\xD7\xCD",
                '02175773-89e6-4ab6-b559-5c16998bd7cd',
                Photo::createFromDataSet(new DataSet([
                    'country_id' => '45',
                    'country_name' => 'Chile',
                    'two_char_code' => 'CL',
                    'three_char_code' => 'CHL',
                    'geo_id' => '2559',
                    'photo_id' => '2689',
                    'photo_uuid' => '02175773-89e6-4ab6-b559-5c16998bd7cd',
                    'latitude' => '-33438084',
                    'longitude' => '-33438084',
                    'accuracy' =>  '16',
                    'width' => '456',
                    'height' => '123',
                    'cscore' => '4',
                    'pscore' => '5',
                    'date_taken' => "2012-10-21",
                    'description' => "Note the spurs...",
                    'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                    'filename' => "P1070237.JPG",
                    'title' => "Getting ready to dance",
                    'town' => "Valparaiso",
                    'comment_count' => '1',
                    'fave_count' => '1'
                ]))
            ],
            'not_found' => [
                null,
                "\x38\xA0\xA2\x18\x9A\x5C\x4B\xB9\xAB\x30\xAA\xE6\xCA\x3F\xFC\x61",
                '38a0a218-9a5c-4bb9-ab30-aae6ca3ffc61',
                null
            ]
        ];
    }

    /** @param array<int, array<string, string>> $databaseResult */
    #[DataProvider('topPhotosDataProvider')]
    public function testTopPhotos(
        array $databaseResult,
        PhotoCollection $expectedFunctionResult,
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                self::PHOTO_PROPERTIES .
                'ORDER BY (comment_count + fave_count)
            LIMIT 100',
                []
            )
            ->willReturn($databaseResult);

        $repository = new PhotoRepository($db);
        $actualResult = $repository->topPhotos();

        $this->assertEquals($expectedFunctionResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function topPhotosDataProvider(): array
    {
        return [
            'test_1' => [
                'databaseResult' => [
                    [
                        'country_id' => '45',
                        'country_name' => 'Chile',
                        'two_char_code' => 'CL',
                        'three_char_code' => 'CHL',
                        'geo_id' => '2559',
                        'latitude' => '-33438084',
                        'longitude' => '-33438084',
                        'accuracy' =>  '16',
                        'width' => '456',
                        'height' => '123',
                        'date_taken' => "2012-10-21",
                        'description' => "Note the spurs...",
                        'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                        'filename' => "P1070237.JPG",
                        'photo_id' => '2689',
                        'title' => "Getting ready to dance",
                        'town' => "Valparaiso",
                        'comment_count' => '1',
                        'fave_count' => '1',
                        'photo_uuid' => '51812b8b-a878-4e21-bc9a-e27350c43904'
                    ],
                ],
                'expectedFunctionResult' => new PhotoCollection([
                    [
                        'country_id' => '45',
                        'country_name' => 'Chile',
                        'two_char_code' => 'CL',
                        'three_char_code' => 'CHL',
                        'geo_id' => '2559',
                        'latitude' => '-33438084',
                        'longitude' => '-33438084',
                        'accuracy' =>  '16',
                        'width' => '456',
                        'height' => '123',
                        'date_taken' => "2012-10-21",
                        'description' => "Note the spurs...",
                        'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                        'filename' => "P1070237.JPG",
                        'photo_id' => '2689',
                        'title' => "Getting ready to dance",
                        'town' => "Valparaiso",
                        'comment_count' => '1',
                        'fave_count' => '1',
                        'photo_uuid' => '51812b8b-a878-4e21-bc9a-e27350c43904'
                    ],
                ])
            ],
            'test_2' => [
                'databaseResult' => [],
                'expectedFunctionResult' => new PhotoCollection([]),
            ]
        ];
    }

    /** @param array<string, array<string, string>> $databaseResult */
    #[DataProvider('randomSelectionDataProvider')]
    public function testRandomSelection(
        array $databaseResult,
        PhotoCollection $expectedFunctionResult,
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                self::PHOTO_PROPERTIES .
                ' WHERE photo_album.album_id = :albumId
            ORDER BY RAND()
            LIMIT 100',
                [
                    'albumId' => self::MY_FAVOURITES
                ]
            )
            ->willReturn($databaseResult);

        $repository = new PhotoRepository($db);
        $actualResult = $repository->randomSelection();

        $this->assertEquals($expectedFunctionResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function randomSelectionDataProvider(): array
    {
        return [
            'test_1' => [
                'databaseResult' => [
                    [
                        'country_id' => '45',
                        'country_name' => 'Chile',
                        'two_char_code' => 'CL',
                        'three_char_code' => 'CHL',
                        'geo_id' => '2559',
                        'latitude' => '-33438084',
                        'longitude' => '-33438084',
                        'accuracy' =>  '16',
                        'width' => '456',
                        'height' => '123',
                        'date_taken' => "2012-10-21",
                        'description' => "Note the spurs...",
                        'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                        'filename' => "P1070237.JPG",
                        'photo_id' => '2689',
                        'title' => "Getting ready to dance",
                        'town' => "Valparaiso",
                        'comment_count' => '1',
                        'fave_count' => '1',
                        'photo_uuid' => '51812b8b-a878-4e21-bc9a-e27350c43904'
                    ],
                ],
                'expectedFunctionResult' => new PhotoCollection([
                    [
                        'country_id' => '45',
                        'country_name' => 'Chile',
                        'two_char_code' => 'CL',
                        'three_char_code' => 'CHL',
                        'geo_id' => '2559',
                        'latitude' => '-33438084',
                        'longitude' => '-33438084',
                        'accuracy' =>  '16',
                        'width' => '456',
                        'height' => '123',
                        'date_taken' => "2012-10-21",
                        'description' => "Note the spurs...",
                        'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                        'filename' => "P1070237.JPG",
                        'photo_id' => '2689',
                        'title' => "Getting ready to dance",
                        'town' => "Valparaiso",
                        'comment_count' => '1',
                        'fave_count' => '1',
                        'photo_uuid' => '51812b8b-a878-4e21-bc9a-e27350c43904'
                    ],
                ]),
            ],
            'test_2' => [
                'databaseResult' => [],
                'expectedFunctionResult' => new PhotoCollection([]),
            ]
        ];
    }

    /**
     * @param array<int, string> $queryTerms
     * @param array<int, string> $searchTerms
     * @param array<int, array<string, string>> $searchResults
     */
    #[DataProvider('searchDataProvider')]
    public function testSearch(
        array $queryTerms,
        array $searchTerms,
        string $expectedSearchString,
        array $searchResults,
        int $searchCount
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                self::PHOTO_MATCH_PROPERTIES . ' WHERE ' . $expectedSearchString,
                $searchTerms
            )
            ->willReturn($searchResults);

        $repository = new PhotoRepository($db);
        $results = $repository->search($queryTerms);

        $this->assertInstanceOf(PhotoCollection::class, $results);
        $this->assertCount($searchCount, $results);
    }

    /** @return array<string, mixed> */
    public static function searchDataProvider(): array
    {
        return [
            'test_1' => [
                ['sunset'],
                [
                    'searchTerms' => 'sunset*',
                    'term0' => 'sunset*'
                ],
                "(MATCH (photos.title, photos.description, photos.town) AGAINST (:term0 IN BOOLEAN MODE) OR MATCH (countries.name) AGAINST (:term0 IN BOOLEAN MODE))",
                [
                    [
                        'country_id' => '45',
                        'country_name' => 'Chile',
                        'two_char_code' => 'CL',
                        'three_char_code' => 'CHL',
                        'geo_id' => '2559',
                        'latitude' => '-33438084',
                        'longitude' => '-33438084',
                        'accuracy' =>  '16',
                        'width' => '456',
                        'height' => '123',
                        'date_taken' => "2012-10-21",
                        'description' => "Note the spurs...",
                        'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                        'filename' => "P1070237.JPG",
                        'photo_id' => '2689',
                        'title' => "Getting ready to dance",
                        'town' => "Valparaiso",
                        'comment_count' => '1',
                        'fave_count' => '1',
                        'photo_uuid' => 'adf36769-8983-448d-b3ad-0ab1e5edb9c5'
                    ],
                    [
                        'country_id' => '45',
                        'country_name' => 'Chile',
                        'two_char_code' => 'CL',
                        'three_char_code' => 'CHL',
                        'geo_id' => '2559',
                        'latitude' => '-33438084',
                        'longitude' => '-33438084',
                        'accuracy' =>  '16',
                        'width' => '456',
                        'height' => '123',
                        'date_taken' => "2012-10-21",
                        'description' => "Note the spurs...",
                        'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                        'filename' => "P1070237.JPG",
                        'photo_id' => '2689',
                        'title' => "Getting ready to dance",
                        'town' => "Valparaiso",
                        'comment_count' => '1',
                        'fave_count' => '1',
                        'photo_uuid' => '4b9d0175-6d47-4460-b48b-6385db446a30'
                    ]
                ],
                2
            ],
            'test_2' => [
                ['mexico','sunset'],
                [
                    'searchTerms' => 'mexico* sunset*',
                    'term0' => 'mexico*',
                    'term1' => 'sunset*'
                ],
                "(MATCH (photos.title, photos.description, photos.town) AGAINST (:term0 IN BOOLEAN MODE) OR MATCH (countries.name) AGAINST (:term0 IN BOOLEAN MODE)) AND (MATCH (photos.title, photos.description, photos.town) AGAINST (:term1 IN BOOLEAN MODE) OR MATCH (countries.name) AGAINST (:term1 IN BOOLEAN MODE))",
                [],
                0
            ]
        ];
    }

    public function testSearchNoParams(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->never())
            ->method('fetchAll');

        $repository = new PhotoRepository($db);
        $results = $repository->search(['ab']);

        $this->assertInstanceOf(PhotoCollection::class, $results);
        $this->assertCount(0, $results);
    }
}

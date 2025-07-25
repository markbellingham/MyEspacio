<?php

declare(strict_types=1);

namespace Tests\Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Infrastructure\MySql\PhotoRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
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

    public function testFetchById(): void
    {
        $db = $this->createMock(Connection::class);

        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                self::PHOTO_PROPERTIES . ' WHERE photos.id = :photoId',
                [
                    'photoId' => 1
                ]
            )
            ->willReturn(
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
                    'photo_uuid' => '02175773-89e6-4ab6-b559-5c16998bd7cd',
                    'title' => "Getting ready to dance",
                    'town' => "Valparaiso",
                    'comment_count' => '1',
                    'fave_count' => '1'
                ]
            );

        $repository = new PhotoRepository($db);
        $result = $repository->fetchById(1);

        $this->assertInstanceOf(Photo::class, $result);
    }

    /**
     * @param array<string, string>|null $queryResult
     * @throws Exception
     */
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

    public function testFetchByIdNotFound(): void
    {
        $db = $this->createMock(Connection::class);

        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                self::PHOTO_PROPERTIES . ' WHERE photos.id = :photoId',
                [
                    'photoId' => 1
                ]
            )
            ->willReturn(null);

        $repository = new PhotoRepository($db);
        $result = $repository->fetchById(1);

        $this->assertNull($result);
    }

    public function testTopPhotos(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                self::PHOTO_PROPERTIES .
                'ORDER BY (comment_count + fave_count)
            LIMIT 100',
                []
            )
            ->willReturn([
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
                ]
            ]);

        $repository = new PhotoRepository($db);
        $result = $repository->topPhotos();

        $this->assertInstanceOf(PhotoCollection::class, $result);
        $this->assertCount(1, $result);
    }

    public function testTopPhotosDatabaseError(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                self::PHOTO_PROPERTIES .
                'ORDER BY (comment_count + fave_count)
            LIMIT 100',
                []
            )
            ->willReturn([]);

        $repository = new PhotoRepository($db);
        $result = $repository->topPhotos();

        $this->assertInstanceOf(PhotoCollection::class, $result);
        $this->assertCount(0, $result);
    }

    public function testRandomSelection(): void
    {
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
            ->willReturn(
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
                        'photo_uuid' => '51812b8b-a878-4e21-bc9a-e27350c43904'
                    ]
                ]
            );

        $repository = new PhotoRepository($db);
        $result = $repository->randomSelection();

        $this->assertInstanceOf(PhotoCollection::class, $result);
        $this->assertCount(1, $result);
    }

    public function testRandomSelectionDatabaseError(): void
    {
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
            ->willReturn([]);

        $repository = new PhotoRepository($db);
        $result = $repository->randomSelection();

        $this->assertInstanceOf(PhotoCollection::class, $result);
        $this->assertCount(0, $result);
    }

    /**
     * @param array<int, string> $queryTerms
     * @param array<int, string> $searchTerms
     * @param array<int, array<string, string>> $searchResults
     * @throws Exception
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

    /**
     * @return array<string, mixed>
     */
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

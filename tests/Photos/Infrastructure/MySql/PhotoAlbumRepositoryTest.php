<?php

declare(strict_types=1);

namespace Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoAlbumCollection;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Infrastructure\MySql\PhotoAlbumRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class PhotoAlbumRepositoryTest extends TestCase
{
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

    /**
     * @param null|array<string, string> $queryResult
     * @throws Exception
     */
    #[DataProvider('fetchByIdDataProvider')]
    public function testFetchById(
        int $albumId,
        ?array $queryResult,
        ?PhotoAlbum $expectedFunctionResult
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT albums.album_id, albums.uuid AS album_uuid, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id
            WHERE albums.album_id = :albumId',
                [
                    'albumId' => $albumId
                ]
            )
            ->willReturn($queryResult);

        $repository = new PhotoAlbumRepository($db);
        $actualResult = $repository->fetchById($albumId);

        $this->assertEquals($expectedFunctionResult, $actualResult);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function fetchByIdDataProvider(): array
    {
        return [
            'test_found' => [
                4,
                [
                    'album_id' => 4,
                    'album_uuid' => '3ad9590d-6bce-4eb3-a693-e06403178628',
                    'description' => null,
                    'title' => 'The Red Fort, Delhi',
                    'country_id' => '102',
                    'country_name' => 'India',
                    'three_char_code' => 'IND',
                    'two_char_code' => 'IN'
                ],
                new PhotoAlbum(
                    title: 'The Red Fort, Delhi',
                    albumId: 4,
                    uuid: Uuid::fromString('3ad9590d-6bce-4eb3-a693-e06403178628'),
                    description: '',
                    country: new Country(
                        id: 102,
                        name: 'India',
                        twoCharCode: 'IN',
                        threeCharCode:  'IND'
                    ),
                    photos: new PhotoCollection([])
                )
            ],
            'test_not_found' => [
                4,
                null,
                null
            ]
        ];
    }

    /**
     * @param array<string, string> $databaseResult
     * @throws Exception
     */
    #[DataProvider('fetchAllDataProvider')]
    public function testFetchAll(
        array $databaseResult,
        int $count,
        PhotoAlbumCollection $expectedFunctionResult
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT albums.album_id, albums.uuid AS album_uuid, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id',
                []
            )
            ->willReturn($databaseResult);

        $repository = new PhotoAlbumRepository($db);
        $actualResult = $repository->fetchAll();

        $this->assertInstanceOf(PhotoAlbumCollection::class, $actualResult);
        $this->assertCount($count, $actualResult);
        $this->assertEquals($expectedFunctionResult, $actualResult);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function fetchAllDataProvider(): array
    {
        return [
            'test_found' => [
                [
                    [
                        'album_id' => '4',
                        'album_uuid' => '7add56c3-ea9a-4c36-916e-a51a19c4bba1',
                        'description' => null,
                        'title' => 'The Red Fort, Delhi',
                        'country_id' => '102',
                        'country_name' => 'India',
                        'three_char_code' => 'IND',
                        'two_char_code' => 'IN'
                    ],
                    [
                        'album_id' => '5',
                        'album_uuid' => '51812b8b-a878-4e21-bc9a-e27350c43904',
                        'description' => null,
                        'title' => 'Qutab Minar, Delhi',
                        'country_id' => '102',
                        'country_name' => 'India',
                        'three_char_code' => 'IND',
                        'two_char_code' => 'IN'
                    ],
                    [
                        'album_id' => '7',
                        'album_uuid' => '254b994d-fbb0-4f26-a99d-1da9f189df38',
                        'description' => null,
                        'title' => 'Mumbai',
                        'country_id' => '102',
                        'country_name' => 'India',
                        'three_char_code' => 'IND',
                        'two_char_code' => 'IN'
                    ]
                ],
                3,
                new PhotoAlbumCollection(
                    [
                        [
                            'album_id' => '4',
                            'album_uuid' => '7add56c3-ea9a-4c36-916e-a51a19c4bba1',
                            'description' => null,
                            'title' => 'The Red Fort, Delhi',
                            'country_id' => '102',
                            'country_name' => 'India',
                            'three_char_code' => 'IND',
                            'two_char_code' => 'IN'
                        ],
                        [
                            'album_id' => '5',
                            'album_uuid' => '51812b8b-a878-4e21-bc9a-e27350c43904',
                            'description' => null,
                            'title' => 'Qutab Minar, Delhi',
                            'country_id' => '102',
                            'country_name' => 'India',
                            'three_char_code' => 'IND',
                            'two_char_code' => 'IN'
                        ],
                        [
                            'album_id' => '7',
                            'album_uuid' => '254b994d-fbb0-4f26-a99d-1da9f189df38',
                            'description' => null,
                            'title' => 'Mumbai',
                            'country_id' => '102',
                            'country_name' => 'India',
                            'three_char_code' => 'IND',
                            'two_char_code' => 'IN'
                        ]
                    ]
                )
            ],
            'test_not_found' => [
                [],
                0,
                new PhotoAlbumCollection([])
            ]
        ];
    }

    /**
     * @param array<int, array<string, string>> $databaseResult
     * @throws Exception
     */
    #[DataProvider('fetchAlbumPhotosDataProvider')]
    public function testFetchAlbumPhotos(
        PhotoAlbum $photoAlbum,
        int $photoCount,
        array $databaseResult,
        PhotoCollection $expectedFunctionResult
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                self::PHOTO_PROPERTIES . ' WHERE photo_album.album_id = :albumId',
                [
                    'albumId' => '1'
                ]
            )
            ->willReturn($databaseResult);

        $repository = new PhotoAlbumRepository($db);
        $actualResult = $repository->fetchAlbumPhotos($photoAlbum);

        $this->assertInstanceOf(PhotoCollection::class, $actualResult);
        $this->assertCount($photoCount, $actualResult);
        $this->assertEquals($expectedFunctionResult, $actualResult);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function fetchAlbumPhotosDataProvider(): array
    {
        return [
            'test_found' => [
                new PhotoAlbum(
                    title: 'MyAlbum',
                    albumId: 1,
                    uuid: Uuid::fromString('f133fede-65f5-4b68-aded-f8f0e9bfe3bb'),
                    description: 'My favourite photos',
                    country: new Country(
                        id: 1,
                        name: 'United Kingdom',
                        twoCharCode: 'GB',
                        threeCharCode: 'GBR'
                    )
                ),
                1,
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
                        'photo_uuid' => '2cb35615-f812-45b9-b552-88a116979d11'
                    ]
                ],
                new PhotoCollection(
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
                            'photo_uuid' => '2cb35615-f812-45b9-b552-88a116979d11'
                        ]
                    ]
                )
            ],
            'test_not_found' => [
                new PhotoAlbum(
                    title: 'MyAlbum',
                    albumId: 1,
                    uuid: Uuid::fromString('f133fede-65f5-4b68-aded-f8f0e9bfe3bb'),
                    description: 'My favourite photos',
                    country: new Country(
                        id: 1,
                        name: 'United Kingdom',
                        twoCharCode: 'GB',
                        threeCharCode: 'GBR'
                    )
                ),
                0,
                [],
                new PhotoCollection([])
            ]
        ];
    }

    /**
     * @param array<int, mixed> $queryTerms
     * @param array<int, mixed> $searchTerms
     * @param array<int, array<string, string>> $searchResults
     * @throws Exception
     */
    #[DataProvider('searchAlbumPhotosDataProvider')]
    public function testSearchAlbumPhotos(
        PhotoAlbum $photoAlbum,
        int $albumId,
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
                self::PHOTO_MATCH_PROPERTIES . ' WHERE photo_album.album_id = :albumId AND ' . $expectedSearchString,
                $searchTerms
            )
            ->willReturn($searchResults);

        $repository = new PhotoAlbumRepository($db);
        $actualResult = $repository->searchAlbumPhotos($photoAlbum, $queryTerms);

        $this->assertInstanceOf(PhotoCollection::class, $actualResult);
        $this->assertCount($searchCount, $actualResult);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function searchAlbumPhotosDataProvider(): array
    {
        return [
            'test_1' => [
                new PhotoAlbum(
                    title: 'MyAlbum',
                    albumId: 1,
                    uuid: Uuid::fromString('72f997d2-1614-46f1-8396-434042ecd0b3'),
                    description: 'My favourite photos',
                    country: new Country(
                        id: 1,
                        name: 'United Kingdom',
                        twoCharCode: 'GB',
                        threeCharCode: 'GBR'
                    )
                ),
                1,
                ['dance'],
                [
                    'albumId' => 1,
                    'searchTerms' => 'dance*',
                    'term0' => 'dance*'
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
                        'photo_uuid' => '4fb5fe7d-41de-4b41-a5f9-1897221f4333'
                    ]
                ],
                1
            ],
            'test_2' => [
                new PhotoAlbum(
                    title: 'BigAlbum',
                    albumId: 2,
                    description: 'Loads of photos',
                    country: null
                ),
                2,
                ['dance','carnival'],
                [
                    'albumId' => 2,
                    'searchTerms' => 'dance* carnival*',
                    'term0' => 'dance*',
                    'term1' => 'carnival*'
                ],
                "(MATCH (photos.title, photos.description, photos.town) AGAINST (:term0 IN BOOLEAN MODE) OR MATCH (countries.name) AGAINST (:term0 IN BOOLEAN MODE)) AND (MATCH (photos.title, photos.description, photos.town) AGAINST (:term1 IN BOOLEAN MODE) OR MATCH (countries.name) AGAINST (:term1 IN BOOLEAN MODE))",
                [],
                0
            ]
        ];
    }

    public function testSearchAlbumPhotosNotParams(): void
    {
        $queryTerms = ['ab'];
        $photoAlbum = new PhotoAlbum(
            title: 'MyAlbum',
            uuid: Uuid::uuid4(),
            description: 'My favourite photos',
            country: new Country(
                id: 1,
                name: 'United Kingdom',
                twoCharCode: 'GB',
                threeCharCode: 'GBR'
            )
        );
        $db = $this->createMock(Connection::class);
        $db->expects($this->never())
            ->method('fetchAll');

        $repository = new PhotoAlbumRepository($db);
        $actualResult = $repository->searchAlbumPhotos($photoAlbum, $queryTerms);

        $this->assertInstanceOf(PhotoCollection::class, $actualResult);
        $this->assertCount(0, $actualResult);
    }

    /**
     * @param null|array<string, string> $queryResult
     * @throws Exception
     */
    #[DataProvider('fetchByNameDataProvider')]
    public function testFetchByName(
        string $requestAlbumName,
        string $paramAlbumName,
        ?array $queryResult,
        ?PhotoAlbum $album
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT albums.album_id, albums.uuid AS album_uuid, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id
            WHERE LOWER(title) LIKE :albumName',
                [
                    'albumName' => $paramAlbumName
                ]
            )
            ->willReturn($queryResult);

        $repository = new PhotoAlbumRepository($db);
        $actualResult = $repository->fetchByName($requestAlbumName);
        $this->assertEquals($album, $actualResult);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function fetchByNameDataProvider(): array
    {
        return [
            'test_return_album' => [
                'Tulum',
                '%tulum%',
                [
                    'title' => 'Tulum',
                    'album_id' => '86',
                    'album_uuid' => '120f05ed-fda7-4a3b-8a4a-bbf9bb6f8211',
                    'description' => null,
                    'country_id' => '142',
                    'country_name' => 'Mexico',
                    'two_char_code' => 'MX',
                    'three_char_code' => 'MEX'
                ],
                new PhotoAlbum(
                    title: 'Tulum',
                    albumId: 86,
                    uuid: Uuid::fromString('120f05ed-fda7-4a3b-8a4a-bbf9bb6f8211'),
                    description: '',
                    country: new Country(
                        id: 142,
                        name: 'Mexico',
                        twoCharCode: 'MX',
                        threeCharCode: 'MEX'
                    )
                )
            ],
            'test_album_not_found' => [
                'Mexico',
                '%mexico%',
                null,
                null
            ],
        ];
    }

    public function testFetchByNameEmptyString(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->never())
            ->method('fetchOne');

        $repository = new PhotoAlbumRepository($db);
        $actualResult = $repository->fetchByName('');
        $this->assertNull($actualResult);
    }

    /**
     * @param null|array<string, string> $queryResult
     * @throws Exception
     */
    #[DataProvider('fetchMyFavouritesDataProvider')]
    public function testFetchMyFavourites(
        int $albumId,
        ?array $queryResult,
        ?PhotoAlbum $album
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT albums.album_id, albums.uuid AS album_uuid, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id
            WHERE albums.album_id = :albumId',
                [
                    'albumId' => $albumId
                ]
            )
            ->willReturn($queryResult);

        if ($album) {
            $db->expects($this->once())
                ->method('fetchAll')
                ->with(
                    self::PHOTO_PROPERTIES . ' WHERE photo_album.album_id = :albumId',
                    [
                        'albumId' => $albumId
                    ]
                )
                ->willReturn([]);
        }

        $repository = new PhotoAlbumRepository($db);
        $actualResult = $repository->fetchMyFavourites();
        $this->assertEquals($album, $actualResult);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function fetchMyFavouritesDataProvider(): array
    {
        return [
            'test_1' => [
                2,
                [
                    'title' => 'My Favourites',
                    'album_id' => '2',
                    'album_uuid' => '9d0a6098-8e0e-4caf-9748-175518694fe4',
                    'description' => null,
                    'country_id' => null,
                    'country_name' => null,
                    'two_char_code' => null,
                    'three_char_code' => null
                ],
                new PhotoAlbum(
                    title: 'My Favourites',
                    albumId: 2,
                    uuid: Uuid::fromString('9d0a6098-8e0e-4caf-9748-175518694fe4'),
                    description: '',
                    country: null,
                    photos: new PhotoCollection([])
                )
            ],
            'test_2' => [
                2,
                null,
                null
            ]
        ];
    }
}

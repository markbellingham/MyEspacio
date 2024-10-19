<?php

declare(strict_types=1);

namespace Photos\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoAlbumCollection;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;
use MyEspacio\Photos\Infrastructure\MySql\PhotoAlbumRepository;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PhotoAlbumRepositoryTest extends TestCase
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
        photos.uu_id,
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
        photos.uu_id,
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
                'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id
            WHERE albums.album_id = :albumId',
                [
                    'albumId' => 4
                ]
            )
            ->willReturn(
                [
                    'album_id' => '4',
                    'description' => null,
                    'title' => 'The Red Fort, Delhi',
                    'country_id' => '102',
                    'country_name' => 'India',
                    'three_char_code' => 'IND',
                    'two_char_code' => 'IN'
                ]
            );

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchById(4);

        $this->assertInstanceOf(PhotoAlbum::class, $result);
        $this->assertSame(4, $result->getAlbumId());
    }

    public function testFetchByIdNotFound(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id
            WHERE albums.album_id = :albumId',
                [
                    'albumId' => 4
                ]
            )
            ->willReturn(null);

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchById(4);
        $this->assertNull($result);
    }

    public function testFetchAll(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id',
                []
            )
            ->willReturn(
                [
                    [
                        'album_id' => '4',
                        'description' => null,
                        'title' => 'The Red Fort, Delhi',
                        'country_id' => '102',
                        'country_name' => 'India',
                        'three_char_code' => 'IND',
                        'two_char_code' => 'IN'
                    ],
                    [
                        'album_id' => '5',
                        'description' => null,
                        'title' => 'Qutab Minar, Delhi',
                        'country_id' => '102',
                        'country_name' => 'India',
                        'three_char_code' => 'IND',
                        'two_char_code' => 'IN'
                    ],
                    [
                        'album_id' => '7',
                        'description' => null,
                        'title' => 'Mumbai',
                        'country_id' => '102',
                        'country_name' => 'India',
                        'three_char_code' => 'IND',
                        'two_char_code' => 'IN'
                    ]
                ]
            );

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchAll();

        $this->assertInstanceOf(PhotoAlbumCollection::class, $result);
        $this->assertCount(3, $result);
    }

    public function testFetchAllDatabaseError(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
                countries.name AS country_name, countries.two_char_code, countries.three_char_code
            FROM pictures.albums
            LEFT JOIN pictures.countries ON albums.country_id = countries.id',
                []
            )
            ->willReturn([]);

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchAll();

        $this->assertInstanceOf(PhotoAlbumCollection::class, $result);
        $this->assertCount(0, $result);
    }

    public function testFetchAlbumPhotos(): void
    {
        $photoAlbum = new PhotoAlbum(
            title: 'MyAlbum',
            albumId: 1,
            description: 'My favourite photos',
            country: new Country(
                id: 1,
                name: 'United Kingdom',
                twoCharCode: 'GB',
                threeCharCode: 'GBR'
            )
        );

        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                self::PHOTO_PROPERTIES . ' WHERE photo_album.album_id = :albumId',
                [
                    'albumId' => 1
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
                        'fave_count' => '1'
                    ]
                ]
            );

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchAlbumPhotos($photoAlbum);

        $this->assertInstanceOf(PhotoCollection::class, $result);
        $this->assertCount(1, $result);
    }

    public function testFetchAlbumPhotosNoneFound(): void
    {
        $photoAlbum = new PhotoAlbum(
            title: 'MyAlbum',
            albumId: 1,
            description: 'My favourite photos',
            country: new Country(
                id: 1,
                name: 'United Kingdom',
                twoCharCode: 'GB',
                threeCharCode: 'GBR'
            )
        );

        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                self::PHOTO_PROPERTIES . ' WHERE photo_album.album_id = :albumId',
                [
                    'albumId' => 1
                ]
            )
            ->willReturn([]);

        $repository = new PhotoAlbumRepository($db);
        $result = $repository->fetchAlbumPhotos($photoAlbum);

        $this->assertInstanceOf(PhotoCollection::class, $result);
        $this->assertCount(0, $result);
    }

    /**
     * @dataProvider searchAlbumPhotosDataProvider
     * @param array<int, mixed> $queryTerms
     * @param array<int, mixed> $searchTerms
     * @param array<int, array<string, string>> $searchResults
     * @throws Exception
     */
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
                        'fave_count' => '1'
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
            albumId: 1,
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
     * @dataProvider fetchByNameDataProvider
     * @param null|array<string, string> $queryResult
     * @throws Exception
     */
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
                'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
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
            'test_1' => [
                'Tulum',
                '%tulum%',
                [
                    'title' => 'Tulum',
                    'album_id' => '86',
                    'description' => null,
                    'country_id' => '142',
                    'country_name' => 'Mexico',
                    'two_char_code' => 'MX',
                    'three_char_code' => 'MEX'
                ],
                new PhotoAlbum(
                    title: 'Tulum',
                    albumId: 86,
                    description: '',
                    country: new Country(
                        id: 142,
                        name: 'Mexico',
                        twoCharCode: 'MX',
                        threeCharCode: 'MEX'
                    )
                )
            ],
            'test_2' => [
                'Mexico',
                '%mexico%',
                null,
                null
            ]
        ];
    }

    /**
     * @dataProvider fetchMyFavouritesDataProvider
     * @param null|array<string, string> $queryResult
     * @throws Exception
     */
    public function testFetchMyFavourites(
        int $albumId,
        ?array $queryResult,
        ?PhotoAlbum $album
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT albums.album_id, albums.title, albums.description, albums.country_id, 
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
                    'description' => null,
                    'country_id' => null,
                    'country_name' => null,
                    'two_char_code' => null,
                    'three_char_code' => null
                ],
                new PhotoAlbum(
                    title: 'My Favourites',
                    albumId: 2,
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

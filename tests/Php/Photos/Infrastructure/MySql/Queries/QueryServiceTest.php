<?php

declare(strict_types=1);

namespace Tests\Php\Photos\Infrastructure\MySql\Queries;

use MyEspacio\Photos\Infrastructure\MySql\Queries\QueryService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QueryServiceTest extends TestCase
{
    public function testPhotoProperties(): void
    {
        $this->assertSame(
            'SELECT photos.id AS photo_id,
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
    LEFT JOIN pictures.countries ON countries.Id = photos.country_id
    LEFT JOIN pictures.geo ON photos.id = geo.photo_id
    LEFT JOIN pictures.photo_comments ON photos.id = photo_comments.photo_id
    LEFT JOIN pictures.photo_faves ON photos.id = photo_faves.photo_id
    LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id',
            QueryService::PHOTO_PROPERTIES
        );
    }

    public function testPhotoMatchProperties(): void
    {
        $this->assertSame(
            'SELECT 
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
    LEFT JOIN pictures.countries ON countries.id = photos.country_id
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
    ) AS fv ON fv.photo_id = photos.id',
            QueryService::PHOTO_MATCH_PROPERTIES
        );
    }

    /**
     * @param array<int, string> $searchTerms
     * @param array<int, string> $expectedResult
     */
    #[DataProvider('preparedSearchTermsDataProvider')]
    public function testPrepareSearchTerms(
        array $searchTerms,
        array $expectedResult
    ): void {
        $actualResult = QueryService::prepare($searchTerms);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array<string, array<int, array<int, string>>>
     */
    public static function preparedSearchTermsDataProvider(): array
    {
        return [
            'test_1' => [
                [],
                []
            ],
            'test_2' => [
                ['sunset'],
                ['sunset*']
            ],
            'test_3' => [
                ['mexico', 'sunset'],
                ['mexico*', 'sunset*']
            ],
            'test_4' => [
                ['', 'sunset'],
                ['sunset*']
            ],
            'test_5' => [
                ['','',''],
                []
            ],
            'test_6' => [
                ['12','34','ab','cd'],
                []
            ],
            'test_7' => [
                ['ab','valid','cd'],
                ['valid*']
            ],
            'test_8' => [
                ['hello!', '@world$', '%foo#'],
                ['hello*', 'world*', 'foo*']
            ],
            'test_9' => [
                [' ', ' sunset '],
                ['sunset*']
            ],
            'test_10' => [
                ['@12', 'world+'],
                ['world*']
            ]
        ];
    }
}

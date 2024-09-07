<?php

declare(strict_types=1);

namespace Tests\Photos\Infrastructure\MySql\Queries;

use MyEspacio\Photos\Infrastructure\MySql\Queries\QueryService;
use PHPUnit\Framework\TestCase;

final class QueryServiceTest extends TestCase
{
    public function testPhotoProperties(): void
    {
        $this->assertEquals(
            'SELECT photos.id AS photo_id,
        photos.date_taken,
        photos.description,
        photos.directory,
        photos.filename,
        photos.title,
        photos.town,
        photos.height,
        photos.width,
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
    LEFT JOIN pictures.photo_album ON photos.id = photo_album.photo_id',
            QueryService::PHOTO_PROPERTIES
        );
    }

    public function testPhotoMatchProperties(): void
    {
        $this->assertEquals(
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
        MATCH(photos.title, photos.description, photos.town) AGAINST(:searchTerm) AS pscore,
        MATCH(countries.name) AGAINST(:searchTerm) AS cscore
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
    ) AS fv ON fv.photo_id = photos.id',
            QueryService::PHOTO_MATCH_PROPERTIES
        );
    }

    /**
     * @param array<int, string> $searchTerms
     * @dataProvider preparedSearchTermsDataProvider
     */
    public function testPrepareSearchTerms(
        array $searchTerms,
        ?string $expectedResult
    ): void {
        $actualResult = QueryService::prepareSearchTerms($searchTerms);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array<string, mixed>
     */
    public static function preparedSearchTermsDataProvider(): array
    {
        return [
            'test_1' => [
                [],
                null
            ],
            'test_2' => [
                ['sunset'],
                '+sunset*'
            ],
            'test_3' => [
                ['mexico', 'sunset'],
                '+mexico* +sunset*'
            ],
            'test_4' => [
                ['', 'sunset'],
                '+sunset*'
            ],
            'test_5' => [
                ['','',''],
                null
            ],
            'test_6' => [
                ['12','34','ab','cd'],
                null
            ],
            'test_7' => [
                ['ab','valid','cd'],
                '+valid*'
            ],
            'test_8' => [
                ['hello!', '@world$', '%foo#'],
                '+hello* +world* +foo*'
            ],
            'test_9' => [
                [' ', ' sunset '],
                '+sunset*'
            ],
            'test_10' => [
                ['@12', 'world+'],
                '+world*'
            ]
        ];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Php\Php\Photos\Domain\Entity;

use DateTimeImmutable;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\Relevance;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PhotoTest extends TestCase
{
    /** @param array<string, mixed> $jsonSerialized */
    #[DataProvider('modelDataProvider')]
    public function testModel(
        Country $country,
        GeoCoordinates $geo,
        Dimensions $dimensions,
        Relevance $relevance,
        UuidInterface $uuid,
        DateTimeImmutable $dateTaken,
        ?string $description,
        string $directory,
        string $filename,
        int $id,
        string $title,
        string $town,
        ?int $commentCount,
        ?int $faveCount,
        array $jsonSerialized
    ): void {
        $photo = new Photo(
            $country,
            $geo,
            $dimensions,
            $relevance,
            $uuid,
            $dateTaken,
            $description,
            $directory,
            $filename,
            $id,
            $title,
            $town,
            $commentCount,
            $faveCount,
        );

        $this->assertSame($country, $photo->getCountry());
        $this->assertSame($geo, $photo->getGeoCoordinates());
        $this->assertSame($dimensions, $photo->getDimensions());
        $this->assertSame($relevance, $photo->getRelevance());
        $this->assertSame($uuid, $photo->getUuid());
        $this->assertSame($dateTaken, $photo->getDateTaken());
        $this->assertSame($description, $photo->getDescription());
        $this->assertSame($directory, $photo->getDirectory());
        $this->assertSame($filename, $photo->getFilename());
        $this->assertSame($id, $photo->getId());
        $this->assertSame($title, $photo->getTitle());
        $this->assertSame($town, $photo->getTown());
        $this->assertSame($commentCount, $photo->getCommentCount());
        $this->assertSame($faveCount, $photo->getFaveCount());

        $this->assertEquals($jsonSerialized, $photo->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                'country' => new Country(
                    id: 45,
                    name:'Chile',
                    twoCharCode: 'CL',
                    threeCharCode: 'CHL'
                ),
                'geo' => new GeoCoordinates(
                    id: 2559,
                    photoUuid: Uuid::fromString('8d7fb4b9-b496-478b-bd9e-14dc30a1ca71'),
                    latitude: -33438084,
                    longitude: -33438084,
                    accuracy:  16
                ),
                'dimensions' => new Dimensions(
                    width: 456,
                    height: 123
                ),
                'relevance' => new Relevance(
                    cScore: 4,
                    pScore: 5
                ),
                'uuid' => Uuid::fromString('8d7fb4b9-b496-478b-bd9e-14dc30a1ca71'),
                'dateTaken' => new DateTimeImmutable("2012-10-21"),
                'description' => "Note the spurs...",
                'directory' => "RTW Trip\/16Chile\/03 - Valparaiso",
                'filename' => "P1070237.JPG",
                'id' => 2689,
                'title' => "Getting ready to dance",
                'town' => "Valparaiso",
                'commentCount' => 1,
                'faveCount' => 1,
                'jsonSerialized' => [
                    'country' => [
                        'name' => 'Chile',
                        'twoCharCode' => 'CL',
                        'threeCharCode' => 'CHL',
                    ],
                    'geoCoordinates' => [
                        'latitude' => -33438084,
                        'longitude' => -33438084,
                        'accuracy' => 16,
                    ],
                    'dimensions' => [
                        'width' => 456,
                        'height' => 123,
                    ],
                    'relevance' => [
                        'cScore' => 4,
                        'pScore' => 5,
                    ],
                    'dateTaken' => '2012-10-21T00:00:00+00:00',
                    'description' => 'Note the spurs...',
                    'title' => 'Getting ready to dance',
                    'town' => 'Valparaiso',
                    'commentCount' => 1,
                    'faveCount' => 1,
                    'photoUuid' => '8d7fb4b9-b496-478b-bd9e-14dc30a1ca71'
                ],
            ],
            'test_2' => [
                'country' => new Country(
                    id: 33,
                    name: 'France',
                    twoCharCode: 'FR',
                    threeCharCode: 'FRA'
                ),
                'geo' => new GeoCoordinates(
                    id: 3120,
                    photoUuid: Uuid::fromString('c57d1b9e-ff5b-4f4d-89ab-21a3f1d9a481'),
                    latitude: 48563210,
                    longitude: 2317890,
                    accuracy: 12
                ),
                'dimensions' => new Dimensions(
                    width: 1920,
                    height: 1080
                ),
                'relevance' => new Relevance(
                    cScore: 7,
                    pScore: 9
                ),
                'uuid' => Uuid::fromString('c57d1b9e-ff5b-4f4d-89ab-21a3f1d9a481'),
                'dateTaken' => new DateTimeImmutable("2018-05-14"),
                'description' => "Evening light over the Eiffel Tower.",
                'directory' => "Europe Trip\/France\/Paris",
                'filename' => "IMG_4521.JPG",
                'id' => 3791,
                'title' => "Paris at sunset",
                'town' => "Paris",
                'commentCount' => 3,
                'faveCount' => 5,
                'jsonSerialized' => [
                    'country' => [
                        'name' => 'France',
                        'twoCharCode' => 'FR',
                        'threeCharCode' => 'FRA',
                    ],
                    'geoCoordinates' => [
                        'latitude' => 48563210,
                        'longitude' => 2317890,
                        'accuracy' => 12,
                    ],
                    'dimensions' => [
                        'width' => 1920,
                        'height' => 1080,
                    ],
                    'relevance' => [
                        'cScore' => 7,
                        'pScore' => 9,
                    ],
                    'dateTaken' => '2018-05-14T00:00:00+00:00',
                    'description' => 'Evening light over the Eiffel Tower.',
                    'title' => 'Paris at sunset',
                    'town' => 'Paris',
                    'commentCount' => 3,
                    'faveCount' => 5,
                    'photoUuid' => 'c57d1b9e-ff5b-4f4d-89ab-21a3f1d9a481'
                ],
            ],
            'test_null' => [
                'country' => new Country(
                    id: 81,
                    name: 'Japan',
                    twoCharCode: 'JP',
                    threeCharCode: 'JPN'
                ),
                'geo' => new GeoCoordinates(
                    id: 4278,
                    photoUuid: Uuid::fromString('a83ef6c2-70b2-49fc-a6e4-00f2dc6f4e93'),
                    latitude: 35468972,
                    longitude: 139691706,
                    accuracy: 10
                ),
                'dimensions' => new Dimensions(
                    width: 4000,
                    height: 3000
                ),
                'relevance' => new Relevance(
                    cScore: 8,
                    pScore: 6
                ),
                'uuid' => Uuid::fromString('a83ef6c2-70b2-49fc-a6e4-00f2dc6f4e93'),
                'dateTaken' => new DateTimeImmutable("2020-03-28"),
                'description' => null,
                'directory' => "Asia Trip\/Japan\/Tokyo",
                'filename' => "DSC_1024.JPG",
                'id' => 4821,
                'title' => "Cherry blossoms in full bloom",
                'town' => "Tokyo",
                'commentCount' => null,
                'faveCount' => null,
                'jsonSerialized' => [
                    'country' => [
                        'name' => 'Japan',
                        'twoCharCode' => 'JP',
                        'threeCharCode' => 'JPN',
                    ],
                    'geoCoordinates' => [
                        'latitude' => 35468972,
                        'longitude' => 139691706,
                        'accuracy' => 10,
                    ],
                    'dimensions' => [
                        'width' => 4000,
                        'height' => 3000,
                    ],
                    'relevance' => [
                        'cScore' => 8,
                        'pScore' => 6,
                    ],
                    'dateTaken' => '2020-03-28T00:00:00+00:00',
                    'description' => null,
                    'title' => 'Cherry blossoms in full bloom',
                    'town' => 'Tokyo',
                    'commentCount' => null,
                    'faveCount' => null,
                    'photoUuid' => 'a83ef6c2-70b2-49fc-a6e4-00f2dc6f4e93'
                ],
            ],
        ];
    }

    #[DataProvider('createFromDataSetDataProvider')]
    public function testCreateFromDataset(
        DataSet $dataset,
        Photo $expectedModel,
    ): void {
        $photo = Photo::createFromDataSet($dataset);
        $this->assertEquals($expectedModel, $photo);
        $this->assertInstanceOf(Photo::class, $photo);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDataSetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataset' => new DataSet([
                    'country_id' => '45',
                    'country_name' => 'Chile',
                    'two_char_code' => 'CL',
                    'three_char_code' => 'CHL',
                    'geo_id' => '2559',
                    'photo_uuid' => '8d7fb4b9-b496-478b-bd9e-14dc30a1ca71',
                    'photo_id' => '2689',
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
                ]),
                'expectedModel' => new Photo(
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
                    id: 2689,
                    title: "Getting ready to dance",
                    town: "Valparaiso",
                    commentCount: 1,
                    faveCount: 1,
                ),
            ],
            'test_null' => [
                'dataset' => new DataSet([
                    'country_id' => '81',
                    'country_name' => 'Japan',
                    'two_char_code' => 'JP',
                    'three_char_code' => 'JPN',
                    'geo_id' => '9087',
                    'photo_uuid' => '9f4f40d6-7c72-44e6-91d4-3e6ec43e8b27',
                    'photo_id' => '7842',
                    'latitude' => '35689543',
                    'longitude' => '139691706',
                    'accuracy' => '8',
                    'width' => '4000',
                    'height' => '3000',
                    'cscore' => '6',
                    'pscore' => '8',
                    'date_taken' => "2018-04-05",
                    'description' => null,
                    'directory' => "Asia Trip/Japan/Tokyo",
                    'filename' => "DSC_0451.JPG",
                    'title' => "Cherry Blossoms in Tokyo",
                    'town' => "Tokyo",
                    'comment_count' => null,
                    'fave_count' => null
                ]),
                'expectedModel' => new Photo(
                    country: new Country(
                        id: 81,
                        name: 'Japan',
                        twoCharCode: 'JP',
                        threeCharCode: 'JPN'
                    ),
                    geoCoordinates: new GeoCoordinates(
                        id: 9087,
                        photoUuid: Uuid::fromString('9f4f40d6-7c72-44e6-91d4-3e6ec43e8b27'),
                        latitude: 35689543,
                        longitude: 139691706,
                        accuracy: 8,
                    ),
                    dimensions: new Dimensions(
                        width: 4000,
                        height: 3000,
                    ),
                    relevance: new Relevance(
                        cScore: 6,
                        pScore: 8
                    ),
                    uuid: Uuid::fromString('9f4f40d6-7c72-44e6-91d4-3e6ec43e8b27'),
                    dateTaken: new DateTimeImmutable("2018-04-05"),
                    description: null,
                    directory: "Asia Trip/Japan/Tokyo",
                    filename: "DSC_0451.JPG",
                    id: 7842,
                    title: "Cherry Blossoms in Tokyo",
                    town: "Tokyo",
                    commentCount: null,
                    faveCount: null,
                ),
            ],
        ];
    }
}

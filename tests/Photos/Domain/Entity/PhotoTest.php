<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use DateTimeImmutable;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\Relevance;
use PHPUnit\Framework\TestCase;

final class PhotoTest extends TestCase
{
    public function testPhoto(): void
    {
        $country = new Country(
            id: 45,
            name:'Chile',
            twoCharCode: 'CL',
            threeCharCode: 'CHL'
        );
        $geo = new GeoCoordinates(
            id: 2559,
            photoId: 2559,
            latitude: -33438084,
            longitude: -33438084,
            accuracy:  16
        );
        $dimensions = new Dimensions(
            width: 456,
            height: 123
        );
        $relevance = new Relevance(
            cScore: 4,
            pScore: 5
        );
        $photo = new Photo(
            country: $country,
            geoCoordinates: $geo,
            dimensions: $dimensions,
            relevance: $relevance,
            dateTaken: DateTimeImmutable::createFromFormat('Y-m-d', "2012-10-21"),
            description: "Note the spurs...",
            directory: "RTW Trip\/16Chile\/03 - Valparaiso",
            filename: "P1070237.JPG",
            id: 2689,
            title: "Getting ready to dance",
            town: "Valparaiso",
            commentCount: 1,
            faveCount: 1
        );

        $this->assertInstanceOf(Country::class, $photo->getCountry());
        $this->assertInstanceOf(GeoCoordinates::class, $photo->getGeoCoordinates());
        $this->assertInstanceOf(Dimensions::class, $photo->getDimensions());
        $this->assertInstanceOf(Relevance::class, $photo->getRelevance());
        $this->assertInstanceOf(DateTimeImmutable::class, $photo->getDateTaken());
        $this->assertEquals("Note the spurs...", $photo->getDescription());
        $this->assertEquals("RTW Trip\/16Chile\/03 - Valparaiso", $photo->getDirectory());
        $this->assertEquals("P1070237.JPG", $photo->getFilename());
        $this->assertSame(2689, $photo->getId());
        $this->assertEquals("Getting ready to dance", $photo->getTitle());
        $this->assertEquals("Valparaiso", $photo->getTown());
        $this->assertSame(1, $photo->getCommentCount());
        $this->assertSame(1, $photo->getFaveCount());
    }

    public function testJsonSerialize(): void
    {
        $country = new Country(
            id: 45,
            name:'Chile',
            twoCharCode: 'CL',
            threeCharCode: 'CHL'
        );
        $geo = new GeoCoordinates(
            id: 2559,
            photoId: 2689,
            latitude: -33438084,
            longitude: -33438084,
            accuracy:  16
        );
        $dimensions = new Dimensions(
            width: 456,
            height: 123
        );
        $relevance = new Relevance(
            cScore: 4,
            pScore: 5
        );
        $photo = new Photo(
            country: $country,
            geoCoordinates: $geo,
            dimensions: $dimensions,
            relevance: $relevance,
            dateTaken: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', "2012-10-21 00:00:00"),
            description: "Note the spurs...",
            directory: "RTW Trip\/16Chile\/03 - Valparaiso",
            filename: "P1070237.JPG",
            id: 2689,
            title: "Getting ready to dance",
            town: "Valparaiso",
            commentCount: 1,
            faveCount: 1
        );

        $jsonData = [
            'country' => [
                'name' => 'Chile',
                'twoCharCode' => 'CL',
                'threeCharCode' => 'CHL',
            ],
            'geoCoordinates' => [
                'id' => 2559,
                'photoId' => 2689,
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
            'dateTaken' => '2012-10-21 00:00:00',
            'description' => 'Note the spurs...',
            'directory' => 'RTW Trip\/16Chile\/03 - Valparaiso',
            'filename' => 'P1070237.JPG',
            'id' => 2689,
            'title' => 'Getting ready to dance',
            'town' => 'Valparaiso',
            'commentCount' => 1,
            'faveCount' => 1,
        ];

        $this->assertEquals(
            $jsonData,
            json_decode(json_encode($photo), true)
        );
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'country_id' => '45',
            'country_name' => 'Chile',
            'two_char_code' => 'CL',
            'three_char_code' => 'CHL',
            'geo_id' => '2559',
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
        ]);

        $photo = Photo::createFromDataSet($data);
        $this->assertInstanceOf(Photo::class, $photo);
        $this->assertInstanceOf(Country::class, $photo->getCountry());
        $this->assertInstanceOf(GeoCoordinates::class, $photo->getGeoCoordinates());
        $this->assertInstanceOf(Dimensions::class, $photo->getDimensions());
        $this->assertInstanceOf(Relevance::class, $photo->getRelevance());
        $this->assertInstanceOf(DateTimeImmutable::class, $photo->getDateTaken());
        $this->assertEquals('2012-10-21 00:00:00', $photo->getDateTakenString());
        $this->assertEquals("Note the spurs...", $photo->getDescription());
        $this->assertEquals("RTW Trip\/16Chile\/03 - Valparaiso", $photo->getDirectory());
        $this->assertEquals("P1070237.JPG", $photo->getFilename());
        $this->assertSame(2689, $photo->getId());
        $this->assertEquals("Getting ready to dance", $photo->getTitle());
        $this->assertEquals("Valparaiso", $photo->getTown());
        $this->assertSame(1, $photo->getCommentCount());
        $this->assertSame(1, $photo->getFaveCount());
    }
}

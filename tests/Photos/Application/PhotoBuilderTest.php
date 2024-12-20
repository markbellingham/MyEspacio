<?php

declare(strict_types=1);

namespace Tests\Photos\Application;

use DateTimeImmutable;
use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Application\PhotoBuilder;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\Relevance;
use PHPUnit\Framework\TestCase;

final class PhotoBuilderTest extends TestCase
{
    public function testPhotoBuilder(): void
    {
        $dataset = new DataSet([
            'country_id' => '45',
            'country_name' => 'Chile',
            'two_char_code' => 'CL',
            'three_char_code' => 'CHL',
            'geo_id' => '2559',
            'photo_uuid' => '39fa7943-6fa7-4412-97c8-c6cec6a44e0b',
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
            'uuid' => '39fa7943-6fa7-4412-97c8-c6cec6a44e0b'
        ]);

        $builder = new PhotoBuilder($dataset);
        $photo = $builder->build();

        $this->assertInstanceOf(Photo::class, $photo);

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
}

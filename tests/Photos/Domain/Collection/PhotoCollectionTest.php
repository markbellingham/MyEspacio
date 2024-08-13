<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Collection;

use MyEspacio\Framework\Exceptions\CollectionException;
use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\Photo;
use PHPUnit\Framework\TestCase;

final class PhotoCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $data = [
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
        ];

        $collection = new PhotoCollection($data);

        $this->assertCount(1, $collection);
        foreach ($collection as $photo) {
            $this->assertInstanceOf(Photo::class, $photo);
        }
    }

    public function testCollectionEmpty(): void
    {
        $data = [];
        $collection = new PhotoCollection($data);

        $this->assertCount(0, $collection);
    }

    public function testRequiredKeys(): void
    {
        $data = [
            [
                'bad_key' => 'Bad Value'
            ]
        ];

        $exceptionMessage = 'Missing required keys: country_id, country_name, two_char_code, three_char_code, geo_id, ' .
            'latitude, longitude, accuracy, width, height, date_taken, description, directory, filename, photo_id, title, ' .
            'town, comment_count, fave_count';

        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new PhotoCollection($data);
    }
}
<?php

declare(strict_types=1);

namespace Tests\Photos\Infrastructure\MySql;

use Monolog\DateTimeImmutable;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Photos\Domain\Collection\PhotoTagCollection;
use MyEspacio\Photos\Domain\Entity\Country;
use MyEspacio\Photos\Domain\Entity\Dimensions;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use MyEspacio\Photos\Domain\Entity\Photo;
use MyEspacio\Photos\Domain\Entity\Relevance;
use MyEspacio\Photos\Infrastructure\MySql\PhotoTagRepository;
use PHPUnit\Framework\TestCase;

final class PhotoTagRepositoryTest extends TestCase
{
    private Photo $photo;

    protected function setUp(): void
    {
        parent::setUp();
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
        $this->photo = new Photo(
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
    }

    public function testGetPhotoTags(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT
                tags.id,
                tags.tag,
                photo_tags.photo_id
            FROM project.tags
            LEFT JOIN pictures.photo_tags ON tags.id = photo_tags.tag_id
            WHERE photo_tags.photo_id = :photoId',
                [
                    'photoId' => 2689
                ]
            )
            ->willReturn(
                [
                    [
                        'photo_id' => 2689,
                        'tag' => 'sunset',
                        'id' => 1
                    ],
                    [
                        'photo_id' => 2689,
                        'tag' => 'mexico',
                        'id' => 2
                    ]
                ]
            );

        $repository = new PhotoTagRepository($db);
        $result = $repository->getPhotoTags($this->photo);

        $this->assertInstanceOf(PhotoTagCollection::class, $result);
        $this->assertCount(2, $result);
    }

    public function testGetPhotoTagsFail(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT
                tags.id,
                tags.tag,
                photo_tags.photo_id
            FROM project.tags
            LEFT JOIN pictures.photo_tags ON tags.id = photo_tags.tag_id
            WHERE photo_tags.photo_id = :photoId',
                [
                    'photoId' => 2689
                ]
            )
            ->willReturn([]);

        $repository = new PhotoTagRepository($db);
        $result = $repository->getPhotoTags($this->photo);

        $this->assertInstanceOf(PhotoTagCollection::class, $result);
        $this->assertCount(0, $result);
    }
}

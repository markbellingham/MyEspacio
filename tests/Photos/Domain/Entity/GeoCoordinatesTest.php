<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class GeoCoordinatesTest extends TestCase
{
    /** @param array<string, int> $jsonSerialized */
    #[DataProvider('geoCoordinatesDataProvider')]
    public function testGeoCoordinates(
        int $id,
        UuidInterface $photoUuid,
        int $latitude,
        int $longitude,
        int $accuracy,
        array $jsonSerialized,
    ): void {
        $geo = new GeoCoordinates(
            $id,
            $photoUuid,
            $latitude,
            $longitude,
            $accuracy
        );

        $this->assertSame($id, $geo->getId());
        $this->assertSame($photoUuid, $geo->getPhotoUuid());
        $this->assertSame($latitude, $geo->getLatitude());
        $this->assertSame($longitude, $geo->getLongitude());
        $this->assertSame($accuracy, $geo->getAccuracy());
        $this->assertEquals($jsonSerialized, $geo->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function geoCoordinatesDataProvider(): array
    {
        return [
            'test_1' => [
                'id' => 17,
                'photoUuid' => Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
                'latitude' => 32240065,
                'longitude' => 77187860,
                'accuracy' => 16,
                'jsonSerialized' => [
                    'latitude' => 32240065,
                    'longitude' => 77187860,
                    'accuracy' => 16,
                ],
            ],
            'test_2' => [
                'id' => 31,
                'photoUuid' => Uuid::fromString('aa91e0a8-31c7-4ad8-8fa7-1bec70a80d82'),
                'latitude' => 32309405,
                'longitude' => 77175694,
                'accuracy' => 14,
                'jsonSerialized' => [
                    'latitude' => 32309405,
                    'longitude' => 77175694,
                    'accuracy' => 14,
                ]
            ]
        ];
    }

    #[DataProvider('createFromDatasetDataProvider')]
    public function testCreateFromDataset(
        DataSet $dataset,
        GeoCoordinates $expectedModel,
    ): void {
        $geo = GeoCoordinates::createFromDataSet($dataset);
        $this->assertEquals($expectedModel, $geo);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDatasetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataset' => new DataSet([
                    'geo_id' => '17',
                    'photo_uuid' => '95e7a3b0-6b8a-41bc-bbe2-4efcea215aea',
                    'latitude' => '32240065',
                    'longitude' => '77187860',
                    'accuracy' => '16',
                ]),
                'expectedModel' => new GeoCoordinates(
                    id: 17,
                    photoUuid: Uuid::fromString('95e7a3b0-6b8a-41bc-bbe2-4efcea215aea'),
                    latitude: 32240065,
                    longitude: 77187860,
                    accuracy: 16,
                ),
            ],
            'test_2' => [
                'dataset' => new DataSet([
                    'geo_id' => '31',
                    'photo_uuid' => 'aa91e0a8-31c7-4ad8-8fa7-1bec70a80d82',
                    'latitude' => '32309405',
                    'longitude' => '77175694',
                    'accuracy' => '14',
                ]),
                'expectedModel' => new GeoCoordinates(
                    id: 31,
                    photoUuid: Uuid::fromString('aa91e0a8-31c7-4ad8-8fa7-1bec70a80d82'),
                    latitude: 32309405,
                    longitude: 77175694,
                    accuracy: 14,
                ),
            ],
        ];
    }
}

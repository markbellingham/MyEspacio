<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\GeoCoordinates;
use PHPUnit\Framework\TestCase;

final class GeoCoordinatesTest extends TestCase
{
    public function testGeoCoordinates(): void
    {
        $geo = new GeoCoordinates(
            id: 17,
            photoId: 17,
            latitude: 32240065,
            longitude: 77187860,
            accuracy:  16
        );

        $this->assertSame(17, $geo->getId());
        $this->assertSame(17, $geo->getPhotoId());
        $this->assertSame(32240065, $geo->getLatitude());
        $this->assertSame(77187860, $geo->getLongitude());
        $this->assertSame(16, $geo->getAccuracy());
        $this->assertEquals(
            [
                'id' => 17,
                'photoId' => 17,
                'latitude' => 32240065,
                'longitude' => 77187860,
                'accuracy' => 16
            ],
            $geo->jsonSerialize()
        );
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'geo_id' => '17',
            'photo_id' => '17',
            'latitude' => '32240065',
            'longitude' => '77187860',
            'accuracy' => '16'
        ]);

        $geo = GeoCoordinates::createFromDataSet($data);
        $this->assertInstanceOf(GeoCoordinates::class, $geo);
        $this->assertSame(17, $geo->getId());
        $this->assertSame(17, $geo->getPhotoId());
        $this->assertSame(32240065, $geo->getLatitude());
        $this->assertSame(77187860, $geo->getLongitude());
        $this->assertSame(16, $geo->getAccuracy());
    }
}

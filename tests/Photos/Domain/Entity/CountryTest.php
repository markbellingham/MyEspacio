<?php

declare(strict_types=1);

namespace Tests\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Country;
use PHPUnit\Framework\TestCase;

final class CountryTest extends TestCase
{
    public function testCountry(): void
    {
        $country = new Country(
            id: 1,
            name: 'United Kingdom',
            twoCharCode: 'GB',
            threeCharCode: 'GBR'
        );

        $this->assertSame(1, $country->getId());
        $this->assertEquals('United Kingdom', $country->getName());
        $this->assertEquals('GB', $country->getTwoCharCode());
        $this->assertEquals('GBR', $country->getThreeCharCode());
        $this->assertEquals(
            [
                'id' => 1,
                'name' => 'United Kingdom',
                'twoCharCode' => 'GB',
                'threeCharCode' => 'GBR'
            ],
            $country->jsonSerialize()
        );
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'country_id' => '1',
            'country_name' => 'United Kingdom',
            'two_char_code' => 'GB',
            'three_char_code' => 'GBR'
        ]);

        $country = Country::createFromDataSet($data);
        $this->assertInstanceOf(Country::class, $country);
        $this->assertSame(1, $country->getId());
        $this->assertEquals('United Kingdom', $country->getName());
        $this->assertEquals('GB', $country->getTwoCharCode());
        $this->assertEquals('GBR', $country->getThreeCharCode());
    }
}

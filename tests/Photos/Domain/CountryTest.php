<?php

declare(strict_types=1);

namespace Tests\Photos\Domain;

use MyEspacio\Photos\Domain\Country;
use PHPUnit\Framework\TestCase;

final class CountryTest extends TestCase
{
    public function testCountry(): void
    {
        $country = new Country(
            id: 1,
            name:'United Kingdom',
            twoCharCode: 'GB',
            threeCharCode: 'GBR'
        );

        $this->assertSame(1, $country->getId());
        $this->assertEquals('United Kingdom', $country->getName());
        $this->assertEquals('GB', $country->getTwoCharCode());
        $this->assertEquals('GBR', $country->getThreeCharCode());
    }
}

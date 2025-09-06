<?php

declare(strict_types=1);

namespace Tests\Php\Php\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
use MyEspacio\Photos\Domain\Entity\Country;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CountryTest extends TestCase
{
    /** @param array<string, string> $jsonSerialized */
    #[DataProvider('countryDataProvider')]
    public function testCountry(
        int $id,
        string $name,
        string $twoCharCode,
        string $threeCharCode,
        array $jsonSerialized,
    ): void {
        $country = new Country(
            $id,
            $name,
            $twoCharCode,
            $threeCharCode
        );

        $this->assertSame($id, $country->getId());
        $this->assertSame($name, $country->getName());
        $this->assertSame($twoCharCode, $country->getTwoCharCode());
        $this->assertSame($threeCharCode, $country->getThreeCharCode());
        $this->assertSame($jsonSerialized, $country->jsonSerialize());
    }

    /** @return array<int, array<string, mixed>> */
    public static function countryDataProvider(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'United Kingdom',
                'twoCharCode' => 'GB',
                'threeCharCode' => 'GBR',
                'jsonSerialized' => [
                    'name' => 'United Kingdom',
                    'twoCharCode' => 'GB',
                    'threeCharCode' => 'GBR'
                ],
            ],
            [
                'id' => 2,
                'name' => 'United States',
                'twoCharCode' => 'US',
                'threeCharCode' => 'USA',
                'jsonSerialized' => [
                    'name' => 'United States',
                    'twoCharCode' => 'US',
                    'threeCharCode' => 'USA',
                ],
            ],
        ];
    }

    #[DataProvider('createFromDatasetDataProvider')]
    public function testCreateFromDataset(
        DataSet $dataset,
        Country $expectedCountry,
    ): void {
        $actualCountry = Country::createFromDataSet($dataset);
        $this->assertEquals($expectedCountry, $actualCountry);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDatasetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataset' => new DataSet([
                    'country_id' => 1,
                    'country_name' => 'United Kingdom',
                    'two_char_code' => 'GB',
                    'three_char_code' => 'GBR'
                ]),
                'expectedCountry' => new Country(
                    id: 1,
                    name: 'United Kingdom',
                    twoCharCode: 'GB',
                    threeCharCode: 'GBR'
                ),
            ],
            'test_2' => [
                'dataset' => new DataSet([
                    'country_id' => 2,
                    'country_name' => 'United States',
                    'two_char_code' => 'US',
                    'three_char_code' => 'USA',
                ]),
                'expectedCountry' => new Country(
                    id: 2,
                    name: 'United States',
                    twoCharCode: 'US',
                    threeCharCode: 'USA',
                ),
            ],
        ];
    }
}

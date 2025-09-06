<?php

declare(strict_types=1);

namespace Tests\Php\Php\Common\Domain\Entity;

use MyEspacio\Common\Domain\Entity\CaptchaIcon;
use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CaptchaIconTest extends TestCase
{
    /** @param array<string, string> $jsonSerialized */
    #[DataProvider('modelDataProvider')]
    public function testModel(
        int $iconId,
        string $icon,
        string $name,
        string $colour,
        array $jsonSerialized,
    ): void {
        $model = new CaptchaIcon(
            iconId: $iconId,
            icon: $icon,
            name: $name,
            colour: $colour
        );

        $this->assertSame($iconId, $model->getIconId());
        $this->assertSame($icon, $model->getIcon());
        $this->assertSame($name, $model->getName());
        $this->assertSame($colour, $model->getColour());
        $this->assertEquals($jsonSerialized, $model->jsonSerialize());
    }

    /** @return array<string, array<string, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                'iconId' => 1,
                'icon' => '<i class="bi bi-phone-vibrate"></i>',
                'name' => 'Mobile',
                'colour' => 'btn-warning',
                'jsonSerialized' => [
                    'name' => 'Mobile',
                ],
            ],
            'test_2' => [
                'iconId' => 2,
                'icon' => '<i class="bi bi-keyboard"></i>',
                'name' => 'Keyboard',
                'colour' => 'btn-warning',
                'jsonSerialized' => [
                    'name' => 'Keyboard',
                ],
            ],
        ];
    }

    public function testCaptchaIconSetters(): void
    {
        $icon = new CaptchaIcon(
            iconId: 0,
            icon: '',
            name: '',
            colour: ''
        );

        $this->assertSame(0, $icon->getIconId());
        $this->assertSame('', $icon->getIcon());
        $this->assertSame('', $icon->getName());
        $this->assertSame('', $icon->getColour());

        $icon->setIconId(1);
        $icon->setIcon('<i class="bi bi-phone-vibrate"></i>');
        $icon->setName('Mobile');
        $icon->setColour('btn-warning');

        $this->assertEquals(1, $icon->getIconId());
        $this->assertEquals('<i class="bi bi-phone-vibrate"></i>', $icon->getIcon());
        $this->assertEquals('Mobile', $icon->getName());
        $this->assertEquals('btn-warning', $icon->getColour());
    }

    public function testBuildFromDataset(): void
    {
        $data = new DataSet([
            'icon_id' => 1,
            'icon' => '<i class="bi bi-phone-vibrate"></i>',
            'name' => 'Mobile',
            'colour' => 'btn-warning'
        ]);

        $icon = CaptchaIcon::createFromDataSet($data);
        $this->assertInstanceOf(CaptchaIcon::class, $icon);
        $this->assertSame(1, $icon->getIconId());
        $this->assertEquals('<i class="bi bi-phone-vibrate"></i>', $icon->getIcon());
        $this->assertEquals('Mobile', $icon->getName());
        $this->assertEquals('btn-warning', $icon->getColour());
    }

    public function testCreateFromDatasetFail(): void
    {
        $data = new DataSet([
            'bad_key' => null
        ]);
        $icon = CaptchaIcon::createFromDataSet($data);
        $this->assertInstanceOf(CaptchaIcon::class, $icon);
        $this->assertSame(0, $icon->getIconId());
        $this->assertEquals('', $icon->getIcon());
        $this->assertEquals('', $icon->getName());
        $this->assertEquals('', $icon->getColour());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Common\Domain\Entity;

use MyEspacio\Common\Domain\Entity\CaptchaIcon;
use MyEspacio\Framework\DataSet;
use PHPUnit\Framework\TestCase;

final class CaptchaIconTest extends TestCase
{
    public function testCaptchaIcon(): void
    {
        $icon = new CaptchaIcon(
            iconId: 1,
            icon: '<i class="bi bi-phone-vibrate"></i>',
            name: 'Mobile',
            colour: 'btn-warning'
        );

        $this->assertEquals(1, $icon->getIconId());
        $this->assertEquals('<i class="bi bi-phone-vibrate"></i>', $icon->getIcon());
        $this->assertEquals('Mobile', $icon->getName());
        $this->assertEquals('btn-warning', $icon->getColour());
        $this->assertEquals(
            [
                'name' => 'Mobile'
            ],
            $icon->jsonSerialize()
        );
    }

    public function testCaptchaIconDefaults(): void
    {
        $icon = new CaptchaIcon();

        $this->assertEquals(null, $icon->getIconId());
        $this->assertEquals('', $icon->getIcon());
        $this->assertEquals('', $icon->getName());
        $this->assertEquals('', $icon->getColour());
    }

    public function testCaptchaIconNull(): void
    {
        $icon = new CaptchaIcon(
            iconId: null,
            icon: null,
            name: null,
            colour: null
        );

        $this->assertNull($icon->getIconId());
        $this->assertNull($icon->getIcon());
        $this->assertNull($icon->getName());
        $this->assertNull($icon->getColour());
    }

    public function testCaptchaIconSetters(): void
    {
        $icon = new CaptchaIcon(
            iconId: null,
            icon: null,
            name: null,
            colour: null
        );

        $this->assertNull($icon->getIconId());
        $this->assertNull($icon->getIcon());
        $this->assertNull($icon->getName());
        $this->assertNull($icon->getColour());

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

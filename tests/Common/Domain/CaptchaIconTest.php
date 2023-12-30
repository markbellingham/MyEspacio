<?php

declare(strict_types=1);

namespace Tests\Common\Domain;

use MyEspacio\Common\Domain\CaptchaIcon;
use PHPUnit\Framework\TestCase;

final class CaptchaIconTest extends TestCase
{
    public function testCaptchaIcon()
    {
        $icon = new CaptchaIcon(
            icon_id: 1,
            icon: '<i class="bi bi-phone-vibrate"></i>',
            name: 'Mobile',
            colour: 'btn-warning'
        );

        $this->assertEquals(1, $icon->getIconId());
        $this->assertEquals('<i class="bi bi-phone-vibrate"></i>', $icon->getIcon());
        $this->assertEquals('Mobile', $icon->getName());
        $this->assertEquals('btn-warning', $icon->getColour());
    }

    public function testCaptchaIconDefaults()
    {
        $icon = new CaptchaIcon();

        $this->assertEquals(null, $icon->getIconId());
        $this->assertEquals('', $icon->getIcon());
        $this->assertEquals('', $icon->getName());
        $this->assertEquals('', $icon->getColour());
    }

    public function testCaptchaIconNull()
    {
        $icon = new CaptchaIcon(
            icon_id: null,
            icon: null,
            name: null,
            colour: null
        );

        $this->assertNull($icon->getIconId());
        $this->assertNull($icon->getIcon());
        $this->assertNull($icon->getName());
        $this->assertNull($icon->getColour());
    }
}

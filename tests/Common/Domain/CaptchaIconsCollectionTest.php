<?php

declare(strict_types=1);

namespace Tests\Common\Domain;

use MyEspacio\Common\Domain\CaptchaIcon;
use MyEspacio\Common\Domain\CaptchaIconCollection;
use PHPUnit\Framework\TestCase;

final class CaptchaIconsCollectionTest extends TestCase
{
    public function testAdd()
    {
        $elements = [
            [
                'icon_id' => '1',
                'icon' => '<i class="bi bi-phone-vibrate"></i>',
                'name' => 'Mobile'
            ],
            [
                'icon_id' => '2',
                'icon' => '<i class="bi bi-keyboard"></i>',
                'name' => 'Keyboard'
            ]
        ];
        $collection = new CaptchaIconCollection($elements);

        $this->assertCount(2, $collection);
        foreach ($collection as $icon) {
            $element = array_shift($elements);
            $this->assertInstanceOf(CaptchaIcon::class, $icon);
            $this->assertEquals($element['icon_id'], $icon->getIconId());
            $this->assertEquals($element['icon'], $icon->getIcon());
            $this->assertEquals($element['name'], $icon->getName());
            $this->assertEquals($element['colour'] ?? '', $icon->getColour());
        }
    }
}
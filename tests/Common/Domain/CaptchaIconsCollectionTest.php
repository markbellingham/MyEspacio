<?php

declare(strict_types=1);

namespace Tests\Common\Domain;

use InvalidArgumentException;
use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
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
            $this->assertInstanceOf(\MyEspacio\Common\Domain\Entity\CaptchaIcon::class, $icon);
            $this->assertEquals($element['icon_id'], $icon->getIconId());
            $this->assertEquals($element['icon'], $icon->getIcon());
            $this->assertEquals($element['name'], $icon->getName());
            $this->assertEquals('', $icon->getColour());
        }
    }

    public function testEmpty()
    {
        $collection = new CaptchaIconCollection([]);

        $this->assertInstanceOf(CaptchaIconCollection::class, $collection);
        $this->assertCount(0, $collection);
    }

    public function testInvalidCollection()
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new CaptchaIconCollection([
            'Hello',
            'World'
        ]);
    }
}

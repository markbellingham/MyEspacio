<?php

declare(strict_types=1);

namespace Tests\Common\Domain\Collection;

use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Common\Domain\Entity\CaptchaIcon;
use MyEspacio\Framework\Exceptions\CollectionException;
use PHPUnit\Framework\TestCase;

final class CaptchaIconsCollectionTest extends TestCase
{
    public function testCaptchaIconsCollection()
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
            $this->assertEquals('', $icon->getColour());
        }
    }

    public function testEmpty()
    {
        $collection = new CaptchaIconCollection([]);

        $this->assertInstanceOf(CaptchaIconCollection::class, $collection);
        $this->assertCount(0, $collection);
    }

    public function testRequiredKeys()
    {
        $elements = [
            [
                'colour' => '1'
            ],
            [
                'colour' => '2'
            ]
        ];

        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('Missing required values: icon_id, icon, name');

        $collection = new CaptchaIconCollection($elements);
        foreach ($collection as $tag) {
        }
    }

    public function testToArray()
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

        $this->assertEquals($elements, $collection->toArray());
    }

    public function testWrongDataType()
    {
        $elements = [
            'icon_id' => '1',
            'icon' => '<i class="bi bi-phone-vibrate"></i>',
            'name' => 'Mobile'
        ];

        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('The data passed is not an array.');

        $collection = new CaptchaIconCollection($elements);
        foreach ($collection as $tag) {
        }
    }
}

<?php

namespace Tests\Common\Infrastructure\MySql;

use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Framework\Database\Connection;
use PHPUnit\Framework\TestCase;

final class IconsRepositoryTest extends TestCase
{
    public function testGetIcons(): void
    {
        $qty = 2;
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT icon_id, icon, name
            FROM project.icons
            ORDER BY RAND()
            LIMIT :quantity',
                [
                    'quantity' => $qty
                ]
            )
            ->willReturn(
                [
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
                ]
            );

        $repository = new \MyEspacio\Common\Infrastructure\MySql\IconRepository($db);
        $results = $repository->getIcons($qty);
        $this->assertInstanceOf(CaptchaIconCollection::class, $results);

        $names = ['Mobile','Keyboard'];
        foreach ($results as $result) {
            $this->assertEquals(array_shift($names), $result->getName());
        }
    }
}

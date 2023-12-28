<?php

namespace Tests\Common\Infrastructure;

use MyEspacio\Common\Domain\CaptchaIconCollection;
use MyEspacio\Common\Infrastructure\MysqlIconsRepository;
use MyEspacio\Framework\Database\PdoConnection;
use PHPUnit\Framework\TestCase;

final class IconsRepositoryTest extends TestCase
{
    public function testGetIcons()
    {
        $qty = 2;
        $db = $this->createMock(PdoConnection::class);
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

        $repository = new MysqlIconsRepository($db);
        $results = $repository->getIcons($qty);
        $this->assertInstanceOf(CaptchaIconCollection::class, $results);

        $names = ['Mobile','Keyboard'];
        foreach ($results as $result) {
            $this->assertEquals(array_shift($names), $result->getName());
        }
    }
}
<?php

namespace Tests\Common\Infrastructure\MySql;

use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Common\Infrastructure\MySql\IconRepository;
use MyEspacio\Framework\Database\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class IconRepositoryTest extends TestCase
{
    /**
     * @param array<int, array<string, mixed>> $databaseResult
     * @throws Exception
     */
    #[DataProvider('getIconsDataProvider')]
    public function testGetIcons(
        int $quantity,
        string $query,
        array $databaseResult,
        int $resultCount
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchAll')
            ->with($query, [])
            ->willReturn($databaseResult);

        $repository = new IconRepository($db);
        $results = $repository->getIcons($quantity);
        $this->assertInstanceOf(CaptchaIconCollection::class, $results);
        $this->assertCount($resultCount, $results);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function getIconsDataProvider(): array
    {
        return [
            'test_1' => [
                2,
                'SELECT icon_id, icon, name
            FROM project.icons
            ORDER BY RAND()
            LIMIT 3',
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
                    ],
                    [
                        'icon_id' => '4',
                        'icon' => '<i class="bi bi-headphones"></i>',
                        'name' => 'Headphones'
                    ]
                ],
                3
            ],
            'test_2' => [
                5,
                'SELECT icon_id, icon, name
            FROM project.icons
            ORDER BY RAND()
            LIMIT 5',
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
                    ],
                    [
                        'icon_id' => '4',
                        'icon' => '<i class="bi bi-headphones"></i>',
                        'name' => 'Headphones'
                    ],
                    [
                        'icon_id' => '15',
                        'icon' => '<i class="bi bi-pc-display-horizontal"></i>',
                        'name' => 'Desktop Computer'
                    ],
                    [
                        'icon_id' => '12',
                        'icon' => '<i class="bi bi-lock"></i>',
                        'name' => 'Padlock'
                    ]
                ],
                5
            ]
        ];
    }
}

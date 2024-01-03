<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use MyEspacio\Framework\Database\PdoConnection;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class PdoConnectionTest extends TestCase
{
    public function testFetchOne()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 1])
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('errorInfo')
            ->willReturn(['00000', null, null]);

        $stmt->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo(PDO::FETCH_ASSOC))
            ->willReturn(
                [
                    'icon_id' => '1',
                    'icon' => '<i class="bi bi-phone-vibrate"></i>',
                    'name' => 'Mobile'
                ]
            );

        $connection = new PdoConnection($pdo);
        $result = $connection->fetchOne(
            'SELECT * FROM project.icons WHERE icon_id = :id',
            ['id' => 1]
        );
        $this->assertIsArray($result);
        $this->assertEquals(count($result), count($result, COUNT_RECURSIVE));
        $this->assertArrayHasKey('icon_id', $result);
    }

    public function testFetchOneFail()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(
                [
                    'icon_id' => '1',
                    'icon' => '<i class="bi bi-phone-vibrate"></i>',
                ]
            )
            ->willReturn(false);

        $stmt->expects($this->once())
            ->method('errorInfo')
            ->willReturn(
                [
                    'HY000',
                    '1364',
                    "Field 'name' doesn't have a default value"
                ]
            );

        $connection = new PdoConnection($pdo);
        $result = $connection->fetchOne(
            'INSERT INTO project.icons (icon_id, icon) VALUES (:icon_id, :icon)',
            [
                'icon_id' => '1',
                'icon' => '<i class="bi bi-phone-vibrate"></i>',
            ]
        );
        $this->assertNull($result);
    }

    public function testFetchOneNotFound()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 100])
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('errorInfo')
            ->willReturn(['00000', null, null]);

        $stmt->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo(PDO::FETCH_ASSOC))
            ->willReturn(false);

        $connection = new PdoConnection($pdo);
        $result = $connection->fetchOne(
            'SELECT * FROM project.icons WHERE icon_id = :id',
            ['id' => 100]
        );
        $this->assertNull($result);
    }

    public function testFetchAll()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('errorInfo')
            ->willReturn(['00000', null, null]);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with($this->equalTo(PDO::FETCH_ASSOC))
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

        $connection = new PdoConnection($pdo);
        $results = $connection->fetchAll(
            'SELECT * FROM project.icons',
            []
        );
        $this->assertCount(2, $results);
        $names = ['Mobile', 'Keyboard'];
        foreach ($results as $result) {
            $this->assertEquals(array_shift($names), $result['name']);
        }
    }

    public function testFetchALlFail()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $stmt->expects($this->once())
            ->method('errorInfo')
            ->willReturn(
                [
                    'HY000',
                    '1645',
                    "Access denied for user \'username\'@\'localhost\' (using password: YES)"
                ]
            );
        $connection = new PdoConnection($pdo);
        $results = $connection->fetchAll(
            'SELECT * FROM project.icons',
            []
        );
        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    public function testRun()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM project.icons')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with([])
            ->willReturn(true);

        $connection = new PdoConnection($pdo);
        $result = $connection->run('SELECT * FROM project.icons', []);
        $this->assertEquals($stmt, $result);
    }

    public function testRunFail()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM project.icons')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with([])
            ->willReturn(false);

        $connection = new PdoConnection($pdo);
        $result = $connection->run('SELECT * FROM project.icons', []);
        $this->assertEquals($stmt, $result);
    }

    public function testStatementHasErrorsFalse()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('errorInfo')
            ->willReturn(['00000', null, null]);

        $connection = new PdoConnection($pdo);
        $this->assertFalse(
            $connection->statementHasErrors($stmt)
        );
    }

    public function testStatementHasErrorsTrue()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('errorInfo')
            ->willReturn(
                [
                    'HY000',
                    '1364',
                    "Field 'name' doesn't have a default value"
                ]
            );
        $connection = new PdoConnection($pdo);
        $this->assertTrue(
            $connection->statementHasErrors($stmt)
        );
    }

    public function testLastInsertId()
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('1');

        $connection = new PdoConnection($pdo);
        $result = $connection->lastInsertId();
        $this->assertEquals(1, $result);
    }

    public function testLastInsertIdFail()
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('');

        $connection = new PdoConnection($pdo);
        $result = $connection->lastInsertId();
        $this->assertEquals(0, $result);
    }
}

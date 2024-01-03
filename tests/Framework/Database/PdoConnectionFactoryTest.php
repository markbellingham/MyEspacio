<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use MyEspacio\Framework\Database\PdoConnection;
use MyEspacio\Framework\Database\PdoConnectionFactory;
use PHPUnit\Framework\TestCase;

final class PdoConnectionFactoryTest extends TestCase
{
    public function testCreate()
    {
        $factory = new PdoConnectionFactory();
        $connection = $factory->create('project');
        $this->assertInstanceOf(PdoConnection::class, $connection);
    }
}

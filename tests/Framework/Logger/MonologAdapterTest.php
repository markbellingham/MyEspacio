<?php

declare(strict_types=1);

namespace Tests\Framework\Logger;

use Monolog\Level;
use Monolog\Logger;
use MyEspacio\Framework\Logger\MonologAdapter;
use PHPUnit\Framework\TestCase;

final class MonologAdapterTest extends TestCase
{
    public function testLog(): void
    {
        $level = Level::Info;
        $message = 'Test message';

        $monologMock = $this->createMock(Logger::class);
        $monologMock->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo($level),
                $this->equalTo($message),
                $this->equalTo([])
            );

        $adapter = new MonologAdapter($monologMock);
        $adapter->log($level, $message);
    }
}

<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Logger;

use Monolog\Logger;

final class MonologAdapter implements LoggerInterface
{
    public function __construct(
        private readonly Logger $monolog
    ) {
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $this->monolog->log($level, $message);
    }
}

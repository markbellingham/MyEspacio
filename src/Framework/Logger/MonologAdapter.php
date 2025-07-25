<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Logger;

use Monolog\Level;
use Monolog\Logger;

final readonly class MonologAdapter implements LoggerInterface
{
    public function __construct(
        private Logger $monolog
    ) {
    }

    public function log(Level $level, string $message, array $context = []): void
    {
        $this->monolog->log($level, $message);
    }
}

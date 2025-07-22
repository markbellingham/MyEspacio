<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Logger;

use Monolog\Level;

interface LoggerInterface
{
    /**
     * @param Level $level
     * @param string $message
     * @param array<string, string> $context
     * @return void
     */
    public function log(Level $level, string $message, array $context = []): void;
}

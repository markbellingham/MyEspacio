<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Logger;

interface LoggerInterface
{
    /**
     * @param string $level
     * @param string $message
     * @param array<string, string> $context
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void;
}

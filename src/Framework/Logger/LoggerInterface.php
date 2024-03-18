<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Logger;

interface LoggerInterface
{
    public function log(string $level, string $message, array $context = []): void;
}

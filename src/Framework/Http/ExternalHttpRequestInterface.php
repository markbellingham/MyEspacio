<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

interface ExternalHttpRequestInterface
{
    public function get(string $url, array $headers = [], int $timeout = 30): mixed;

    public function post(string $url, ?array $data = null, array $headers = [], int $timeout = 30): mixed;
}

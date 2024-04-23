<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

interface ExternalHttpRequestInterface
{
    public function get(string $url, array $headers): bool|string;

    public function post(string $url, array $data, array $headers): bool|string;
}

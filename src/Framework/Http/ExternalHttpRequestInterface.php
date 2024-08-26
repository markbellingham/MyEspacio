<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

interface ExternalHttpRequestInterface
{
    /**
     * @param string $url
     * @param array<string, string> $headers
     * @return bool|string
     */
    public function get(string $url, array $headers): bool|string;

    /**
     * @param string $url
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return bool|string
     */
    public function post(string $url, array $data, array $headers): bool|string;
}

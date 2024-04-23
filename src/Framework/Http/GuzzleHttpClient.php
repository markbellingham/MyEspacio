<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleHttpClient implements ExternalHttpRequestInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function get(string $url, array $headers = []): string|bool
    {
        try {
            $response = $this->client->request('GET', $url, [
                'headers' => $headers
            ]);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            return false;
        }
    }

    public function post(string $url, array $data = [], array $headers = []): bool|string
    {
        try {
            $response = $this->client->request('POST', $url, [
                'headers' => $headers,
                'form_params' => $data
            ]);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            return false;
        }
    }
}

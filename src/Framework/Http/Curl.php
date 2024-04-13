<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

use CurlHandle;
use RuntimeException;

use function PHPUnit\Framework\isInstanceOf;

class Curl implements ExternalHttpRequestInterface
{
    private mixed $curlHandler;

    public function __construct($curlHandler = null)
    {
        if ($curlHandler === null) {
            $curlHandler = curl_init();
            if ($curlHandler === false) {
                throw new RuntimeException('cURL initialization failed.');
            }
        }
        $this->curlHandler = $curlHandler;
    }

    public function get(string $url, array $headers = [], int $timeout = 30): mixed
    {
        return $this->sendRequest('GET', $url, null, $headers, $timeout);
    }

    public function post(string $url, ?array $data = null, array $headers = [], int $timeout = 30): mixed
    {
        return $this->sendRequest('POST', $url, $data, $headers, $timeout);
    }

    private function sendRequest(string $method, string $url, ?array $data = null, array $headers = [], int $timeout = 30): mixed
    {
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($method === 'POST' && $data !== null) {
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        }

        curl_setopt_array($this->curlHandler, $options);

        $response = curl_exec($this->curlHandler);
        $error = curl_error($this->curlHandler);
        $statusCode = curl_getinfo($this->curlHandler, CURLINFO_RESPONSE_CODE);

        if ($error !== '') {
            throw new RuntimeException('cURL error: ' . $error);
        }

        if ($response === false) {
            throw new RuntimeException('cURL request failed.');
        }

        if ($statusCode >= 400) {
            throw new RuntimeException('HTTP request failed with status code: ' . $statusCode);
        }

        return $response;
    }

    public function __destruct()
    {
        if ($this->curlHandler instanceof CurlHandle) {
            curl_close($this->curlHandler);
        }
    }
}

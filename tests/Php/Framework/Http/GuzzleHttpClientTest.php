<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MyEspacio\Framework\Http\GuzzleHttpClient;
use PHPUnit\Framework\TestCase;

final class GuzzleHttpClientTest extends TestCase
{
    public function testGet(): void
    {
        $url = 'https://httpbin.org/get';
        $expectedResponse = '{"url": "https://httpbin.org/get"}';

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('GET', $url)
            ->willReturn(new Response(200, [], $expectedResponse));

        $httpClient = new GuzzleHttpClient($clientMock);

        $response = $httpClient->get($url);
        $decoded = json_decode((string) $response, true);

        $this->assertIsArray($decoded);
        $this->assertEquals($url, $decoded['url']);
    }

    public function testGetWithHeaders(): void
    {
        $url = 'https://httpbin.org/get';
        $expectedResponse = '{"url": "https://httpbin.org/get"}';
        $headers = ['Authorization' => 'Bearer YOUR_ACCESS_TOKEN'];

        $clientMock = $this->createMock(Client::class);

        $clientMock->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $url,
                $this->callback(function ($options) use ($headers) {
                    $this->assertArrayHasKey('headers', $options);
                    foreach ($headers as $header => $value) {
                        $this->assertArrayHasKey($header, $options['headers']);
                        $this->assertEquals($value, $options['headers'][$header]);
                    }
                    return true;
                })
            )
            ->willReturn(new Response(200, [], $expectedResponse));

        $httpClient = new GuzzleHttpClient($clientMock);
        $response = $httpClient->get($url, $headers);
        $decoded = json_decode((string) $response, true);
        $this->assertIsArray($decoded);
        $this->assertEquals($url, $decoded['url']);
    }

    public function testPost(): void
    {
        $url = 'https://httpbin.org/post';
        $expectedResponse = '{"url": "https://httpbin.org/post"}';

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('POST', $url)
            ->willReturn(new Response(200, [], $expectedResponse));

        $httpClient = new GuzzleHttpClient($clientMock);

        $response = $httpClient->post($url);
        $decoded = json_decode((string) $response, true);

        $this->assertIsArray($decoded);
        $this->assertEquals($url, $decoded['url']);
    }

    public function testPostWithHeaders(): void
    {
        $url = 'https://httpbin.org/post';
        $expectedResponse = '{"url": "https://httpbin.org/post"}';
        $headers = ['Authorization' => 'Bearer YOUR_ACCESS_TOKEN'];

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $url,
                $this->callback(function ($options) use ($headers) {
                    $this->assertArrayHasKey('headers', $options);
                    foreach ($headers as $header => $value) {
                        $this->assertArrayHasKey($header, $options['headers']);
                        $this->assertEquals($value, $options['headers'][$header]);
                    }
                    return true;
                })
            )
            ->willReturn(new Response(200, [], $expectedResponse));

        $httpClient = new GuzzleHttpClient($clientMock);
        $response = $httpClient->post($url, [], $headers);
        $decoded = json_decode((string) $response, true);

        $this->assertIsArray($decoded);
        $this->assertEquals($url, $decoded['url']);
    }

    public function testPostWithData(): void
    {
        $url = 'https://httpbin.org/post';
        $expectedResponse = '{"data": {"name": "John Doe", "email": "john@example.com"}}';
        $postData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $headers = ['Authorization' => 'Bearer YOUR_ACCESS_TOKEN', 'Content-Type' => 'application/json'];

        $clientMock = $this->createMock(Client::class);

        $clientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $url,
                $this->callback(function ($options) use ($postData, $headers) {
                    $this->assertArrayHasKey('form_params', $options);
                    $this->assertEquals($postData, $options['form_params']);
                    $this->assertArrayHasKey('headers', $options);
                    foreach ($headers as $header => $value) {
                        $this->assertArrayHasKey($header, $options['headers']);
                        $this->assertEquals($value, $options['headers'][$header]);
                    }
                    return true;
                })
            )
            ->willReturn(new Response(200, [], $expectedResponse));

        $httpClient = new GuzzleHttpClient($clientMock);
        $response = $httpClient->post($url, $postData, $headers);
        $decoded = json_decode((string) $response, true);

        $this->assertIsArray($decoded);
        $this->assertEquals($postData, $decoded['data']);
    }

    public function testGetThrowsRequestException(): void
    {
        $url = 'https://httpbin.org/get';

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('GET', $url)
            ->willThrowException(new RequestException('Error fetching URL', new Request('GET', $url)));

        $httpClient = new GuzzleHttpClient($clientMock);

        $response = $httpClient->get($url);
        $this->assertFalse($response);
    }

    public function testPostThrowsRequestException(): void
    {
        $url = 'https://httpbin.org/post';
        $postData = ['name' => 'John Doe', 'email' => 'john@example.com'];

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('POST', $url)
            ->willThrowException(new RequestException('Error posting data', new Request('POST', $url)));

        $httpClient = new GuzzleHttpClient($clientMock);

        $response = $httpClient->post($url, $postData);
        $this->assertFalse($response);
    }
}

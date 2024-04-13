<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use MyEspacio\Framework\Http\Curl;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CurlTest extends TestCase
{
    private const EXPECTED_RESPONSE = '';

    public function testGetRequestWithHttpBin()
    {
        $url = 'https://httpbin.org/get';

        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curlHandler, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curlHandler, CURLOPT_URL, $url);
        curl_setopt($curlHandler, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, []);
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, null);

        $expectedResponse = self::EXPECTED_RESPONSE;

        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, $expectedResponse);

        $curl = new Curl($curlHandler);
        $response = $curl->get($url);
        $json = json_decode($response);

        $this->assertObjectHasProperty('url', $json);
        $this->assertEquals($json->url, $url);
    }

    public function testPostRequestWithHttpBin()
    {
        $url = 'https://httpbin.org/post';

        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curlHandler, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curlHandler, CURLOPT_URL, $url);
        curl_setopt($curlHandler, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, []);
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, null);

        $expectedResponse = self::EXPECTED_RESPONSE;

        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, $expectedResponse);

        $curl = new Curl($curlHandler);
        $response = $curl->post($url);
        $json = json_decode($response);

        $this->assertObjectHasProperty('url', $json);
        $this->assertEquals($json->url, $url);
    }

    public function testPostWithData()
    {
        $url = 'https://httpbin.org/post';

        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curlHandler, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curlHandler, CURLOPT_URL, $url);
        curl_setopt($curlHandler, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, []);
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, null);

        $expectedResponse = self::EXPECTED_RESPONSE;

        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, $expectedResponse);

        $curl = new Curl($curlHandler);
        $response = $curl->post($url, ['data' => 'Some data']);
        $json = json_decode($response);

        $this->assertObjectHasProperty('url', $json);
        $this->assertEquals($json->url, $url);
    }

    public function testCurlExecReturnsError()
    {
        $curlHandler = curl_init();
        $curl = new Curl($curlHandler);

        // Set an option that will cause curl_exec to return an error
        curl_setopt($curlHandler, CURLOPT_CONNECTTIMEOUT_MS, 1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/cURL error:/');

        $curl->get('http://example.com');
    }

    public function testHttpStatusCodeError()
    {
        $curlHandler = curl_init();
        $curl = new Curl($curlHandler);

        // Mock the response to simulate a 404 error
        curl_setopt($curlHandler, CURLOPT_HEADER, true);
        curl_setopt($curlHandler, CURLOPT_NOBODY, true);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_URL, 'http://example.com/nonexistent');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTP request failed with status code: 404');

        $curl->get('http://example.com/nonexistent');
    }
}

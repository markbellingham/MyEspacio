<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use MyEspacio\Framework\Http\Curl;
use PHPUnit\Framework\TestCase;

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
        $response = $curl->getRequest($url);
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
        $response = $curl->postRequest($url);
        $json = json_decode($response);

        $this->assertObjectHasProperty('url', $json);
        $this->assertEquals($json->url, $url);
    }
}

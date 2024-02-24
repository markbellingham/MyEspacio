<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class CurlTest extends TestCase
{
    public function testDummy()
    {
        $this->assertTrue(true);
    }
//    private $client;
//
//    protected function setUp(): void
//    {
//        $this->client = new Client();
//    }
//
//    public function testGetRequest()
//    {
//        $url = 'https://jsonplaceholder.typicode.com/posts/1';
//        $response = $this->client->request('GET', $url);
//        $data = json_decode($response->getBody()->getContents());
//
//        $this->assertObjectHasProperty('id', $data);
//        $this->assertEquals(1, $data->id);
//    }
//
//    public function testPostRequest()
//    {
//        $url = 'https://jsonplaceholder.typicode.com/posts';
//        $data = ['title' => 'foo', 'body' => 'bar', 'userId' => 1];
//        $response = $this->client->request('POST', $url, ['form_params' => $data]);
//        $data = json_decode($response->getBody()->getContents());
//
//        $this->assertObjectHasProperty('id', $data);
//        $this->assertEquals(101, $data->id);
//    }
}

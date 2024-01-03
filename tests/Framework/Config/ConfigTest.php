<?php

declare(strict_types=1);

namespace Tests\Framework\Config;

use PHPUnit\Framework\TestCase;

define('CONFIG_CONFIG_TEST', include ROOT_DIR . '/config/config.php');

final class ConfigTest extends TestCase
{
    public function testArrayKeys()
    {
        $expectedKeys = [
            'contact',
            'project',
            'lastfm_api',
            'google',
            'server_secret'
        ];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, CONFIG_CONFIG_TEST);
        }
    }

    public function testContactConfig()
    {
        $config = CONFIG['contact'];
        $expectedKeys = [
            'email',
            'name'
        ];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $config);
            $this->assertIsString($config[$key]);
            $this->assertNotEmpty($config[$key]);
        }
    }

    public function testDbConfig()
    {
        $config = CONFIG['project'];
        $expectedKeys = [
            'db_host',
            'db_name',
            'db_char',
            'db_user',
            'db_pass'
        ];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $config);
            $this->assertIsString($config[$key]);
            $this->assertNotEmpty($config[$key]);
        }
    }

    public function testLastFmApiConfig()
    {
        $config = CONFIG['lastfm_api'];
        $expectedKeys = [
            'root_url',
            'username',
            'api_key',
            'shared_secret'
        ];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $config);
            $this->assertIsString($config[$key]);
            $this->assertNotEmpty($config[$key]);
        }
    }

    public function testGoogleConfig()
    {
        $config = CONFIG['google'];
        $expectedKeys = [
            'email',
            'apppassword'
        ];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $config);
            $this->assertIsString($config[$key]);
            $this->assertNotEmpty($config[$key]);
        }
    }

    public function testServerSecret()
    {
        $this->assertIsString(CONFIG['server_secret']);
        $this->assertNotEmpty(CONFIG['server_secret']);
    }
}

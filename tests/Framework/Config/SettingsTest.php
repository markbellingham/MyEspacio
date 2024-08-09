<?php

declare(strict_types=1);

namespace Tests\Framework\Config;

use MyEspacio\Framework\Config\Settings;
use PHPUnit\Framework\TestCase;

define('CONFIG_SETTINGS_TEST', include ROOT_DIR . '/config/config.php');

final class SettingsTest extends TestCase
{
    public function testGetConfig(): void
    {
        $key = 'project';
        $this->assertEquals(CONFIG_SETTINGS_TEST[$key], Settings::getConfig($key));
    }

    public function testGetServerSecret(): void
    {
        $this->assertEquals(
            CONFIG_SETTINGS_TEST['server_secret'],
            Settings::getServerSecret()
        );
    }
}

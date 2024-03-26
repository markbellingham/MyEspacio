<?php

declare(strict_types=1);

namespace Tests\Locale;

use Monolog\Test\TestCase;

final class MessagesTest extends TestCase
{
    /**
     * @dataProvider localizationFilesProvider
     */
    public function testRequiredKeysExist(string $file)
    {
        $messages = require $file;
        $this->assertIsArray($messages);

        // Define your required keys
        $requiredKeys = ['login'];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $messages);
        }
    }

    public static function localizationFilesProvider(): array
    {
        return [
            [__DIR__ . '/../../src/Locale/en/messages.php'],
            [__DIR__ . '/../../src/Locale/es/messages.php'],
        ];
    }
}

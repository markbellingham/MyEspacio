<?php

declare(strict_types=1);

namespace Tests\Locale;

use Monolog\Test\TestCase;
use MyEspacio\Framework\Localisation\LanguagesDirectory;

final class MessagesTest extends TestCase
{
    private array $referenceKeys;

    protected function setUp(): void
    {
        $referencesFile = include(__DIR__ . '/../../src/Locale/en/messages.php');
        $this->referenceKeys = $this->arrayKeysRecursive($referencesFile);
    }

    /**
     * @dataProvider localizationFilesProvider
     */
    public function testAllLanguageFilesHaveSameKeys(string $languageFile): void
    {
        $comparedFile = include($languageFile);
        $comparedKeys = $this->arrayKeysRecursive($comparedFile);
        $this->assertEquals($this->referenceKeys, $comparedKeys);
    }

    private function arrayKeysRecursive(array $array): array
    {
        $keys = [];

        foreach ($array as $key => $value) {
            $keys[] = $key;
            if (is_array($value)) {
                $keys = array_merge($keys, $this->arrayKeysRecursive($value));
            }
        }

        return $keys;
    }

    public static function localizationFilesProvider(): array
    {
        return [
            [__DIR__ . '/../../src/Locale/es/messages.php'],
            [__DIR__ . '/../../src/Locale/fr/messages.php'],
        ];
    }
}

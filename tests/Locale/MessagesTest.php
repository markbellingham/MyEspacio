<?php

declare(strict_types=1);

namespace Tests\Locale;

use Monolog\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class MessagesTest extends TestCase
{
    /** @var array|String[] */
    private array $referenceKeys;

    protected function setUp(): void
    {
        $referencesFile = include(__DIR__ . '/../../src/Locale/en/messages.php');
        $this->referenceKeys = $this->arrayKeysRecursive($referencesFile);
    }

    #[DataProvider('localizationFilesProvider')]
    public function testAllLanguageFilesHaveSameKeys(string $languageFile): void
    {
        $comparedFile = include($languageFile);
        $comparedKeys = $this->arrayKeysRecursive($comparedFile);
        $this->assertEquals($this->referenceKeys, $comparedKeys);
    }

    /**
     * @param array<string, String[]>|String[] $array
     * @return String[] $array
     */
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

    /**
     * @return array<int, String[]>
     */
    public static function localizationFilesProvider(): array
    {
        return [
            [__DIR__ . '/../../src/Locale/es/messages.php'],
            [__DIR__ . '/../../src/Locale/fr/messages.php'],
        ];
    }
}

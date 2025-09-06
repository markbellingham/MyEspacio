<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Localisation;

use MyEspacio\Framework\Localisation\LanguageLoader;
use MyEspacio\Framework\Localisation\LanguagesDirectoryInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Throwable;

final class LanguageLoaderTest extends TestCase
{
    /**
     * @param null|array<string, mixed> $expectedResult
     */
    #[DataProvider('loadTranslationsProvider')]
    public function testLoadTranslationsDataProvider(
        string $language,
        string $filename,
        ?array $expectedResult,
        ?string $expectedException,
        ?string $expectedMessage
    ): void {
        $root = vfsStream::setup();
        $directory = vfsStream::newDirectory('languages')->at($root);
        $englishDirectory = vfsStream::newDirectory('en')->at($directory);

        vfsStream::newFile('test.php')->at($englishDirectory)
            ->setContent('<?php return ["hello" => ["hello" => "Hello"], "goodbye" => ["goodbye" => "Goodbye"]];');

        vfsStream::newFile('not_array.php')->at($englishDirectory)
            ->setContent('<?php return "not an array";');

        vfsStream::newFile('bad_key.php')->at($englishDirectory)
            ->setContent('<?php return [123 => ["hello" => "Hello"]];');

        vfsStream::newFile('bad_value.php')->at($englishDirectory)
            ->setContent('<?php return ["hello" => "not an array"];');

        vfsStream::newFile('bad_subkey.php')->at($englishDirectory)
            ->setContent('<?php return ["hello" => [123 => "Hello"]];');

        vfsStream::newFile('bad_subvalue.php')->at($englishDirectory)
            ->setContent('<?php return ["hello" => ["greeting" => 123]];');

        $languagesDirectory = $this->createMock(LanguagesDirectoryInterface::class);
        $languagesDirectory->method('toString')->willReturn($directory->url());

        $loader = new LanguageLoader($languagesDirectory);

        /** @var null|class-string<Throwable> $expectedException */
        if ($expectedException !== null) {
            $this->expectException($expectedException);
            if ($expectedMessage !== null) {
                $this->expectExceptionMessage($expectedMessage);
            }
            $loader->loadTranslations($language, $filename);
            return;
        }

        $actual = $loader->loadTranslations($language, $filename);
        $this->assertEquals($expectedResult, $actual);
    }

    /** @return array<string, array<int,mixed>> */
    public static function loadTranslationsProvider(): array
    {
        return [
            'valid english file' => [
                'en',
                'test',
                ["hello" => ["hello" => "Hello"], "goodbye" => ["goodbye" => "Goodbye"]],
                null,
                null,
            ],
            'fallback to english when other language missing' => [
                'fr',
                'test',
                ["hello" => ["hello" => "Hello"], "goodbye" => ["goodbye" => "Goodbye"]],
                null,
                null,
            ],
            'english file not found' => [
                'en',
                'bad_file',
                null,
                FileNotFoundException::class,
                '/en/bad_file does not exist',
            ],
            'file does not return array' => [
                'en',
                'not_array',
                null,
                RuntimeException::class,
                '/en/not_array does not return an array',
            ],
            'fallback to english but not array' => [
                'fr',
                'not_array',
                null,
                RuntimeException::class,
                '/en/not_array does not return an array',
            ],
            'non-string top-level key' => [
                'en',
                'bad_key',
                null,
                RuntimeException::class,
                '/en/bad_key contains a non-string key',
            ],
            'non-array top-level value' => [
                'en',
                'bad_value',
                null,
                RuntimeException::class,
                "/en/bad_value: 'hello' does not contain an array",
            ],
            'non-string sub-key' => [
                'en',
                'bad_subkey',
                null,
                RuntimeException::class,
                "/en/bad_subkey: 'hello' contains a non-string key",
            ],
            'non-string sub-value' => [
                'en',
                'bad_subvalue',
                null,
                RuntimeException::class,
                "/en/bad_subvalue: 'hello.greeting' contains a non-string value",
            ]
        ];
    }
}

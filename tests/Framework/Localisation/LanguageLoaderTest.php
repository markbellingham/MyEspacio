<?php

declare(strict_types=1);

namespace Tests\Framework\Localisation;

use MyEspacio\Framework\Localisation\LanguageLoader;
use MyEspacio\Framework\Localisation\LanguagesDirectory;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

final class LanguageLoaderTest extends TestCase
{
    public function testLoadTranslations(): void
    {
        // Create a virtual file system
        $root = vfsStream::setup();

        // Define the directory structure within the virtual file system
        $directory = vfsStream::newDirectory('languages')->at($root);
        $englishDirectory = vfsStream::newDirectory('en')->at($directory);

        // Create spoof language files
        vfsStream::newFile('test.php')->at($englishDirectory)->setContent('<?php return ["hello" => "Hello", "goodbye" => "Goodbye"];');
        vfsStream::newFile('non_existent_file.php')->at($englishDirectory);

        // Mock the LanguagesDirectory
        $languagesDirectory = $this->createMock(LanguagesDirectory::class);
        $languagesDirectory->method('toString')->willReturn($directory->url());

        // Create an instance of LanguageLoader
        $loader = new LanguageLoader($languagesDirectory);

        // Test when the language file exists for both the specified language and the default language (en)
        $this->assertEquals(['hello' => 'Hello', 'goodbye' => 'Goodbye'], $loader->loadTranslations('en', 'test'));

        // Test when the language file for the specified language doesn't exist
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("/fr/test does not exist");
        $loader->loadTranslations('fr', 'test');

        // Test when the language file for the default language (en) doesn't exist
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("/en/non_existent_file does not return an array");
        $loader->loadTranslations('en', 'non_existent_file');
    }
}

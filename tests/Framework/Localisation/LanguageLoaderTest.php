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
        $root = vfsStream::setup();
        $directory = vfsStream::newDirectory('languages')->at($root);
        $englishDirectory = vfsStream::newDirectory('en')->at($directory);

        vfsStream::newFile('test.php')->at($englishDirectory)->setContent('<?php return ["hello" => "Hello", "goodbye" => "Goodbye"];');
        vfsStream::newFile('non_existent_file.php')->at($englishDirectory);

        $languagesDirectory = $this->createMock(LanguagesDirectory::class);
        $languagesDirectory->method('toString')->willReturn($directory->url());

        $loader = new LanguageLoader($languagesDirectory);

        $this->assertEquals(['hello' => 'Hello', 'goodbye' => 'Goodbye'], $loader->loadTranslations('en', 'test'));
    }

    public function testFileDoesNotExist()
    {
        $root = vfsStream::setup();
        $directory = vfsStream::newDirectory('languages')->at($root);
        $englishDirectory = vfsStream::newDirectory('en')->at($directory);

        vfsStream::newFile('test.php')->at($englishDirectory)->setContent('<?php return ["hello" => "Hello", "goodbye" => "Goodbye"];');
        vfsStream::newFile('non_existent_file.php')->at($englishDirectory);

        $languagesDirectory = $this->createMock(LanguagesDirectory::class);
        $languagesDirectory->method('toString')->willReturn($directory->url());

        $loader = new LanguageLoader($languagesDirectory);

        // Return English if language file not found
        $this->assertEquals(['hello' => 'Hello', 'goodbye' => 'Goodbye'], $loader->loadTranslations('fr', 'test'));
    }

    public function testEnglishFileNotFound()
    {
        $root = vfsStream::setup();
        $directory = vfsStream::newDirectory('languages')->at($root);
        $englishDirectory = vfsStream::newDirectory('en')->at($directory);

        vfsStream::newFile('test.php')->at($englishDirectory)->setContent('<?php return ["hello" => "Hello", "goodbye" => "Goodbye"];');
        vfsStream::newFile('non_existent_file.php')->at($englishDirectory);

        $languagesDirectory = $this->createMock(LanguagesDirectory::class);
        $languagesDirectory->method('toString')->willReturn($directory->url());

        $loader = new LanguageLoader($languagesDirectory);

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("/en/bad_file does not exist");
        $loader->loadTranslations('en', 'bad_file');
    }

    public function testFileDoesNotReturnArray()
    {
        $root = vfsStream::setup();
        $directory = vfsStream::newDirectory('languages')->at($root);
        $englishDirectory = vfsStream::newDirectory('en')->at($directory);

        vfsStream::newFile('test.php')->at($englishDirectory)->setContent('<?php return ["hello" => "Hello", "goodbye" => "Goodbye"];');
        vfsStream::newFile('not_array.php')->at($englishDirectory);

        $languagesDirectory = $this->createMock(LanguagesDirectory::class);
        $languagesDirectory->method('toString')->willReturn($directory->url());

        $loader = new LanguageLoader($languagesDirectory);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("/en/not_array does not return an array");
        $loader->loadTranslations('en', 'not_array');
    }

    public function testNotFoundAndNotArray()
    {
        $root = vfsStream::setup();
        $directory = vfsStream::newDirectory('languages')->at($root);
        $englishDirectory = vfsStream::newDirectory('en')->at($directory);

        vfsStream::newFile('test.php')->at($englishDirectory)->setContent('<?php return ["hello" => "Hello", "goodbye" => "Goodbye"];');
        vfsStream::newFile('not_array.php')->at($englishDirectory);

        $languagesDirectory = $this->createMock(LanguagesDirectory::class);
        $languagesDirectory->method('toString')->willReturn($directory->url());

        $loader = new LanguageLoader($languagesDirectory);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("/en/not_array does not return an array");
        $loader->loadTranslations('fr', 'not_array');
    }
}

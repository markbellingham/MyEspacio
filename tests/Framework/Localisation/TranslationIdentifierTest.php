<?php

declare(strict_types=1);

namespace Tests\Framework\Localisation;

use InvalidArgumentException;
use MyEspacio\Framework\Localisation\LanguagesDirectory;
use MyEspacio\Framework\Localisation\TranslationIdentifier;
use PHPUnit\Framework\TestCase;

final class TranslationIdentifierTest extends TestCase
{
    public function testTranslationIdentifier()
    {
        $directory = new LanguagesDirectory(ROOT_DIR);
        $identifier = new TranslationIdentifier('en', 'messages', $directory);

        $this->assertEquals('en', $identifier->getLanguage());
        $this->assertEquals('messages', $identifier->getFilename());
    }

    public function testTranslationIdentifierWrongLanguage()
    {
        $directory = new LanguagesDirectory(ROOT_DIR);
        $identifier = new TranslationIdentifier('se', 'messages', $directory);

        $this->assertEquals('en', $identifier->getLanguage());
        $this->assertEquals('messages', $identifier->getFilename());
    }

    public function testTranslationIdentifierWrongFile()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File does not exist: Bad File');

        $directory = new LanguagesDirectory(ROOT_DIR);
        $identifier = new TranslationIdentifier('en', 'Bad File', $directory);
    }
}

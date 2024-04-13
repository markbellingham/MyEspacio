<?php

namespace Tests\Framework\Localisation;

use MyEspacio\Framework\Localisation\LanguagesDirectory;
use MyEspacio\Framework\Localisation\TranslationIdentifier;
use MyEspacio\Framework\Localisation\TranslationIdentifierFactory;
use PHPUnit\Framework\TestCase;

class TranslationIdentifierFactoryTest extends TestCase
{
    public function testCreate()
    {
        $languagesDirectory = new LanguagesDirectory(ROOT_DIR);

        $factory = new TranslationIdentifierFactory($languagesDirectory);

        $language = 'en';
        $filename = 'messages';

        $translationIdentifier = $factory->create($language, $filename);

        $this->assertInstanceOf(TranslationIdentifier::class, $translationIdentifier);

        $this->assertEquals($language, $translationIdentifier->getLanguage());
        $this->assertEquals($filename, $translationIdentifier->getFilename());
    }
}

<?php

namespace MyEspacio\Framework\Localisation;

final class TranslationIdentifierFactory implements TranslationIdentifierFactoryInterface
{
    public function __construct(
        private LanguagesDirectoryInterface $languagesDirectory
    ) {
        $this->languagesDirectory = $languagesDirectory;
    }

    public function create(string $language, string $filename): TranslationIdentifier
    {
        return new TranslationIdentifier($language, $filename, $this->languagesDirectory);
    }
}

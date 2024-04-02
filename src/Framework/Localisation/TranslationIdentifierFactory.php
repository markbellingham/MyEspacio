<?php

namespace MyEspacio\Framework\Localisation;

final class TranslationIdentifierFactory
{
    private LanguagesDirectory $languagesDirectory;

    public function __construct(LanguagesDirectory $languagesDirectory)
    {
        $this->languagesDirectory = $languagesDirectory;
    }

    public function create(string $language, string $filename): TranslationIdentifier
    {
        return new TranslationIdentifier($language, $filename, $this->languagesDirectory);
    }
}

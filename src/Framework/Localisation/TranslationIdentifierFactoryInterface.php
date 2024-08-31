<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

interface TranslationIdentifierFactoryInterface
{
    public function create(string $language, string $filename): TranslationIdentifier;
}

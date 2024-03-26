<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

final class TranslationIdentifier
{
    public function __construct(
        private readonly string $language,
        private readonly string $filename
    ) {
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
    public function getFilename(): string
    {
        return $this->filename;
    }
}

<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

interface TranslationIdentifierInterface
{
    public function getLanguage(): string;
    public function getFilename(): string;
}

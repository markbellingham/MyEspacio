<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

interface LanguageLoaderInterface
{
    /** @return array<string, array<string, string>> */
    public function loadTranslations(string $language, string $filename): array;
}

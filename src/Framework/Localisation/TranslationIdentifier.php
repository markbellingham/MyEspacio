<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

use InvalidArgumentException;

final class TranslationIdentifier
{
    private const ALLOWED_LANGUAGES = ['en','es','fr'];
    private const DEFAULT_LANGUAGE = 'en';

    public function __construct(
        private string $language,
        private readonly string $filename,
        private readonly LanguagesDirectory $localisationDirectory
    ) {
        if (in_array($this->language, self::ALLOWED_LANGUAGES) === false) {
            $this->language = self::DEFAULT_LANGUAGE;
        }

        $languageFile = $this->localisationDirectory->toString() . "/$this->language/$this->filename.php";
        if (file_exists($languageFile) === false) {
            throw new InvalidArgumentException('File does not exist: ' . $this->filename);
        }
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

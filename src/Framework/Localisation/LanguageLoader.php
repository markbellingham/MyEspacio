<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class LanguageLoader
{
    private const PLACEHOLDER_PATTERN = '/%\{(\w+)}/';

    private string $languageDirectory;
    private ?array $translations = null;

    public function __construct(
        LocalisationDirectory $localisationDirectory,
        private readonly NestedArrayReader $reader
    ) {
        $this->languageDirectory = $localisationDirectory->toString();
    }

    private function loadTranslations(string $language, string $filename): void
    {
        $languageFilePath = $this->languageDirectory . "/$language/$filename.php";
        if (file_exists($languageFilePath)) {
            $this->translations = include $languageFilePath;
            return;
        }

        $languageFilePath = $this->languageDirectory . "/en/$filename.php";
        if (file_exists($languageFilePath)) {
            $this->translations = include $languageFilePath;
            return;
        }

        throw new FileNotFoundException("$language/$filename");
    }

    public function getTranslationText(
        TranslationIdentifier $identifier,
        string $key,
        array $variables = []
    ): ?string {

        if ($this->translations === null) {
            $this->loadTranslations($identifier->getLanguage(), $identifier->getFilename());
        }

        $keys = explode('.', $key);
        $value = $this->reader->getValue($keys);

        if ($value && count($variables) > 0) {
            $value = $this->replaceVariables($value, $variables);
        }
        return $value !== null ? $value : '';
    }

    private function replaceVariables(string $text, array $variables): string
    {
        return preg_replace_callback(
            pattern: self::PLACEHOLDER_PATTERN,
            callback: fn($matches) => $variables[$matches[1]] ?: $matches[0],
            subject: $text
        );
    }
}

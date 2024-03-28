<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

final class LanguageLoader
{
    private string $languageDirectory;

    public function __construct(
        LanguagesDirectory $localisationDirectory,
    ) {
        $this->languageDirectory = $localisationDirectory->toString();
    }

    public function loadTranslations(string $language, string $filename): array
    {
        $languageFilePath = $this->languageDirectory . "/$language/$filename.php";
        if (file_exists($languageFilePath)) {
            $translations = include $languageFilePath;
            if (is_array($translations)) {
                return $translations;
            } else {
                throw new RuntimeException("/$language/$filename does not return an array");
            }
        }

        $languageFilePath = $this->languageDirectory . "/en/$filename.php";
        if (file_exists($languageFilePath)) {
            $translations = include $languageFilePath;
            if (is_array($translations)) {
                return $translations;
            } else {
                throw new RuntimeException("/en/$filename does not return an array");
            }
        }

        throw new FileNotFoundException("/$language/$filename does not exist");
    }
}

<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

final class LanguageLoader implements LanguageLoaderInterface
{
    private string $languageDirectory;

    public function __construct(
        LanguagesDirectoryInterface $localisationDirectory,
    ) {
        $this->languageDirectory = $localisationDirectory->toString();
    }

    /** @return array<string, array<string, string>> */
    public function loadTranslations(string $language, string $filename): array
    {
        $languageFilePath = $this->languageDirectory . "/$language/$filename.php";
        if (file_exists($languageFilePath)) {
            $translations = include $languageFilePath;
            if (is_array($translations)) {
                /** @var array<string, array<string, string>> $validatedTranslations */
                return $this->validateTranslationsStructure($translations, "/$language/$filename");
            } else {
                throw new RuntimeException("/$language/$filename does not return an array");
            }
        }

        $languageFilePath = $this->languageDirectory . "/en/$filename.php";
        if (file_exists($languageFilePath)) {
            $translations = include $languageFilePath;
            if (is_array($translations)) {
                /** @var array<string, array<string, string>> $validatedTranslations */
                return $this->validateTranslationsStructure($translations, "/en/$filename");
            } else {
                throw new RuntimeException("/en/$filename does not return an array");
            }
        }

        throw new FileNotFoundException("/$language/$filename does not exist");
    }

    /**
     * Validates that the translations array has the correct structure.
     *
     * @param array<mixed, mixed> $translations The translations to validate
     * @param string $fileIdentifier The file identifier for error messages
     * @return array<string, array<string, string>> The validated translations
     * @throws RuntimeException If the translations have an invalid structure
     */
    private function validateTranslationsStructure(array $translations, string $fileIdentifier): array
    {
        foreach ($translations as $key => $value) {
            if (!is_string($key)) {
                throw new RuntimeException("/$fileIdentifier contains a non-string key");
            }

            if (!is_array($value)) {
                throw new RuntimeException("/$fileIdentifier: '$key' does not contain an array");
            }

            foreach ($value as $subKey => $subValue) {
                if (!is_string($subKey)) {
                    throw new RuntimeException("/$fileIdentifier: '$key' contains a non-string key");
                }

                if (!is_string($subValue)) {
                    throw new RuntimeException("/$fileIdentifier: '$key.$subKey' contains a non-string value");
                }
            }
        }

        /** @var array<string, array<string, string>> */
        return $translations;
    }
}

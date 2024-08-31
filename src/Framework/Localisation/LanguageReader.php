<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

class LanguageReader
{
    private const PLACEHOLDER_PATTERN = '/%\{(\w+)}/';

    public function __construct(
        private readonly LanguageLoaderInterface $languageLoader,
        private readonly NestedArrayReaderInterface $nestedArrayReader
    ) {
    }

    /**
     * @param TranslationIdentifier $identifier
     * @param string $key
     * @param array<string, string> $variables
     * @return string|null
     */
    public function getTranslationText(
        TranslationIdentifier $identifier,
        string $key,
        array $variables = []
    ): ?string {

        if ($this->nestedArrayReader->hasData() === false) {
            $this->nestedArrayReader->setData(
                $this->languageLoader->loadTranslations($identifier->getLanguage(), $identifier->getFilename())
            );
        }

        $keys = explode('.', $key);
        $value = $this->nestedArrayReader->getValue($keys);

        if ($value && count($variables) > 0) {
            $value = $this->replaceVariables($value, $variables);
        }
        return $value === null ? '' : $value;
    }

    /**
     * @param string $text
     * @param array<string, string> $variables
     * @return string
     */
    private function replaceVariables(string $text, array $variables): string
    {
        return preg_replace_callback(
            pattern: self::PLACEHOLDER_PATTERN,
            callback: fn($matches) => $variables[$matches[1]] ?: $matches[0],
            subject: $text
        );
    }
}

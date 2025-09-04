<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

use Symfony\Component\HttpFoundation\Request;

final readonly class LanguageDetector
{
    public const string URL_LANGUAGE_PATTERN = '#^/([a-zA-Z]{2})(/|$)#';

    /** @param list<Language> $supportedLanguages */
    public function __construct(
        private array $supportedLanguages,
        private Language $defaultLanguage = Language::EN,
    ) {
    }

    public function getFromPath(Request $request): Language
    {
        $language = $this->detectFromRequest($request);
        if ($language !== null) {
            return $language;
        }
        return $this->defaultLanguage;
    }

    public function removeLanguagePrefix(Request $request): string
    {
        $path = $request->getPathInfo();
        $language = $this->detectFromRequest($request);
        if (in_array($language, $this->supportedLanguages)) {
            return substr($path, 3) ?: '/';
        }
        return $path;
    }

    private function detectFromRequest(Request $request): ?Language
    {
        $path = $request->getPathInfo();
        if (preg_match(self::URL_LANGUAGE_PATTERN, $path, $matches)) {
            $language = Language::tryFrom($matches[1]);
            if (in_array($language, $this->supportedLanguages)) {
                return $language;
            }
        }
        return null;
    }
}

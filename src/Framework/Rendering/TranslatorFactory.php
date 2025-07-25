<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Rendering;

use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

final readonly class TranslatorFactory implements TranslatorFactoryInterface
{
    public function __construct(
        private ArrayLoader $arrayLoader
    ) {
    }

    public function createTranslator(string $locale): Translator
    {
        // To handle dynamic content (like usernames), use placeholders:
        // /../locale/en/messages.php
        // user => [
        //     'WELCOME' => "Welcome, %name%!"
        // ]

        // In your Twig template:
        // <p>{{ 'user.WELCOME'|trans({'%name%': 'John'}) }}</p>

        $translator = new Translator($locale);
        $translator->addLoader('array', $this->arrayLoader);
        $translations = include(__DIR__ . "/../../Locale/$locale/messages.php");
        $translator->addResource('array', $translations, $locale);
        return $translator;
    }
}

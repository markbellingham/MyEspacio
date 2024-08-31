<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Rendering;

use Symfony\Component\Translation\Translator;

interface TranslatorFactoryInterface
{
    public function createTranslator(string $locale): Translator;
}

<?php

declare(strict_types=1);

namespace Tests\Framework\Rendering;

use Monolog\Test\TestCase;
use MyEspacio\Framework\Rendering\TranslatorFactory;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

final class TranslatorFactoryTest extends TestCase
{
    public function testCreateTranslator(): void
    {
        $locale = 'en';
        $arrayLoader = $this->createMock(ArrayLoader::class);
        $factory = new TranslatorFactory($arrayLoader);

        $result = $factory->createTranslator($locale);
        $this->assertInstanceOf(Translator::class, $result);
    }
}

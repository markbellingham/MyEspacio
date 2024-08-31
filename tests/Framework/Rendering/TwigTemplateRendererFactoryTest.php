<?php

declare(strict_types=1);

namespace Tests\Framework\Rendering;

use MyEspacio\Common\Application\CaptchaInterface;
use MyEspacio\Framework\Csrf\StoredTokenReader;
use MyEspacio\Framework\Rendering\TemplateDirectory;
use MyEspacio\Framework\Rendering\TranslatorFactory;
use MyEspacio\Framework\Rendering\TwigTemplateRenderer;
use MyEspacio\Framework\Rendering\TwigTemplateRendererFactory;
use PHPUnit\Framework\TestCase;

final class TwigTemplateRendererFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $captcha = $this->createMock(CaptchaInterface::class);
        $storedTokenReader = $this->createMock(StoredTokenReader::class);
        $templateDirectory = $this->createMock(TemplateDirectory::class);
        $translatorFactory = $this->createMock(TranslatorFactory::class);

        $factory = new TwigTemplateRendererFactory(
            $captcha,
            $storedTokenReader,
            $templateDirectory,
            $translatorFactory
        );
        $renderer = $factory->create();
        $this->assertInstanceOf(TwigTemplateRenderer::class, $renderer);
    }
}

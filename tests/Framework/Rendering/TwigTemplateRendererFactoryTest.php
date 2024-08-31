<?php

declare(strict_types=1);

namespace Tests\Framework\Rendering;

use MyEspacio\Common\Application\CaptchaInterface;
use MyEspacio\Framework\Csrf\StoredTokenReaderInterface;
use MyEspacio\Framework\Rendering\TemplateDirectoryInterface;
use MyEspacio\Framework\Rendering\TranslatorFactoryInterface;
use MyEspacio\Framework\Rendering\TwigTemplateRenderer;
use MyEspacio\Framework\Rendering\TwigTemplateRendererFactory;
use PHPUnit\Framework\TestCase;

final class TwigTemplateRendererFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $captcha = $this->createMock(CaptchaInterface::class);
        $storedTokenReader = $this->createMock(StoredTokenReaderInterface::class);
        $templateDirectory = $this->createMock(TemplateDirectoryInterface::class);
        $translatorFactory = $this->createMock(TranslatorFactoryInterface::class);

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

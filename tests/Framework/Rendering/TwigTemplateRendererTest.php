<?php

declare(strict_types=1);

namespace Tests\Framework\Rendering;

use MyEspacio\Framework\Rendering\TwigTemplateRenderer;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

final class TwigTemplateRendererTest extends TestCase
{
    public function testRender()
    {
        $environment = $this->createMock(Environment::class);
        $environment->expects($this->once())
            ->method('render')
            ->willReturn('');

        $templateRenderer = new TwigTemplateRenderer($environment);
        $template = $templateRenderer->render('');
        $this->assertEquals('', $template);
    }

    public function testRenderWithData()
    {
        $environment = $this->createMock(Environment::class);
        $environment->expects($this->once())
            ->method('render')
            ->willReturn('');

        $templateRenderer = new TwigTemplateRenderer($environment);
        $template = $templateRenderer->render('', []);
        $this->assertEquals('', $template);
    }
}

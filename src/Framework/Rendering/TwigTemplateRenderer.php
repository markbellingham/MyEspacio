<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Rendering;

use Twig\Environment;

final readonly class TwigTemplateRenderer implements TemplateRenderer
{
    public function __construct(
        private Environment $twigEnvironment
    ) {
    }

    public function render(string $template, array $data = []): string
    {
        return $this->twigEnvironment->render($template, $data);
    }
}

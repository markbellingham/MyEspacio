<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Rendering;

interface TemplateRendererFactoryInterface
{
    public function create(string $locale = 'en'): TemplateRenderer;
}

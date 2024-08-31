<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Rendering;

final class TemplateDirectory implements TemplateDirectoryInterface
{
    private string $templateDirectory;

    public function __construct(string $rootDirectory)
    {
        $this->templateDirectory = $rootDirectory . '/templates';
    }

    public function toString(): string
    {
        return $this->templateDirectory;
    }
}

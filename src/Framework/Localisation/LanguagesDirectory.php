<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

final class LanguagesDirectory implements LanguagesDirectoryInterface
{
    private string $languagesDirectory;

    public function __construct(
        string $rootDirectory
    ) {
        $this->languagesDirectory = $rootDirectory . '/src/Locale';
    }

    public function toString(): string
    {
        return $this->languagesDirectory;
    }
}

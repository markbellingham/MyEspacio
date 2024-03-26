<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Localisation;

final class LocalisationDirectory
{
    private string $localisationDirectory;

    public function __construct(
        string $rootDirectory
    ) {
        $this->localisationDirectory = $rootDirectory . '/src/Locale';
    }

    public function toString(): string
    {
        return $this->localisationDirectory;
    }
}

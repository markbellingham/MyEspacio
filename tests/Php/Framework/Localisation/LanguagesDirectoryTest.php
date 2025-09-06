<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Localisation;

use MyEspacio\Framework\Localisation\LanguagesDirectory;
use PHPUnit\Framework\TestCase;

final class LanguagesDirectoryTest extends TestCase
{
    public function testToString(): void
    {
        $localDir = '/src/Locale';
        $directory = new LanguagesDirectory(ROOT_DIR);

        $this->assertEquals(ROOT_DIR . $localDir, $directory->toString());
    }
}

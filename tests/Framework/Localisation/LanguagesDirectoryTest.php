<?php

declare(strict_types=1);

namespace Tests\Framework\Localisation;

use MyEspacio\Framework\Localisation\LanguagesDirectory;
use PHPUnit\Framework\TestCase;

final class LanguagesDirectoryTest extends TestCase
{
    public function testToString()
    {
        $localDir = '/src/Locale';
        $directory = new LanguagesDirectory(ROOT_DIR);

        $this->assertEquals(
            expected: ROOT_DIR . $localDir,
            actual: $directory->toString()
        );
    }
}

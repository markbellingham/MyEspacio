<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Rendering;

use MyEspacio\Framework\Rendering\TemplateDirectory;
use PHPUnit\Framework\TestCase;

final class TemplateDirectoryTest extends TestCase
{
    public function testToString(): void
    {
        $templateDirectory = new TemplateDirectory(ROOT_DIR);
        $this->assertEquals(
            ROOT_DIR . '/templates',
            $templateDirectory->toString()
        );
    }
}

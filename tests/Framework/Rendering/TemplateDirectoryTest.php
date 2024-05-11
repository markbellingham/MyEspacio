<?php

declare(strict_types=1);

namespace Tests\Framework\Rendering;

use MyEspacio\Framework\Rendering\TemplateDirectory;
use PHPUnit\Framework\TestCase;

final class TemplateDirectoryTest extends TestCase
{
    public function testToString()
    {
        $templateDirectory = new TemplateDirectory(ROOT_DIR);
        $this->assertEquals(
            ROOT_DIR . '/templates',
            $templateDirectory->toString()
        );
    }
}

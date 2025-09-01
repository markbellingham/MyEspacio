<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Routing;

use LogicException;
use MyEspacio\Framework\BaseController;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final class ControllerDiscovery
{
    /** @return class-string[] */
    public static function discover(string $directory, string $namespace = 'Presentation'): array
    {
        $controllers = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }

            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $class = $namespace . '\\' . str_replace(
                [DIRECTORY_SEPARATOR, '.php'],
                ['\\', ''],
                $relativePath
            );

            if (!class_exists($class)) {
                throw new RuntimeException(sprintf('Class %s does not exist', $class));
            }
            if (!is_subclass_of($class, BaseController::class)) {
                throw new LogicException(sprintf('Class %s does not extend BaseController', $class));
            }

            $controllers[] = $class;
        }

        return $controllers;
    }
}

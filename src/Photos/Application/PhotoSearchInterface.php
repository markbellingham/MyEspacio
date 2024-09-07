<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Application;

use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;

interface PhotoSearchInterface
{
    /**
     * @param array<string, string> $params
     */
    public function search(array $params): PhotoCollection|PhotoAlbum;
}

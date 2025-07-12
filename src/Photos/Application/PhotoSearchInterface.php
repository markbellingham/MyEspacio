<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Application;

use MyEspacio\Photos\Domain\Collection\PhotoCollection;
use MyEspacio\Photos\Domain\Entity\PhotoAlbum;

interface PhotoSearchInterface
{
    public function search(?string $album, ?string $searchTerms): PhotoCollection|PhotoAlbum;
}

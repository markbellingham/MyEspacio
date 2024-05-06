<?php

declare(strict_types=1);

namespace MyEspacio\Common\Domain\Repository;

use MyEspacio\Common\Domain\Entity\Tag;

interface TagRepositoryInterface
{
    public function save(Tag $tag): ?int;

    public function getTagByName(string $name): ?Tag;
}

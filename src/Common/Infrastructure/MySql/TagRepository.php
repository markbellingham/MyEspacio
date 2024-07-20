<?php

declare(strict_types=1);

namespace MyEspacio\Common\Infrastructure\MySql;

use MyEspacio\Common\Domain\Entity\Tag;
use MyEspacio\Common\Domain\Repository\TagRepositoryInterface;
use MyEspacio\Framework\Database\Connection;

final class TagRepository implements TagRepositoryInterface
{
    public function __construct(
        private readonly Connection $db
    ) {
    }

    public function save(Tag $tag): ?int
    {
        $stmt = $this->db->run(
            'INSERT INTO project.tags (tag) VALUES :tag',
            [
                'tag' => $tag->getTag()
            ]
        );
        return $this->db->statementHasErrors($stmt) ? null : $this->db->lastInsertId();
    }

    public function getTagByName(string $name): ?Tag
    {
        return $this->db->fetchOneModel(
            'SELECT id, tag FROM project.tags WHERE tag = :tag',
            [
                'tag' => $name
            ],
            Tag::class
        );
    }
}
